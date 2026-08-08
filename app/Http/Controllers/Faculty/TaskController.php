<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = auth()->user()->assignedTasks();
        
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('deadline', '<', now())
                      ->wherePivot('status', '!=', 'completed');
            } else {
                $query->wherePivot('status', $request->status);
            }
        }
        
        $tasks = $query->orderBy('deadline', 'asc')->paginate(10);
        
        return view('faculty.tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $this->authorize('updateProgress', $task);
        $task->load(['creator', 'meeting', 'checklistItems.creator', 'checklistItems.assignee', 'checklistItems.completedByUser', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')]);
        
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()->pivot;
        
        // Shared Workspace: Get all documents uploaded for this task across all collaborators
        $allTaskDocuments = \App\Models\TaskDocument::where('task_id', $task->id)
            ->with(['user', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Find latest version of each document tree
        $rootDocIds = $allTaskDocuments->whereNull('original_document_id')->pluck('id');
        $childDocs = $allTaskDocuments->whereNotNull('original_document_id');

        $latestDocuments = collect();
        foreach ($rootDocIds as $rootId) {
            $latestChild = $childDocs->where('original_document_id', $rootId)->sortByDesc('version')->first();
            if ($latestChild) {
                $latestDocuments->push($latestChild);
            } else {
                $latestDocuments->push($allTaskDocuments->firstWhere('id', $rootId));
            }
        }
        
        $coveredRootIds = $latestDocuments->pluck('original_document_id')->merge($latestDocuments->pluck('id'))->filter()->unique();
        $orphans = $childDocs->filter(fn ($d) => !$coveredRootIds->contains($d->original_document_id));
        $latestDocuments = $latestDocuments->merge($orphans)->sortByDesc('created_at')->values();

        // Group latest documents by uploading faculty member (Faculty and HOD see only latest versions)
        $groupedDocuments = $latestDocuments->groupBy('user_id');

        // Current faculty user's latest uploaded document
        $myLatestDoc = \App\Models\TaskDocument::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->orderBy('version', 'desc')
            ->first();
        
        return view('faculty.tasks.show', compact('task', 'pivot', 'allTaskDocuments', 'latestDocuments', 'groupedDocuments', 'myLatestDoc'));
    }

    public function updateProgress(Request $request, Task $task)
    {
        $this->authorize('updateProgress', $task);
        
        $validated = $request->validate([
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'status'              => 'nullable|string',
            'remarks'             => 'nullable|string',
            'document'            => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg|max:10240',
        ]);
        
        $progress = isset($validated['progress_percentage']) ? (int) $validated['progress_percentage'] : null;

        // Map status if progress_percentage is provided directly
        if ($progress !== null) {
            $status = match (true) {
                $progress === 0   => 'not_started',
                $progress <= 20  => 'collecting_resources',
                $progress <= 40  => 'working_on_task',
                $progress <= 60  => 'documents_uploaded',
                $progress <= 80  => 'checklist_completed',
                $progress <= 99  => 'submitted_for_review',
                $progress === 100 => 'completed',
            };
        } else {
            $status = $validated['status'] ?? 'working_on_task';
            $statusProgressMap = [
                'not_started'          => 0,
                'pending'              => 0,
                'collecting_resources' => 15,
                'working_on_task'      => 35,
                'in_progress'          => 35,
                'documents_uploaded'   => 55,
                'checklist_completed'  => 75,
                'submitted_for_review' => 90,
                'pending_review'       => 90,
                'completed'            => 100,
            ];
            $progress = $statusProgressMap[$status] ?? 35;
        }

        // Special Rule for 100%: Validate required checklist items before completing
        if ($progress === 100) {
            $totalChecklist = $task->checklistItems()->count();
            $completedChecklist = $task->checklistItems()->where('is_completed', true)->count();
            if ($totalChecklist > 0 && $completedChecklist < $totalChecklist) {
                return redirect()->back()->withInput()->with('error', 'Cannot complete task (100%): All subtasks/checklist items must be completed first (' . $completedChecklist . '/' . $totalChecklist . ' done).');
            }
        }

        $pivot = $task->assignees()->where('user_id', auth()->id())->first()?->pivot;
        $oldProgress = $pivot ? (int) $pivot->progress_percentage : 0;
        $oldRemarks = $pivot ? $pivot->remarks : null;

        // Document Replacement Logic
        $uploadedDoc = null;
        $isDocumentReplaced = false;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('task_documents', 'public');

            // Find previous version uploaded by current faculty
            $existingDoc = \App\Models\TaskDocument::where('task_id', $task->id)
                ->where('user_id', auth()->id())
                ->orderBy('version', 'desc')
                ->first();

            if ($existingDoc) {
                $isDocumentReplaced = true;
                $newVersion = $existingDoc->version + 1;
                $rootId = $existingDoc->original_document_id ?? $existingDoc->id;

                $uploadedDoc = \App\Models\TaskDocument::create([
                    'task_id'              => $task->id,
                    'user_id'              => auth()->id(),
                    'original_document_id' => $rootId,
                    'file_name'            => $file->getClientOriginalName(),
                    'file_path'            => $path,
                    'version'              => $newVersion,
                    'review_status'        => 'draft',
                    'remarks'              => $validated['remarks'] ?? null,
                ]);

                \App\Models\TaskAuditLog::create([
                    'task_id'    => $task->id,
                    'user_id'    => auth()->id(),
                    'field_name' => 'document_replaced',
                    'old_value'  => $existingDoc->file_name . ' (v' . $existingDoc->version . ')',
                    'new_value'  => $file->getClientOriginalName() . ' (v' . $newVersion . ')',
                ]);
            } else {
                $uploadedDoc = \App\Models\TaskDocument::create([
                    'task_id'              => $task->id,
                    'user_id'              => auth()->id(),
                    'original_document_id' => null,
                    'file_name'            => $file->getClientOriginalName(),
                    'file_path'            => $path,
                    'version'              => 1,
                    'review_status'        => 'draft',
                    'remarks'              => $validated['remarks'] ?? null,
                ]);

                \App\Models\TaskAuditLog::create([
                    'task_id'    => $task->id,
                    'user_id'    => auth()->id(),
                    'field_name' => 'document_uploaded',
                    'old_value'  => null,
                    'new_value'  => $file->getClientOriginalName() . ' (v1)',
                ]);
            }
        }

        // Update current user's pivot record
        auth()->user()->assignedTasks()->updateExistingPivot($task->id, [
            'progress_percentage' => $progress,
            'status'              => $status,
            'remarks'             => $validated['remarks'] ?? null,
            'completed_at'        => $progress === 100 ? now() : null,
        ]);

        // Sync main task status
        if ($progress === 100 && $task->status !== 'completed') {
            $task->update(['status' => 'completed']);
        } elseif ($progress >= 81 && !in_array($task->status, ['completed', 'submitted_for_review', 'pending_review'])) {
            $task->update(['status' => 'submitted_for_review']);
        } else {
            $task->update(['status' => $status]);
        }

        $newProgress = $task->fresh(['assignees'])->overall_progress;
        $progressChanged = ($oldProgress !== $newProgress);
        $remarksChanged = ($oldRemarks !== ($validated['remarks'] ?? null));

        // Single Combined Notification & Activity Log Logic
        $actor = auth()->user();
        $hodId = $task->created_by;

        if ($progressChanged && $uploadedDoc) {
            $activityMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded supporting document: {$uploadedDoc->file_name}.";
            $notifMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded a supporting document for Task: \"{$task->title}\".";
            $successMsg = "Progress and supporting document submitted for HOD review.";
        } elseif ($uploadedDoc) {
            $activityMsg = "{$actor->name} uploaded supporting document: {$uploadedDoc->file_name} (v{$uploadedDoc->version}).";
            $notifMsg = "{$actor->name} uploaded an updated supporting document for Task: \"{$task->title}\".";
            $successMsg = "Supporting document updated successfully.";
        } elseif ($progressChanged) {
            $activityMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}%.";
            $notifMsg = "Progress updated from {$oldProgress}% to {$newProgress}% for Task: \"{$task->title}\".";
            $successMsg = "Progress updated successfully to {$newProgress}%.";
        } else {
            $activityMsg = "{$actor->name} updated remarks for task.";
            $notifMsg = "{$actor->name} updated remarks for Task: \"{$task->title}\".";
            $successMsg = "Task details updated successfully.";
        }

        if ($progressChanged) {
            \App\Models\TaskAuditLog::create([
                'task_id'    => $task->id,
                'user_id'    => $actor->id,
                'field_name' => 'progress_percentage',
                'old_value'  => $oldProgress . '%',
                'new_value'  => $newProgress . '%',
            ]);
        }

        if ($remarksChanged && !empty($validated['remarks'])) {
            \App\Models\TaskAuditLog::create([
                'task_id'    => $task->id,
                'user_id'    => $actor->id,
                'field_name' => 'remarks',
                'old_value'  => $oldRemarks ?? 'None',
                'new_value'  => $validated['remarks'],
            ]);
        }

        \App\Models\TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => $actor->id,
            'action'      => $progress === 100 ? 'completed' : ($uploadedDoc ? 'document_uploaded' : 'progress_updated'),
            'description' => $activityMsg,
        ]);

        if ($hodId && $hodId !== $actor->id) {
            $this->notificationService->logNotification(
                $hodId,
                'progress_updated',
                $task->id,
                Task::class,
                $notifMsg
            );
        }

        return redirect()->route('faculty.tasks.show', $task)->with('success', $successMsg);
    }
}
