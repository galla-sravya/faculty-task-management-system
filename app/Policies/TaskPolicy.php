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
            return $task->created_by === $user->id || $task->assignees()->where('user_id', $user->id)->exists();
        }
        
        return $task->assignees()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isHod() || $user->isNbaCoordinator();
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
}
