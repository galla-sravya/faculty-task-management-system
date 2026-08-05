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
        
        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isHod();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
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
        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function removeCollaborator(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
    }

    public function uploadDocument(User $user, Task $task): bool
    {
        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function reviewDocument(User $user, Task $task): bool
    {
        return $user->isHod() && $user->department_id === $task->department_id;
    }
}
