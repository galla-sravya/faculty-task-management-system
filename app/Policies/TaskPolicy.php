<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view tasks
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true; // HOD, coordinators, and faculty can create tasks
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        if ($task->overall_progress > 0 || $task->documents()->exists() || $task->activities()->count() > 1 || $task->comments()->exists()) {
            return false;
        }

        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }
    
    public function updateProgress(User $user, Task $task): bool
    {
        return $task->assignees()->where('user_id', $user->id)->exists() || $task->created_by === $user->id;
    }

    public function addCollaborator(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function reassign(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function removeCollaborator(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }

    public function uploadDocument(User $user, Task $task): bool
    {
        return $task->assignees()->where('user_id', $user->id)->exists() || $task->created_by === $user->id;
    }

    public function reviewDocument(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        return $task->created_by === $user->id;
    }

    public function manageChecklist(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function updateChecklistItem(User $user, Task $task, \App\Models\TaskChecklistItem $item): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        // Faculty can edit items they created
        return $task->assignees()->where('user_id', $user->id)->exists() && $item->created_by === $user->id;
    }

    public function updateChecklistItemStatus(User $user, Task $task, \App\Models\TaskChecklistItem $item): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        // Faculty assigned to task can update status if assigned to them, assigned to all, or created by them
        if (!$task->assignees()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return is_null($item->assigned_to) || $item->assigned_to === $user->id || $item->created_by === $user->id;
    }

    public function deleteChecklistItem(User $user, Task $task, \App\Models\TaskChecklistItem $item): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        // Faculty CANNOT delete HOD-created requirements
        if ($item->created_by_role === 'hod' || $item->isHodRequirement()) {
            return false;
        }

        // Must be an assigned collaborator on the task
        if (!$task->assignees()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Faculty can delete work items created by them or assigned to them or shared items
        if ($item->created_by) {
            return $item->created_by === $user->id || $item->assigned_to === $user->id || $item->assigned_to === null;
        }

        return true;
    }
}
