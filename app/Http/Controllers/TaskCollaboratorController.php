<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskActivity;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;

class TaskCollaboratorController extends Controller
{
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
        $notifyHod = $request->boolean('notify_hod', false);

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

            // Activity log
            TaskActivity::create([
                'task_id'     => $task->id,
                'user_id'     => auth()->id(),
                'action'      => 'collaborator_added',
                'description' => auth()->user()->name . " added {$faculty->name} as " . str_replace('_', ' ', $role) . "." . ($reason ? " Reason: {$reason}" : ''),
            ]);

            // Notify the newly assigned faculty
            NotificationLog::create([
                'user_id'        => $facultyId,
                'type'           => 'collaborator_added',
                'reference_id'   => $task->id,
                'reference_type' => Task::class,
                'message'        => 'You have been added as a ' . str_replace('_', ' ', $role) . ' on task: ' . $task->title,
                'sent_at'        => now(),
            ]);
        }

        // Automatically notify the HOD who created/assigned the task (if current user is not that HOD)
        if (!empty($added) && !auth()->user()->isHod()) {
            $hod = ($task->creator && $task->creator->isHod())
                ? $task->creator
                : User::where('department_id', $task->department_id)->where('role', 'hod')->first();

            if ($hod && $hod->id !== auth()->id()) {
                $collaboratorsList = implode(', ', $added);
                $roleLabel = str_replace('_', ' ', $role);
                $performerName = auth()->user()->name;

                $message = "{$performerName} added {$collaboratorsList} as a {$roleLabel} for \"{$task->title}\"";

                NotificationLog::create([
                    'user_id'        => $hod->id,
                    'type'           => 'collaborator_added',
                    'reference_id'   => $task->id,
                    'reference_type' => Task::class,
                    'message'        => $message,
                    'sent_at'        => now(),
                ]);
            }
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

        TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => auth()->id(),
            'action'      => 'collaborator_removed',
            'description' => auth()->user()->name . " removed {$user->name} from the task.",
        ]);

        NotificationLog::create([
            'user_id'        => $user->id,
            'type'           => 'collaborator_removed',
            'reference_id'   => $task->id,
            'reference_type' => Task::class,
            'message'        => 'You have been removed from task: ' . $task->title,
            'sent_at'        => now(),
        ]);

        return back()->with('success', "{$user->name} removed from this task.");
    }
}
