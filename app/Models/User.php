<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'coordinator_type_id',
        'phone',
        'designation',
        'profile_photo_path',
        'specialization',
        'google_scholar',
        'orcid',
        'google_site',
    ];

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path && file_exists(public_path('storage/' . $this->profile_photo_path))) {
            return asset('storage/' . $this->profile_photo_path);
        }
        return asset('storage/faculty/default-avatar.png');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isHod(): bool
    {
        return $this->role === 'hod';
    }

    public function isFaculty(): bool
    {
        return $this->role === 'faculty';
    }

    public function isNbaCoordinator(): bool
    {
        return $this->role === 'nba_coordinator';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCoordinator(): bool
    {
        return $this->role === 'coordinator';
    }

    public function coordinatorType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CoordinatorType::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedTasks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot(['status', 'progress_percentage', 'remarks', 'completed_at', 'role', 'assigned_by', 'assigned_at', 'is_reassigned'])
            ->withTimestamps();
    }

    public function createdTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskDocument::class);
    }

    public function meetings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_user')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
