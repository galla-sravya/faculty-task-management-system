<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use App\Services\NotificationService;

class TaskController extends Controller
{
    protected function getOwnerRole()
    {
        return auth()->user()->coordinatorType->slug;
    }

    public function index(Request $request)
    {
        $departmentId = auth()->user()->department_id;
        $ownerRole = $this->getOwnerRole();

        $tasks = Task::where('department_id', $departmentId)
            ->where('owner_role', $ownerRole)
            ->with(['assignees', 'creator'])
            ->latest()
            ->paginate(15);
            
        return view('coordinator.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $departmentId = auth()->user()->department_id;
        $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->orderBy('name')->get();
        return view('coordinator.tasks.create', compact('faculties'));
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'category' => $request->category,
            'deadline' => $request->deadline,
            'status' => 'pending',
            'department_id' => auth()->user()->department_id,
            'created_by' => auth()->id(),
            'owner_role' => $this->getOwnerRole(),
        ]);

        $task->assignees()->attach($request->assignees, [
            'role' => 'owner',
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        // Send notifications
        $notificationService->notifyTaskAssigned($task, $request->assignees);

        return redirect()->route('coordinator.dashboard')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        // Ensure isolation
        if ($task->owner_role !== $this->getOwnerRole() || $task->department_id !== auth()->user()->department_id) {
            abort(403);
        }
        
        $task->load(['assignees', 'creator', 'documents.user', 'activities.user', 'comments.user']);
        $faculties = User::where('department_id', auth()->user()->department_id)->where('role', 'faculty')->orderBy('name')->get();
        
        return view('coordinator.tasks.show', compact('task', 'faculties'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
            
        $task->load('assignees');
        $selectedAssignees = $task->assignees->pluck('id')->toArray();
            
        return view('coordinator.tasks.edit', compact('task', 'faculties', 'selectedAssignees'));
    }

    public function update(Request $request, Task $task, NotificationService $notificationService)
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
        ]);

        $oldValues = [
            'title'       => $task->title,
            'description' => $task->description,
            'category'    => $task->category,
            'priority'    => $task->priority,
            'deadline'    => $task->deadline->format('Y-m-d H:i'),
        ];

        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category'    => $validated['category'] ?? $task->category,
            'priority'    => $validated['priority'],
            'deadline'    => $validated['deadline'],
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
                    $notificationService->notifyCollaboratorRemoved($task, $u);
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
                    $notificationService->notifyCollaboratorAdded($task, $u);
                }
            }
            $task->assignees()->attach($attachData);
        }

        // Notify updated task details and log changes
        $notificationService->notifyTaskUpdated($task, $changes, $currentAssignees);

        return redirect()->route('coordinator.tasks.show', $task)->with('success', 'Task updated and audit log recorded.');
    }

    /**
     * Archive task (Soft Delete).
     */
    public function destroy($id, NotificationService $notificationService)
    {
        $task = Task::withTrashed()->findOrFail($id);

        if ($task->trashed()) {
            return redirect()->route('coordinator.tasks.index')->with('success', 'Task is already archived.');
        }

        $this->authorize('delete', $task);

        $notificationService->notifyTaskArchived($task);

        $task->delete(); // Soft delete

        return redirect()->route('coordinator.tasks.index')->with('success', 'Task archived successfully.');
    }

    /**
     * List archived tasks for Coordinator with search and filters.
     */
    public function archived(Request $request)
    {
        $query = Task::onlyTrashed()
            ->where('department_id', auth()->user()->department_id)
            ->where('owner_role', $this->getOwnerRole())
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

        return view('coordinator.tasks.archived', compact('archivedTasks'));
    }

    /**
     * Restore archived task.
     */
    public function restore($id, NotificationService $notificationService)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $task);

        $task->restore();

        $notificationService->notifyTaskRestored($task);

        return redirect()->route('coordinator.tasks.show', $task)->with('success', 'Task restored successfully.');
    }
}
