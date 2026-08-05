<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'title', 'description', 'organized_by', 'department_id',
        'scheduled_at', 'ended_at', 'venue', 'agenda', 'minutes', 'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organized_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_user')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success', 'in_progress' => 'info',
            'scheduled' => 'primary', 'cancelled' => 'danger', default => 'secondary',
        };
    }
}
