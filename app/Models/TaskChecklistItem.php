<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklistItem extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'description',
        'created_by',
        'created_by_role',
        'assigned_to',
        'status',
        'is_completed',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Whether this item was created by the HOD.
     */
    public function isHodRequirement(): bool
    {
        return $this->created_by_role === 'hod';
    }

    /**
     * Whether this item is assigned to all collaborators (assigned_to = null).
     */
    public function isAssignedToAll(): bool
    {
        return is_null($this->assigned_to);
    }

    /**
     * Get the display label for the status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            default       => 'Pending',
        };
    }

    /**
     * Get badge styling for the status.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
            'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
            default       => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
        };
    }
}
