<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use App\Models\TaskAssignment;
use App\Models\TaskDocument;
use App\Models\TaskAuditLog;
use App\Models\TaskActivity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $viewType = $request->get('view', 'all');

        // Base query depending on view filter
        if ($viewType === 'created') {
            $tasksQuery = Task::where('created_by', $user->id)
                ->with(['assignees', 'meeting', 'creator']);
        } elseif ($viewType === 'assigned') {
            $tasksQuery = $user->assignedTasks()
                ->with(['assignees', 'meeting', 'creator']);
        } else {
            // 'all' - tasks created by me OR assigned to me
            $tasksQuery = Task::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $user->id));
            })->with(['assignees', 'meeting', 'creator']);
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $tasksQuery->where('deadline', '<', now())->where('status', '!=', 'completed');
            } else {
                $tasksQuery->where('status', $request->status);
            }
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $tasksQuery->where('priority', $request->priority);
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->search;
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Tab counts
        $assignedCount = $user->assignedTasks()->count();
        $createdCount = Task::where('created_by', $user->id)->count();
        $allCount = Task::where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $user->id));
        })->count();
        $archivedCount = Task::onlyTrashed()->where('created_by', $user->id)->count();

        // Get paginated tasks
        $tasks = $tasksQuery->orderBy('deadline', 'asc')->latest()->paginate(10)->withQueryString();

        return view('faculty.tasks.index', compact('tasks', 'viewType', 'assignedCount', 'createdCount', 'allCount', 'archivedCount'));
    }

    public function create()
    {
        $departmentId = auth()->user()->department_id;
        $faculties = User::where('department_id', $departmentId)
            ->where('role', 'faculty')
            ->orderBy('name')
            ->get();
        $meetings = Meeting::where('department_id', $departmentId)
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
            'category'    => 'nullable|string|max:100',
            'priority'    => 'required|in:low,medium,high,urgent',
            'deadline'    => 'required|date|after:today',
            'assignees'   => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
            'meeting_id'  => 'nullable|exists:meetings,id',
        ]);

        $task = Task::create([
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'category'      => $validated['category'] ?? 'Faculty Assignment',
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
                'role'                => 'owner',
                'assigned_by'         => auth()->id(),
                'assigned_at'         => now(),
            ];

            TaskAssignment::create([
                'task_id'     => $task->id,
                'faculty_id'  => $userId,
                'assigned_by' => auth()->id(),
                'role'        => 'owner',
                'status'      => 'pending',
                'assigned_at' => now(),
            ]);
        }
        $task->assignees()->attach($attachData);

        // Centralized Notification & Activity Logging
        $this->notificationService->notifyTaskAssigned($task, $validated['assignees']);

        return redirect()->route('faculty.tasks.index', ['view' => 'created'])->with('success', 'Task created and assigned successfully.');
    }

    public function show($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('view', $task);

        $task->load([
            'creator',
            'meeting',
            'checklistItems.creator',
            'checklistItems.assignee',
            'checklistItems.completedByUser',
            'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at', 'is_reassigned')
        ]);
        
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()?->pivot;
        
        // Shared Workspace: Get all documents uploaded for this task across all collaborators
        $allTaskDocuments = TaskDocument::where('task_id', $task->id)
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

        // Group latest documents by uploading faculty member
        $groupedDocuments = $latestDocuments->groupBy('user_id');

        // Current faculty user's latest uploaded document
        $myLatestDoc = TaskDocument::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->orderBy('version', 'desc')
            ->first();
        
        return view('faculty.tasks.show', compact('task', 'pivot', 'allTaskDocuments', 'latestDocuments', 'groupedDocuments', 'myLatestDoc'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $departmentId = auth()->user()->department_id;
        $faculties = User::where('department_id', $departmentId)
            ->where('role', 'faculty')
            ->orderBy('name')
            ->get();
        $meetings = Meeting::where('department_id', $departmentId)
            ->latest()
            ->take(10)
            ->get();
            
        $task->load('assignees');
        $selectedAssignees = $task->assignees->pluck('id')->toArray();
            
        return view('faculty.tasks.edit', compact('task', 'faculties', 'meetings', 'selectedAssignees'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'priority'    => 'required|in:low,medium,high,urgent',
            'deadline'    => 'required|date',
            'assignees'   => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
            'meeting_id'  => 'nullable|exists:meetings,id',
        ]);

        $oldValues = [
            'title'       => $task->title,
            'description' => $task->description,
            'category'    => $task->category,
            'priority'    => $task->priority,
            'deadline'    => $task->deadline ? $task->deadline->format('Y-m-d H:i') : '',
            'meeting_id'  => $task->meeting_id,
        ];

        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category'    => $validated['category'] ?? $task->category,
            'priority'    => $validated['priority'],
            'deadline'    => $validated['deadline'],
            'meeting_id'  => $validated['meeting_id'] ?? null,
        ]);

        $newDeadlineFormatted = Carbon::parse($validated['deadline'])->format('Y-m-d H:i');

        // Field-level change detection
        $changes = [];
        if ($oldValues['title'] !== $validated['title']) {
            $changes['title'] = ['old' => $oldValues['title'], 'new' => $validated['title']];
        }
        if ($oldValues['description'] !== $validated['description']) {
            $changes['description'] = ['old' => $oldValues['description'] ?? 'None', 'new' => $validated['description'] ?? 'None'];
        }
        if ($oldValues['priority'] !== $validated['priority']) {
            $changes['priority'] = ['old' => ucfirst($oldValues['priority']), 'new' => ucfirst($validated['priority'])];
        }
        if ($oldValues['category'] !== ($validated['category'] ?? $task->category)) {
            $changes['category'] = ['old' => $oldValues['category'] ?? 'General', 'new' => $validated['category'] ?? 'General'];
        }
        if ($oldValues['deadline'] !== $newDeadlineFormatted) {
            $changes['deadline'] = ['old' => $oldValues['deadline'], 'new' => $newDeadlineFormatted];
        }

        // Sync Assignees with change detection
        $currentAssignees = $task->assignees->pluck('id')->toArray();
        $newAssignees = $validated['assignees'];
        
        $toAttach = array_diff($newAssignees, $currentAssignees);
        $toDetach = array_diff($currentAssignees, $newAssignees);
        
        if (!empty($toDetach)) {
            foreach ($toDetach as $id) {
                $u = User::find($id);
                if ($u) {
                    $this->notificationService->notifyCollaboratorRemoved($task, $u);
                }
            }
            $task->assignees()->detach($toDetach);
            TaskAssignment::where('task_id', $task->id)->whereIn('faculty_id', $toDetach)->delete();
        }
        
        if (!empty($toAttach)) {
            $attachData = [];
            foreach ($toAttach as $id) {
                $u = User::find($id);
                $attachData[$id] = [
                    'status'              => 'pending',
                    'progress_percentage' => 0,
                    'role'                => 'owner',
                    'assigned_by'         => auth()->id(),
                    'assigned_at'         => now(),
                ];
                TaskAssignment::create([
                    'task_id'     => $task->id,
                    'faculty_id'  => $id,
                    'assigned_by' => auth()->id(),
                    'role'        => 'owner',
                    'status'      => 'pending',
                    'assigned_at' => now(),
                ]);
                if ($u) {
                    $this->notificationService->notifyCollaboratorAdded($task, $u);
                }
            }
            $task->assignees()->attach($attachData);
        }

        // Notify updated task details and log changes
        $this->notificationService->notifyTaskUpdated($task, $changes, $currentAssignees);

        return redirect()->route('faculty.tasks.show', $task)->with('success', 'Task updated and audit log recorded.');
    }

    /**
     * Archive task (Soft Delete).
     */
    public function destroy($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        if ($task->trashed()) {
            return redirect()->route('faculty.tasks.index')->with('success', 'Task is already archived.');
        }

        $this->authorize('delete', $task);

        $this->notificationService->notifyTaskArchived($task);

        $task->delete(); // Soft delete

        return redirect()->route('faculty.tasks.index')->with('success', 'Task archived successfully.');
    }

    /**
     * List archived tasks for Faculty with search and filters.
     */
    public function archived(Request $request)
    {
        $query = Task::onlyTrashed()
            ->where('created_by', auth()->id())
            ->with(['assignees', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $archivedTasks = $query->latest('deleted_at')->paginate(10)->withQueryString();

        return view('faculty.tasks.archived', compact('archivedTasks'));
    }

    /**
     * Restore archived task.
     */
    public function restore($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $task);

        $task->restore();

        $this->notificationService->notifyTaskRestored($task);

        return redirect()->route('faculty.tasks.show', $task)->with('success', 'Task restored successfully.');
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

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('task_documents', 'public');

            // Find previous version uploaded by current faculty
            $existingDoc = TaskDocument::where('task_id', $task->id)
                ->where('user_id', auth()->id())
                ->orderBy('version', 'desc')
                ->first();

            if ($existingDoc) {
                $newVersion = $existingDoc->version + 1;
                $rootId = $existingDoc->original_document_id ?? $existingDoc->id;

                $uploadedDoc = TaskDocument::create([
                    'task_id'              => $task->id,
                    'user_id'              => auth()->id(),
                    'original_document_id' => $rootId,
                    'file_name'            => $file->getClientOriginalName(),
                    'file_path'            => $path,
                    'version'              => $newVersion,
                    'review_status'        => 'draft',
                    'remarks'              => $validated['remarks'] ?? null,
                ]);

                TaskAuditLog::create([
                    'task_id'    => $task->id,
                    'user_id'    => auth()->id(),
                    'field_name' => 'document_replaced',
                    'old_value'  => $existingDoc->file_name . ' (v' . $existingDoc->version . ')',
                    'new_value'  => $file->getClientOriginalName() . ' (v' . $newVersion . ')',
                ]);
            } else {
                $uploadedDoc = TaskDocument::create([
                    'task_id'              => $task->id,
                    'user_id'              => auth()->id(),
                    'original_document_id' => null,
                    'file_name'            => $file->getClientOriginalName(),
                    'file_path'            => $path,
                    'version'              => 1,
                    'review_status'        => 'draft',
                    'remarks'              => $validated['remarks'] ?? null,
                ]);

                TaskAuditLog::create([
                    'task_id'    => $task->id,
                    'user_id'    => auth()->id(),
                    'field_name' => 'document_uploaded',
                    'old_value'  => null,
                    'new_value'  => $file->getClientOriginalName() . ' (v1)',
                ]);
            }
        }

        // Update current user's pivot record if user is an assignee
        if ($task->assignees()->where('user_id', auth()->id())->exists()) {
            auth()->user()->assignedTasks()->updateExistingPivot($task->id, [
                'progress_percentage' => $progress,
                'status'              => $status,
                'remarks'             => $validated['remarks'] ?? null,
                'completed_at'        => $progress === 100 ? now() : null,
            ]);
        }

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
        $recipientId = $task->created_by;

        if ($progressChanged && $uploadedDoc) {
            $activityMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded supporting document: {$uploadedDoc->file_name}.";
            $notifMsg = "{$actor->name} updated progress from {$oldProgress}% to {$newProgress}% and uploaded a supporting document for Task: \"{$task->title}\".";
            $successMsg = "Progress and supporting document submitted for review.";
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
            TaskAuditLog::create([
                'task_id'    => $task->id,
                'user_id'    => $actor->id,
                'field_name' => 'progress_percentage',
                'old_value'  => $oldProgress . '%',
                'new_value'  => $newProgress . '%',
            ]);
        }

        if ($remarksChanged && !empty($validated['remarks'])) {
            TaskAuditLog::create([
                'task_id'    => $task->id,
                'user_id'    => $actor->id,
                'field_name' => 'remarks',
                'old_value'  => $oldRemarks ?? 'None',
                'new_value'  => $validated['remarks'],
            ]);
        }

        TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => $actor->id,
            'action'      => $progress === 100 ? 'completed' : ($uploadedDoc ? 'document_uploaded' : 'progress_updated'),
            'description' => $activityMsg,
        ]);

        if ($recipientId && $recipientId !== $actor->id) {
            $this->notificationService->logNotification(
                $recipientId,
                'progress_updated',
                $task->id,
                Task::class,
                $notifMsg
            );
        }

        return redirect()->route('faculty.tasks.show', $task)->with('success', $successMsg);
    }
}
