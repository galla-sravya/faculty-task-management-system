<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id', 'type', 'reference_id', 'reference_type',
        'message', 'is_read', 'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'task_assigned' => 'bi-clipboard-check',
            'deadline_reminder' => 'bi-alarm',
            'meeting_invite' => 'bi-calendar-event',
            'meeting_minutes' => 'bi-journal-text',
            'document_submitted' => 'bi-file-earmark-arrow-up',
            'document_approved' => 'bi-file-earmark-check',
            'document_changes_requested' => 'bi-file-earmark-arrow-down',
            'document_rejected' => 'bi-file-earmark-x',
            'document_uploaded' => 'bi-file-earmark-plus',
            'document_replaced' => 'bi-file-earmark-diff',
            default => 'bi-bell',
        };
    }
}
