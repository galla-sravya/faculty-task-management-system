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

        if ($user->isNbaCoordinator()) {
            return $task->owner_role === 'nba_coordinator' && $user->department_id === $task->department_id;
        }
        
        if ($user->isCoordinator()) {
            return $task->owner_role === $user->coordinatorType?->slug && $user->department_id === $task->department_id;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isHod() || $user->isNbaCoordinator() || $user->isFaculty();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function restore(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        if ($task->overall_progress > 0 || $task->documents()->exists() || $task->activities()->count() > 1 || $task->comments()->exists()) {
            return false;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
    }
    
    public function updateProgress(User $user, Task $task): bool
    {
        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function addCollaborator(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function reassign(User $user, Task $task): bool
    {
        \Illuminate\Support\Facades\Log::info("TaskPolicy@reassign called for User {$user->id} ({$user->role}) on Task {$task->id}");
        
        if ($user->isHod()) {
            $result = $user->department_id === $task->department_id;
            \Illuminate\Support\Facades\Log::info("HOD check: {$user->department_id} === {$task->department_id} -> " . ($result ? 'true' : 'false'));
            return $result;
        }

        if ($user->isNbaCoordinator()) {
            $result = $task->created_by === $user->id;
            \Illuminate\Support\Facades\Log::info("NBA check: {$task->created_by} === {$user->id} -> " . ($result ? 'true' : 'false'));
            return $result;
        }

        $result = $task->assignees()->where('user_id', $user->id)->exists();
        \Illuminate\Support\Facades\Log::info("Faculty check: assignee exists -> " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function removeCollaborator(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
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

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        return false;
    }

    public function manageChecklist(User $user, Task $task): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id || $task->assignees()->where('user_id', $user->id)->exists();
        }

        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function updateChecklistItem(User $user, Task $task, \App\Models\TaskChecklistItem $item): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
        }

        // Faculty can edit items they created
        return $task->assignees()->where('user_id', $user->id)->exists() && $item->created_by === $user->id;
    }

    public function updateChecklistItemStatus(User $user, Task $task, \App\Models\TaskChecklistItem $item): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $task->department_id;
        }

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id || $task->assignees()->where('user_id', $user->id)->exists();
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

        if ($user->isNbaCoordinator()) {
            return $task->created_by === $user->id;
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
