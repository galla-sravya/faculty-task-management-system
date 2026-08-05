<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TaskComment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'comment',
        'attachment_path',
        'attachment_name',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment_path && Storage::disk('public')->exists($this->attachment_path)) {
            return Storage::url($this->attachment_path);
        }
        return null;
    }
}
