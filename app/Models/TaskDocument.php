<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TaskDocument extends Model
{
    protected $fillable = [
        'task_id', 'user_id', 'file_name', 'file_path',
        'version', 'original_document_id', 'remarks',
        'review_status', 'review_comments', 'reviewed_by', 'reviewed_at',
        'file_size', 'file_type',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /* ── Relationships ── */

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function originalDocument(): BelongsTo
    {
        return $this->belongsTo(TaskDocument::class, 'original_document_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TaskDocument::class, 'original_document_id')->orderBy('version', 'desc');
    }

    /* ── Scopes ── */

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('review_status', 'submitted');
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->where('review_status', 'submitted');
    }

    public function scopeLatestVersions(Builder $query): Builder
    {
        return $query->whereNull('original_document_id')
                     ->orWhere(function ($q) {
                         $q->whereNotNull('original_document_id');
                     });
    }

    /* ── Accessors ── */

    public function getReviewStatusBadgeAttribute(): array
    {
        return match ($this->review_status) {
            'draft'             => ['bg' => '#f0f2f5', 'text' => '#6b7280', 'label' => 'Draft'],
            'submitted'         => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Submitted'],
            'approved'          => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Approved'],
            'changes_requested' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Changes Requested'],
            'rejected'          => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Rejected'],
            default             => ['bg' => '#f0f2f5', 'text' => '#6b7280', 'label' => ucfirst($this->review_status)],
        };
    }

    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'               => 'bi-file-earmark-pdf-fill',
            'doc', 'docx'       => 'bi-file-earmark-word-fill',
            'xls', 'xlsx'       => 'bi-file-earmark-excel-fill',
            'ppt', 'pptx'       => 'bi-file-earmark-ppt-fill',
            'zip', 'rar'        => 'bi-file-earmark-zip-fill',
            'png', 'jpg', 'jpeg' => 'bi-file-earmark-image-fill',
            default             => 'bi-file-earmark-fill',
        };
    }

    public function getFileIconColorAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'               => '#c62828',
            'doc', 'docx'       => '#1565c0',
            'xls', 'xlsx'       => '#2e7d32',
            'ppt', 'pptx'      => '#e65100',
            'zip', 'rar'        => '#f57f17',
            'png', 'jpg', 'jpeg' => '#7b1fa2',
            default             => '#6b7280',
        };
    }

    public function getIsPreviewableAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['pdf', 'png', 'jpg', 'jpeg']);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    /**
     * Get all versions of this document (including self).
     * If this IS the original, return self + versions.
     * If this is a child version, go up to original and get all.
     */
    public function getAllVersions()
    {
        $rootId = $this->original_document_id ?? $this->id;
        return static::where('id', $rootId)
            ->orWhere('original_document_id', $rootId)
            ->orderBy('version', 'desc')
            ->get();
    }
}
