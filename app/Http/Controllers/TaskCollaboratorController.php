<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskCollaboratorController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Add one or more collaborators to a task.
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('addCollaborator', $task);

        $validated = $request->validate([
            'collaborators'   => 'required|array|min:1',
            'collaborators.*' => 'exists:users,id',
            'role'            => 'required|in:collaborator,secondary_owner',
            'reason'          => 'nullable|string|max:1000',
            'notify_hod'      => 'nullable|boolean',
        ]);

        $role = $validated['role'];
        $reason = $validated['reason'] ?? null;

        $currentAssigneeIds = $task->assignees->pluck('id')->toArray();
        $added = [];

        foreach ($validated['collaborators'] as $facultyId) {
            $facultyId = (int) $facultyId;

            // Skip if already assigned
            if (in_array($facultyId, $currentAssigneeIds)) {
                continue;
            }

            // Attach to task_user pivot (existing Eloquent relationship)
            $task->assignees()->attach($facultyId, [
                'status'              => 'pending',
                'progress_percentage' => 0,
                'role'                => $role,
                'assigned_by'         => auth()->id(),
                'assigned_at'         => now(),
            ]);

            // Also insert into task_assignments tracking table
            TaskAssignment::create([
                'task_id'     => $task->id,
                'faculty_id'  => $facultyId,
                'assigned_by' => auth()->id(),
                'role'        => $role,
                'status'      => 'pending',
                'reason'      => $reason,
                'assigned_at' => now(),
            ]);

            $faculty = User::find($facultyId);
            $added[] = $faculty->name;

            // Centralized notification & activity logging
            $this->notificationService->notifyCollaboratorAdded($task, $faculty);
        }

        if (empty($added)) {
            return back()->with('error', 'Selected faculty are already assigned to this task.');
        }

        return back()->with('success', 'Collaborator(s) added: ' . implode(', ', $added));
    }

    /**
     * Remove a collaborator from a task (HOD only).
     */
    public function destroy(Task $task, User $user)
    {
        $this->authorize('removeCollaborator', $task);

        // Do not allow removing the task creator
        if ($user->id === $task->created_by) {
            return back()->with('error', 'Cannot remove the task creator.');
        }

        $task->assignees()->detach($user->id);
        TaskAssignment::where('task_id', $task->id)->where('faculty_id', $user->id)->delete();

        // Centralized notification & activity logging
        $this->notificationService->notifyCollaboratorRemoved($task, $user);

        return back()->with('success', "{$user->name} removed from this task.");
    }

    /**
     * Reassign a task from one assignee to another.
     * The old assignee is removed and the new assignee takes over.
     */
    public function reassign(Request $request, Task $task)
    {
        \Illuminate\Support\Facades\Log::info("Entering TaskCollaboratorController@reassign for Task {$task->id}");
        $this->authorize('reassign', $task);
        \Illuminate\Support\Facades\Log::info("Passed authorize('reassign') for Task {$task->id}");

        $validated = $request->validate([
            'new_assignee_id'      => 'required|exists:users,id',
            'old_assignee_id'      => 'nullable|exists:users,id',
            'reason'               => 'nullable|string|max:1000',
        ]);
        \Illuminate\Support\Facades\Log::info("Passed validation for Task {$task->id}");

        $user = auth()->user();
        $newAssigneeId = (int) $validated['new_assignee_id'];
        $reason = $validated['reason'] ?? null;

        // Determine which assignee is being replaced
        if ($user->isHod() || $user->isNbaCoordinator()) {
            // HOD/NBA can specify which assignee to replace
            $oldAssigneeId = (int) ($validated['old_assignee_id'] ?? 0);
            if (!$oldAssigneeId) {
                return back()->with('error', 'Please select the assignee to replace.');
            }
        } else {
            // Faculty can only reassign their own slot
            $oldAssigneeId = $user->id;
        }

        // Verify old assignee is actually on the task
        if (!$task->assignees()->where('user_id', $oldAssigneeId)->exists()) {
            return back()->with('error', 'Selected assignee is not currently on this task.');
        }

        // Cannot reassign to themselves
        if ($newAssigneeId === $oldAssigneeId) {
            return back()->with('error', 'Cannot reassign to the same person.');
        }

        $oldAssignee = User::findOrFail($oldAssigneeId);
        $newAssignee = User::findOrFail($newAssigneeId);

        // Get old assignee's pivot role to preserve the role type
        $oldPivot = $task->assignees()->where('user_id', $oldAssigneeId)->first();
        $oldRole = $oldPivot?->pivot?->role ?? 'owner';

        // Remove old assignee from the task
        $task->assignees()->detach($oldAssigneeId);

        // If new assignee is already on the task, upgrade them. Otherwise, attach them.
        if ($task->assignees()->where('user_id', $newAssigneeId)->exists()) {
            $task->assignees()->updateExistingPivot($newAssigneeId, [
                'role'          => $oldRole,
                'assigned_by'   => $user->id,
                'is_reassigned' => true,
            ]);
        } else {
            $task->assignees()->attach($newAssigneeId, [
                'status'              => 'pending',
                'progress_percentage' => 0,
                'role'                => $oldRole,
                'assigned_by'         => $user->id,
                'assigned_at'         => now(),
                'is_reassigned'       => true,
            ]);
        }

        // Create assignment tracking record
        TaskAssignment::create([
            'task_id'     => $task->id,
            'faculty_id'  => $newAssigneeId,
            'assigned_by' => $user->id,
            'role'        => $oldRole,
            'status'      => 'pending',
            'reason'      => $reason ? "Reassigned from {$oldAssignee->name}. {$reason}" : "Reassigned from {$oldAssignee->name}.",
            'assigned_at' => now(),
        ]);

        // Send notifications
        $this->notificationService->notifyTaskReassigned($task, $oldAssignee, $newAssignee, $reason);

        $message = "Task reassigned from {$oldAssignee->name} to {$newAssignee->name}.";

        // Check if the current user can still view the task
        if ($user->can('view', $task)) {
            return back()->with('success', $message);
        }

        // If they can no longer view it (e.g. Faculty who transferred their own task), redirect to their task list
        if ($user->isFaculty()) {
            return redirect()->route('faculty.tasks.index')->with('success', $message);
        }

        return redirect()->route('dashboard')->with('success', $message);
    }
}
