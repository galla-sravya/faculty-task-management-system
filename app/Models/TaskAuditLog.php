<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAuditLog extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedFieldNameAttribute(): string
    {
        return match ($this->field_name) {
            'title'               => 'Task Title',
            'description'         => 'Description',
            'priority'            => 'Priority Level',
            'category'            => 'Category',
            'deadline'            => 'Deadline Date',
            'collaborator_added'  => 'Collaborator Added',
            'collaborator_removed'=> 'Collaborator Removed',
            'status'              => 'Status',
            'meeting_id'          => 'Linked Meeting',
            default               => ucfirst(str_replace('_', ' ', $this->field_name)),
        };
    }
}
