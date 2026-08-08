<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
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
        $query = Task::where('department_id', auth()->user()->department_id)
            ->where('owner_role', 'hod')
            ->with(['assignees', 'meeting']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('deadline', '<', now())
                      ->where('status', '!=', 'completed');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(10);
        
        return view('hod.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest()
            ->take(10)
            ->get();
            
        return view('hod.tasks.create', compact('faculties', 'meetings'));
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
            'owner_role'    => 'hod',
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

            \App\Models\TaskAssignment::create([
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

        return redirect()->route('hod.tasks.index')->with('success', 'Task created and assigned successfully.');
    }

    public function show($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('view', $task);
        $task->load(['creator', 'meeting', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')]);

        $allTaskDocuments = \App\Models\TaskDocument::where('task_id', $task->id)
            ->with(['user', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

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

        $groupedDocuments = $latestDocuments->groupBy('user_id');

        return view('hod.tasks.show', compact('task', 'allTaskDocuments', 'latestDocuments', 'groupedDocuments'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest()
            ->take(10)
            ->get();
            
        $task->load('assignees');
        $selectedAssignees = $task->assignees->pluck('id')->toArray();
            
        return view('hod.tasks.edit', compact('task', 'faculties', 'meetings', 'selectedAssignees'));
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
            'assignees'   => 'required|array',
            'assignees.*' => 'exists:users,id',
            'meeting_id'  => 'nullable|exists:meetings,id',
        ]);

        $oldValues = [
            'title'       => $task->title,
            'description' => $task->description,
            'category'    => $task->category,
            'priority'    => $task->priority,
            'deadline'    => $task->deadline->format('Y-m-d H:i'),
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
        }
        
        if (!empty($toAttach)) {
            $attachData = [];
            foreach ($toAttach as $id) {
                $u = User::find($id);
                $attachData[$id] = ['status' => 'pending', 'progress_percentage' => 0];
                if ($u) {
                    $this->notificationService->notifyCollaboratorAdded($task, $u);
                }
            }
            $task->assignees()->attach($attachData);
        }

        // Notify updated task details and log changes
        $this->notificationService->notifyTaskUpdated($task, $changes, $currentAssignees);

        return redirect()->route('hod.tasks.show', $task)->with('success', 'Task updated and audit log recorded.');
    }

    /**
     * Archive task (Soft Delete).
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->notificationService->notifyTaskArchived($task);

        $task->delete(); // Soft delete

        return redirect()->route('hod.tasks.index')->with('success', 'Task archived successfully.');
    }

    /**
     * List archived tasks for HOD with search and filters.
     */
    public function archived(Request $request)
    {
        $query = Task::onlyTrashed()
            ->where('department_id', auth()->user()->department_id)
            ->where('owner_role', 'hod')
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

        return view('hod.tasks.archived', compact('archivedTasks'));
    }

    /**
     * Restore archived task.
     */
    public function restore($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $task);

        $task->restore();

        $this->notificationService->notifyTaskRestored($task);

        return redirect()->route('hod.tasks.show', $task)->with('success', 'Task restored successfully.');
    }
}
