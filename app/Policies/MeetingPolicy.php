<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Meeting $meeting): bool
    {
        if ($user->isHod()) {
            return $user->department_id === $meeting->department_id;
        }
        
        return $meeting->attendees()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isHod();
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->isHod() && $user->department_id === $meeting->department_id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->isHod() && $user->department_id === $meeting->department_id;
    }

    public function restore(User $user, Meeting $meeting): bool
    {
        return $user->isHod() && $user->department_id === $meeting->department_id;
    }

    public function forceDelete(User $user, Meeting $meeting): bool
    {
        return $user->isHod() && $user->department_id === $meeting->department_id;
    }
}
