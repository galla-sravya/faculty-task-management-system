<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
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
        $user = auth()->user();
        $tab = $request->get('tab', 'assigned'); // 'assigned' or 'created'

        if ($tab === 'created') {
            $tasksQuery = Task::where('created_by', $user->id)
                ->with(['assignees', 'meeting']);
                
            if ($request->filled('status')) {
                if ($request->status === 'overdue') {
                    $tasksQuery->where('deadline', '<', now())->where('status', '!=', 'completed');
                } else {
                    $tasksQuery->where('status', $request->status);
                }
            }
        } else {
            // Base query for all assigned tasks
            $tasksQuery = $user->assignedTasks();

            // Apply status filter
            if ($request->filled('status')) {
                if ($request->status === 'overdue') {
                    $tasksQuery->where('deadline', '<', now())->wherePivot('status', '!=', 'completed');
                } else {
                    $tasksQuery->wherePivot('status', $request->status);
                }
            }
        }

        // Get paginated tasks
        $tasks = $tasksQuery->orderBy('deadline', 'asc')->paginate(10)->withQueryString();

        return view('faculty.tasks.index', compact('tasks', 'tab'));
    }

    public function create()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->where('id', '!=', auth()->id()) // optionally exclude self
            ->get();
            
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest()
            ->take(10)
            ->get();
            
        return view('faculty.tasks.create', compact('faculties', 'meetings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'deadline'    => 'required|date|after:today',
            'assignees'   => 'required|array',
            'assignees.*' => 'exists:users,id',
            'meeting_id'  => 'nullable|exists:meetings,id',
        ]);

        $task = Task::create([
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'priority'      => $validated['priority'],
            'deadline'      => $validated['deadline'],
            'created_by'    => auth()->id(),
            'owner_role'    => 'faculty',
            'department_id' => auth()->user()->department_id,
            'meeting_id'    => $validated['meeting_id'] ?? null,
            'status'        => 'pending',
        ]);

        $attachData = [];
        foreach ($validated['assignees'] as $userId) {
            $attachData[$userId] = [
                'status'              => 'pending',
                'progress_percentage' => 0,
                'role'                => 'collaborator', // faculty assigning to faculty makes them collaborators usually, or owner of subtask
                'assigned_by'         => auth()->id(),
                'assigned_at'         => now(),
            ];

            \App\Models\TaskAssignment::create([
                'task_id'     => $task->id,
                'faculty_id'  => $userId,
                'assigned_by' => auth()->id(),
                'role'        => 'collaborator',
                'status'      => 'pending',
                'assigned_at' => now(),
            ]);
        }
        $task->assignees()->attach($attachData);

        // Notify
        $this->notificationService->notifyTaskAssigned($task, $validated['assignees']);

        return redirect()->route('faculty.tasks.index', ['tab' => 'created'])->with('success', 'Task created and assigned successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['creator', 'meeting', 'checklistItems.creator', 'checklistItems.assignee', 'checklistItems.completedByUser', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at', 'is_reassigned')]);
        
        $assigneeRecord = $task->assignees()->where('user_id', auth()->id())->first();
        $pivot = $assigneeRecord ? $assigneeRecord->pivot : null;
        
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
            'document.*'          => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg|max:10240',
            'upload_action'       => 'nullable|string',
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

        // Document Replacement/Upload Logic
        $uploadedDocsCount = 0;
        $replacedDocName = null;
        $lastUploadedDoc = null;

        if ($request->hasFile('document')) {
            $files = $request->file('document');
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $uploadAction = $request->input('upload_action', 'new');
            
            $replaceDocId = null;
            if (str_starts_with($uploadAction, 'replace_')) {
                $replaceDocId = (int) str_replace('replace_', '', $uploadAction);
            }

            foreach ($files as $index => $file) {
                $path = $file->store('task_documents', 'public');
                
                // Only replace if it's the very first file and a replace action was selected
                if ($index === 0 && $replaceDocId) {
                    $existingDoc = \App\Models\TaskDocument::where('task_id', $task->id)
                        ->where('user_id', auth()->id())
                        ->where('id', $replaceDocId)
                        ->first();
                        
                    if ($existingDoc) {
                        $newVersion = $existingDoc->version + 1;
                        $rootId = $existingDoc->original_document_id ?? $existingDoc->id;

                        $lastUploadedDoc = \App\Models\TaskDocument::create([
                            'task_id'              => $task->id,
                            'user_id'              => auth()->id(),
                            'original_document_id' => $rootId,
                            'file_name'            => $file->getClientOriginalName(),
                            'file_path'            => $path,
                            'version'              => $newVersion,
                            'review_status'        => 'draft',
                            'remarks'              => $validated['remarks'] ?? null,
                        ]);
                        
                        $replacedDocName = $file->getClientOriginalName();
                        $uploadedDocsCount++;

                        \App\Models\TaskAuditLog::create([
                            'task_id'    => $task->id,
                            'user_id'    => auth()->id(),
                            'field_name' => 'document_replaced',
                            'old_value'  => $existingDoc->file_name . ' (v' . $existingDoc->version . ')',
                            'new_value'  => $file->getClientOriginalName() . ' (v' . $newVersion . ')',
                        ]);
                        continue; // Skip the "new" block for this first file
                    }
                }
                
                // Add as new document
                $lastUploadedDoc = \App\Models\TaskDocument::create([
                    'task_id'              => $task->id,
                    'user_id'              => auth()->id(),
                    'original_document_id' => null,
                    'file_name'            => $file->getClientOriginalName(),
                    'file_path'            => $path,
                    'version'              => 1,
                    'review_status'        => 'draft',
                    'remarks'              => $validated['remarks'] ?? null,
                ]);
                
                $uploadedDocsCount++;

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

        if ($progressChanged && $uploadedDocsCount > 0) {
            $docStr = $uploadedDocsCount === 1 ? ($replacedDocName ? $replacedDocName : $lastUploadedDoc->file_name) : "{$uploadedDocsCount} documents";
            $activityMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded {$docStr}.";
            $notifMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded supporting document(s) for Task: \"{$task->title}\".";
            $successMsg = "Progress and supporting document(s) submitted for review.";
        } elseif ($uploadedDocsCount > 0) {
            $docStr = $uploadedDocsCount === 1 ? ($replacedDocName ? $replacedDocName : $lastUploadedDoc->file_name) : "{$uploadedDocsCount} documents";
            $activityMsg = "{$actor->name} uploaded {$docStr}.";
            $notifMsg = "{$actor->name} uploaded updated supporting document(s) for Task: \"{$task->title}\".";
            $successMsg = "Supporting document(s) uploaded successfully.";
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
            'action'      => $progress === 100 ? 'completed' : ($uploadedDocsCount > 0 ? 'document_uploaded' : 'progress_updated'),
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
