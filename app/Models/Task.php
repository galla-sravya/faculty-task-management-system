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
            ->withPivot(['status', 'progress_percentage', 'remarks', 'completed_at', 'role', 'assigned_by', 'assigned_at', 'is_reassigned'])
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
        return $this->deadline && $this->deadline->isPast() && $this->status !== 'completed';
    }

    public function getSmartDeadlineAttribute(): array
    {
        if ($this->status === 'completed') {
            return [
                'label' => 'Completed',
                'text' => $this->deadline ? $this->deadline->format('M d, Y') : 'Completed',
                'badge_class' => 'badge bg-success text-white',
                'color_class' => 'text-success fw-bold',
                'type' => 'completed',
                'days' => 0,
            ];
        }

        if (!$this->deadline) {
            return [
                'label' => 'No Deadline',
                'text' => '-',
                'badge_class' => 'badge bg-secondary text-white',
                'color_class' => 'text-muted',
                'type' => 'none',
                'days' => 0,
            ];
        }

        $now = Carbon::now()->startOfDay();
        $deadlineDay = $this->deadline->copy()->startOfDay();

        if ($deadlineDay->isPast()) {
            $daysOverdue = (int) $deadlineDay->diffInDays($now);
            if ($daysOverdue === 0) {
                return [
                    'label' => 'Due Today',
                    'text' => 'Due Today',
                    'badge_class' => 'badge bg-warning text-dark',
                    'color_class' => 'text-warning-emphasis fw-bold',
                    'type' => 'today',
                    'days' => 0,
                ];
            }
            $label = $daysOverdue === 1 ? 'Overdue by 1 day' : "Overdue by {$daysOverdue} days";
            return [
                'label' => $label,
                'text' => $label,
                'badge_class' => 'badge bg-danger text-white',
                'color_class' => 'text-danger fw-bold',
                'type' => 'overdue',
                'days' => -$daysOverdue,
            ];
        }

        if ($deadlineDay->isToday()) {
            return [
                'label' => 'Due Today',
                'text' => 'Due Today',
                'badge_class' => 'badge bg-warning text-dark',
                'color_class' => 'text-warning-emphasis fw-bold',
                'type' => 'today',
                'days' => 0,
            ];
        }

        if ($deadlineDay->isTomorrow()) {
            return [
                'label' => 'Due Tomorrow',
                'text' => 'Due Tomorrow',
                'badge_class' => 'badge bg-warning text-dark',
                'color_class' => 'text-warning-emphasis fw-semibold',
                'type' => 'tomorrow',
                'days' => 1,
            ];
        }

        $daysUntil = (int) $now->diffInDays($deadlineDay);
        if ($daysUntil <= 7) {
            $label = "Due in {$daysUntil} days";
            return [
                'label' => $label,
                'text' => $label,
                'badge_class' => 'badge bg-warning text-dark',
                'color_class' => 'text-dark fw-semibold',
                'type' => 'upcoming_7',
                'days' => $daysUntil,
            ];
        }

        return [
            'label' => $this->deadline->format('M d, Y'),
            'text' => $this->deadline->format('M d, Y'),
            'badge_class' => 'badge bg-light text-dark border',
            'color_class' => 'text-muted',
            'type' => 'normal',
            'days' => $daysUntil,
        ];
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->deadline || !$this->is_overdue) return 0;
        return (int) $this->deadline->diffInDays(Carbon::now());
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
