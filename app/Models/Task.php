<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'created_by', 'department_id', 'meeting_id',
        'priority', 'category', 'status', 'deadline', 'reminder_48h_sent_at', 'reminder_12h_sent_at', 'reminder_2h_sent_at',
        'deadline_day_morning_sent_at', 'deadline_day_evening_sent_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'reminder_48h_sent_at' => 'datetime',
        'reminder_12h_sent_at' => 'datetime',
        'reminder_2h_sent_at' => 'datetime',
        'deadline_day_morning_sent_at' => 'datetime',
        'deadline_day_evening_sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot(['status', 'progress_percentage', 'remarks', 'completed_at', 'role', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TaskDocument::class);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('deadline', '<', Carbon::now())->where('status', '!=', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    public function getOverallProgressAttribute(): int
    {
        $assignees = $this->assignees;
        if ($assignees->isEmpty()) return 0;
        return (int) round($assignees->avg('pivot.progress_percentage'));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline->isPast() && $this->status !== 'completed';
    }

    public function getAssignedDateAttribute(): Carbon
    {
        return $this->created_at ?? Carbon::now();
    }

    public function getDurationInDaysAttribute(): int
    {
        $start = $this->assigned_date;
        $end = $this->deadline;
        if (!$start || !$end) return 0;
        return max(1, (int) $start->diffInDays($end));
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'success', default => 'secondary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success', 'in_progress' => 'info', 'pending_review' => 'primary', 'overdue' => 'danger', 'pending' => 'warning', default => 'secondary',
        };
    }
}
