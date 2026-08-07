<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Task extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title', 'description', 'created_by', 'owner_role', 'department_id', 'meeting_id',
        'priority', 'category', 'status', 'deadline', 'reminder_50pct_sent_at', 'reminder_75pct_sent_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (!$task->owner_role && auth()->check()) {
                $task->owner_role = auth()->user()->role;
            }
        });
    }

    protected $casts = [
        'deadline' => 'datetime',
        'reminder_50pct_sent_at' => 'datetime',
        'reminder_75pct_sent_at' => 'datetime',
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

    public function auditLogs(): HasMany
    {
        return $this->hasMany(TaskAuditLog::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id')->with('replies')->latest();
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('id', 'asc');
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
            'completed', 'checklist_completed' => 'success',
            'submitted_for_review', 'pending_review' => 'warning',
            'documents_uploaded' => 'indigo',
            'working_on_task', 'in_progress' => 'primary',
            'collecting_resources' => 'info',
            'overdue' => 'danger',
            'not_started', 'pending' => 'secondary',
            default => 'secondary',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'Not Started',
            'collecting_resources' => 'Collecting Resources',
            'working_on_task' => 'Working on Task',
            'documents_uploaded' => 'Supporting Documents Uploaded',
            'checklist_completed' => 'Checklist Completed',
            'submitted_for_review' => 'Submitted for Review',
            'pending_review' => 'Submitted for Review',
            'completed' => 'Completed',
            'in_progress' => 'Working on Task',
            'pending' => 'Not Started',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeDetailsAttribute(): array
    {
        return match($this->status) {
            'not_started', 'pending' => [
                'bg' => '#6c757d',
                'text' => '#ffffff',
                'class' => 'bg-secondary text-white',
                'label' => 'Not Started',
            ],
            'collecting_resources' => [
                'bg' => '#0dcaf0',
                'text' => '#000000',
                'class' => 'bg-info text-dark',
                'label' => 'Collecting Resources',
            ],
            'working_on_task', 'in_progress' => [
                'bg' => '#0d6efd',
                'text' => '#ffffff',
                'class' => 'bg-primary text-white',
                'label' => 'Working on Task',
            ],
            'documents_uploaded' => [
                'bg' => '#6f42c1',
                'text' => '#ffffff',
                'class' => 'text-white',
                'style' => 'background-color: #6f42c1; color: #ffffff;',
                'label' => 'Supporting Documents Uploaded',
            ],
            'checklist_completed' => [
                'bg' => '#198754',
                'text' => '#ffffff',
                'class' => 'bg-success text-white',
                'label' => 'Checklist Completed',
            ],
            'submitted_for_review', 'pending_review' => [
                'bg' => '#ffc107',
                'text' => '#000000',
                'class' => 'bg-warning text-dark',
                'label' => 'Submitted for Review',
            ],
            'completed' => [
                'bg' => '#198754',
                'text' => '#ffffff',
                'class' => 'bg-success text-white',
                'label' => 'Completed',
            ],
            'overdue' => [
                'bg' => '#dc3545',
                'text' => '#ffffff',
                'class' => 'bg-danger text-white',
                'label' => 'Overdue',
            ],
            default => [
                'bg' => '#6c757d',
                'text' => '#ffffff',
                'class' => 'bg-secondary text-white',
                'label' => ucfirst(str_replace('_', ' ', $this->status)),
            ],
        };
    }
}
