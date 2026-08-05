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
}
