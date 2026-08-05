<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use App\Models\TaskDocument;
use App\Models\TaskChecklistItem;
use App\Models\TaskComment;
use App\Models\NotificationLog;
use App\Models\TaskActivity;
use App\Models\TaskAuditLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssigned;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Helper to log notification log and prevent duplicate entries within 1 minute window.
     */
    protected function logNotification(int $userId, string $type, int $referenceId, string $referenceType, string $message): ?NotificationLog
    {
        // Duplicate prevention check
        $exists = NotificationLog::where('user_id', $userId)
            ->where('type', $type)
            ->where('reference_id', $referenceId)
            ->where('reference_type', $referenceType)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if ($exists) {
            return null;
        }

        return NotificationLog::create([
            'user_id'        => $userId,
            'type'           => $type,
            'reference_id'   => $referenceId,
            'reference_type' => $referenceType,
            'message'        => $message,
            'sent_at'        => now(),
        ]);
    }

    /**
     * Helper to record activity timeline entry.
     */
    protected function recordActivity(int $taskId, ?int $userId, string $action, string $description): TaskActivity
    {
        return TaskActivity::create([
            'task_id'     => $taskId,
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'description' => $description,
        ]);
    }

    /**
     * Helper to record audit log entry.
     */
    protected function recordAudit(int $taskId, ?int $userId, string $fieldName, ?string $oldValue, ?string $newValue): TaskAuditLog
    {
        return TaskAuditLog::create([
            'task_id'    => $taskId,
            'user_id'    => $userId ?? auth()->id(),
            'field_name' => $fieldName,
            'old_value'  => $oldValue,
            'new_value'  => $newValue,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  TASK EVENTS
    // ═══════════════════════════════════════════════════════════════

    public function notifyTaskAssigned(Task $task, array $assigneeIds, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordActivity($task->id, $actor?->id, 'assigned', 'Task created and assigned by ' . ($actor?->name ?? 'HOD') . '.');

        foreach ($assigneeIds as $userId) {
            $this->logNotification(
                $userId,
                'task_assigned',
                $task->id,
                Task::class,
                'You have been assigned a new task: ' . $task->title
            );

            $user = User::find($userId);
            if ($user && $user->email) {
                Mail::to($user)->send(new TaskAssigned($task));
            }
        }
    }

    public function notifyTaskUpdated(Task $task, array $changes, array $existingAssigneeIds, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        foreach ($changes as $field => $val) {
            $this->recordAudit($task->id, $actor?->id, $field, $val['old'], $val['new']);
            $this->recordActivity($task->id, $actor?->id, 'task_updated', ($actor?->name ?? 'HOD') . ' updated ' . ucfirst($field) . ' from "' . $val['old'] . '" to "' . $val['new'] . '".');
        }

        foreach ($existingAssigneeIds as $assigneeId) {
            $this->logNotification(
                $assigneeId,
                'task_updated',
                $task->id,
                Task::class,
                'Task details updated by HOD: ' . $task->title
            );
        }
    }

    public function notifyCollaboratorAdded(Task $task, User $collaborator, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'collaborator_added', null, $collaborator->name);
        $this->recordActivity($task->id, $actor?->id, 'collaborator_added', ($actor?->name ?? 'User') . ' added ' . $collaborator->name . ' as collaborator.');

        // 1. Notify the newly assigned collaborator
        $this->logNotification(
            $collaborator->id,
            'collaborator_added',
            $task->id,
            Task::class,
            'You have been added as a collaborator on task: ' . $task->title
        );

        if ($collaborator->email) {
            Mail::to($collaborator)->send(new TaskAssigned($task));
        }

        // 2. Notify the HOD if a non-HOD user added the collaborator
        if ($actor && !$actor->isHod()) {
            $hod = ($task->creator && $task->creator->isHod())
                ? $task->creator
                : User::where('department_id', $task->department_id)->where('role', 'hod')->first();

            if ($hod && $hod->id !== $actor->id) {
                $performerName = $actor->name;
                $message = "{$performerName} added {$collaborator->name} as a collaborator for \"{$task->title}\"";

                $this->logNotification(
                    $hod->id,
                    'collaborator_added',
                    $task->id,
                    Task::class,
                    $message
                );
            }
        }
    }

    public function notifyCollaboratorRemoved(Task $task, User $collaborator, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'collaborator_removed', $collaborator->name, null);
        $this->recordActivity($task->id, $actor?->id, 'collaborator_removed', ($actor?->name ?? 'HOD') . ' removed collaborator ' . $collaborator->name . '.');

        $this->logNotification(
            $collaborator->id,
            'collaborator_removed',
            $task->id,
            Task::class,
            'You have been removed from task: ' . $task->title
        );
    }

    public function notifyTaskArchived(Task $task, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'task_archived', 'active', 'archived');
        $this->recordActivity($task->id, $actor?->id, 'archived', 'Task archived by HOD ' . ($actor?->name ?? 'HOD') . '.');

        foreach ($task->assignees as $assignee) {
            $this->logNotification(
                $assignee->id,
                'task_archived',
                $task->id,
                Task::class,
                'Task has been archived: ' . $task->title
            );
        }
    }

    public function notifyTaskRestored(Task $task, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'task_restored', 'archived', 'active');
        $this->recordActivity($task->id, $actor?->id, 'restored', 'Task restored by HOD ' . ($actor?->name ?? 'HOD') . '.');

        foreach ($task->assignees as $assignee) {
            $this->logNotification(
                $assignee->id,
                'task_restored',
                $task->id,
                Task::class,
                'Task has been restored: ' . $task->title
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  SUBTASK EVENTS
    // ═══════════════════════════════════════════════════════════════

    public function notifySubtaskCreated(Task $task, TaskChecklistItem $item, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'subtask_created', null, $item->title);
        $this->recordActivity($task->id, $actor?->id, 'checklist_item_added', ($actor?->name ?? 'User') . ' added subtask: "' . $item->title . '"');

        foreach ($task->assignees as $assignee) {
            if ($assignee->id !== $actor?->id) {
                $this->logNotification(
                    $assignee->id,
                    'subtask_created',
                    $task->id,
                    Task::class,
                    ($actor?->name ?? 'User') . ' added subtask "' . $item->title . '" to task: ' . $task->title
                );
            }
        }
    }

    public function notifySubtaskCompleted(Task $task, TaskChecklistItem $item, bool $isCompleted, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, $isCompleted ? 'subtask_completed' : 'subtask_reopened', $isCompleted ? 'Pending' : 'Completed', $isCompleted ? 'Completed: ' . $item->title : 'Pending: ' . $item->title);
        $this->recordActivity($task->id, $actor?->id, 'checklist_item_toggled', ($actor?->name ?? 'User') . ($isCompleted ? ' completed' : ' uncompleted') . ' subtask: "' . $item->title . '"');

        $notifyUserIds = $task->assignees->pluck('id')->push($task->created_by)->unique()->reject(fn ($id) => $id === $actor?->id);
        foreach ($notifyUserIds as $uId) {
            $this->logNotification(
                $uId,
                'subtask_completed',
                $task->id,
                Task::class,
                ($actor?->name ?? 'User') . ($isCompleted ? ' completed' : ' reopened') . ' subtask "' . $item->title . '" on task: ' . $task->title
            );
        }
    }

    public function notifySubtaskDeleted(Task $task, string $title, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordAudit($task->id, $actor?->id, 'subtask_deleted', $title, null);
        $this->recordActivity($task->id, $actor?->id, 'checklist_item_deleted', ($actor?->name ?? 'User') . ' removed subtask: "' . $title . '"');
    }

    // ═══════════════════════════════════════════════════════════════
    //  PROGRESS EVENTS & AUTOMATIC CALCULATION ENGINE
    // ═══════════════════════════════════════════════════════════════

    public function calculateTaskAutomaticProgress(Task $task): int
    {
        $hasDocs = $task->documents()->exists();
        $allDocsApproved = $hasDocs &&
            $task->documents()->where('review_status', 'approved')->exists() &&
            !$task->documents()->whereIn('review_status', ['draft', 'submitted', 'changes_requested', 'rejected'])->exists();

        // Milestone 1: Approved by HOD / Status Completed -> 100%
        if ($task->status === 'completed' || $allDocsApproved) {
            return 100;
        }

        // Milestone 2: Submitted for Review -> 90%
        $hasSubmittedDoc = $task->documents()->where('review_status', 'submitted')->exists();
        if (in_array($task->status, ['submitted_for_review', 'pending_review']) || $hasSubmittedDoc) {
            return 90;
        }

        $totalChecklist = $task->checklistItems()->count();
        $completedChecklist = $task->checklistItems()->where('is_completed', true)->count();
        $checklistRatio = $totalChecklist > 0 ? ($completedChecklist / $totalChecklist) : 0;

        // Milestone 3: Stage-based automatic calculation
        return match ($task->status) {
            'checklist_completed' => 75,
            'documents_uploaded'  => 55,
            'working_on_task'     => 35,
            'in_progress'         => $hasDocs
                ? (int) round(30 + ($checklistRatio * 40))
                : ($totalChecklist > 0 ? (int) min(70, round(10 + ($checklistRatio * 80))) : 10),
            'collecting_resources' => 15,
            'not_started', 'pending' => $hasDocs
                ? (int) round(30 + ($checklistRatio * 40))
                : ($totalChecklist > 0 ? (int) min(75, round($checklistRatio * 100)) : 0),
            default => $hasDocs ? 55 : ($totalChecklist > 0 ? (int) round($checklistRatio * 70) : 0),
        };
    }

    public function updateAutomaticTaskProgress(Task $task, string $triggerReason = 'activity update'): void
    {
        $task->refresh();
        $task->load('assignees');
        $actor = auth()->user();

        $oldProgress = $task->overall_progress;
        $newProgress = $this->calculateTaskAutomaticProgress($task);

        // Update pivot table for all assignees
        foreach ($task->assignees as $assignee) {
            $pivotStatus = $task->status;
            if ($newProgress === 100) {
                $pivotStatus = 'completed';
            }

            $task->assignees()->updateExistingPivot($assignee->id, [
                'progress_percentage' => $newProgress,
                'status'              => $pivotStatus,
                'completed_at'        => $newProgress === 100 ? now() : null,
            ]);
        }

        if ($actor && $task->assignees()->where('user_id', $actor->id)->exists()) {
            $pivotStatus = $task->status;
            if ($newProgress === 100) {
                $pivotStatus = 'completed';
            }
            $task->assignees()->updateExistingPivot($actor->id, [
                'progress_percentage' => $newProgress,
                'status'              => $pivotStatus,
                'completed_at'        => $newProgress === 100 ? now() : null,
            ]);
        }

        $task->load('assignees');

        // Update main task status if completed or submitted
        if ($newProgress === 100 && $task->status !== 'completed') {
            $task->update(['status' => 'completed']);
        } elseif ($newProgress >= 90 && !in_array($task->status, ['completed', 'submitted_for_review', 'pending_review'])) {
            $task->update(['status' => 'submitted_for_review']);
        }

        // Create timeline entry and audit log if progress changed
        if ($oldProgress !== $newProgress) {
            $this->recordAudit($task->id, $actor?->id, 'progress_percentage', $oldProgress . '%', $newProgress . '%');
            $this->recordActivity($task->id, $actor?->id, 'progress_updated', "Progress updated to {$newProgress}% after {$triggerReason}.");

            // Notify HOD / Task Owner at important milestones (30%, 70%, 90%, 100%)
            $milestones = [30, 70, 90, 100];
            $hitMilestone = null;
            foreach ($milestones as $m) {
                if ($oldProgress < $m && $newProgress >= $m) {
                    $hitMilestone = $m;
                }
            }

            if ($hitMilestone !== null) {
                $recipientIds = $task->assignees->pluck('id')
                    ->push($task->created_by)
                    ->unique()
                    ->reject(fn ($id) => $id === $actor?->id);

                foreach ($recipientIds as $recipientId) {
                    $this->logNotification(
                        $recipientId,
                        'progress_updated',
                        $task->id,
                        Task::class,
                        "Progress updated to {$newProgress}% for task: \"{$task->title}\" after {$triggerReason}."
                    );
                }
            }
        }
    }

    public function notifyProgressUpdated(Task $task, int $oldProgress, int $newProgress, string $status, ?string $remarks, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        if ($oldProgress !== $newProgress) {
            $this->recordAudit($task->id, $actor?->id, 'progress_percentage', $oldProgress . '%', $newProgress . '%');
        }

        $action = $status === 'completed' ? 'completed' : 'progress_updated';
        $desc = ($actor?->name ?? 'User') . " updated status to " . ucfirst(str_replace('_', ' ', $status)) . " (Progress: {$newProgress}%).";
        if (!empty($remarks)) {
            $desc .= " Remarks: \"{$remarks}\"";
        }
        $this->recordActivity($task->id, $actor?->id, $action, $desc);

        $hodId = $task->created_by;
        if ($hodId && $hodId !== $actor?->id) {
            $this->logNotification(
                $hodId,
                'progress_updated',
                $task->id,
                Task::class,
                ($actor?->name ?? 'User') . " updated status on \"{$task->title}\" to " . ucfirst(str_replace('_', ' ', $status)) . "."
            );
        }
    }

    public function notifyAllAssigneesCompleted(Task $task, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordActivity($task->id, $actor?->id, 'completed', 'All assignees completed the task. Task marked as fully completed.');
    }

    // ═══════════════════════════════════════════════════════════════
    //  DOCUMENT EVENTS
    // ═══════════════════════════════════════════════════════════════

    public function notifyDocumentUploaded(Task $task, int $count, TaskDocument $doc, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();
        $actorName = $actor?->name ?? 'User';

        $this->recordActivity($task->id, $actor?->id, 'document_uploaded', "{$actorName} uploaded document \"{$doc->file_name}\".");

        $recipientIds = $task->assignees->pluck('id')
            ->push($task->created_by)
            ->unique()
            ->reject(fn ($id) => $id === $actor?->id);

        foreach ($recipientIds as $recipientId) {
            $this->logNotification(
                $recipientId,
                'document_uploaded',
                $task->id,
                Task::class,
                "{$actorName} uploaded \"{$doc->file_name}\" for Task: {$task->title}."
            );
        }
    }

    public function notifyDocumentReuploaded(Task $task, TaskDocument $doc, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();
        $actorName = $actor?->name ?? 'User';

        $this->recordActivity($task->id, $actor?->id, 'document_replaced', "{$actorName} uploaded a new version of \"{$doc->file_name}\" (v{$doc->version}).");

        $recipientIds = $task->assignees->pluck('id')
            ->push($task->created_by)
            ->unique()
            ->reject(fn ($id) => $id === $actor?->id);

        foreach ($recipientIds as $recipientId) {
            $this->logNotification(
                $recipientId,
                'document_replaced',
                $task->id,
                Task::class,
                "{$actorName} uploaded \"{$doc->file_name}\" (v{$doc->version}) for Task: {$task->title}."
            );
        }
    }

    public function notifySubmittedForReview(Task $task, int $count, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordActivity($task->id, $actor?->id, 'document_submitted', ($actor?->name ?? 'User') . ' submitted ' . $count . ' document(s) for review.');

        $hodId = $task->created_by;
        if ($hodId && $hodId !== $actor?->id) {
            $this->logNotification(
                $hodId,
                'document_submitted',
                $task->id,
                Task::class,
                ($actor?->name ?? 'User') . ' submitted ' . $count . ' document(s) for review on task: "' . $task->title . '".'
            );
        }
    }

    public function notifyDocumentReviewed(Task $task, TaskDocument $doc, string $action, ?string $comments, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $actionLabels = [
            'approved'          => 'approved',
            'changes_requested' => 'requested changes for',
            'rejected'          => 'rejected',
        ];

        $desc = ($actor?->name ?? 'HOD') . ' ' . ($actionLabels[$action] ?? $action) . ' document "' . $doc->file_name . '".';
        if ($comments) {
            $desc .= ' Comments: "' . $comments . '"';
        }

        $this->recordActivity($task->id, $actor?->id, 'document_' . ($action === 'changes_requested' ? 'changes_requested' : $action), $desc);

        $notificationType = match ($action) {
            'approved'          => 'document_approved',
            'changes_requested' => 'document_changes_requested',
            'rejected'          => 'document_rejected',
        };

        $notificationMessage = match ($action) {
            'approved'          => 'Your document "' . $doc->file_name . '" for task "' . $task->title . '" has been approved.',
            'changes_requested' => 'Changes requested for your document "' . $doc->file_name . '" on task "' . $task->title . '". Comments: "' . $comments . '"',
            'rejected'          => 'Your document "' . $doc->file_name . '" for task "' . $task->title . '" has been rejected. Reason: "' . $comments . '"',
        };

        $this->logNotification(
            $doc->user_id,
            $notificationType,
            $task->id,
            Task::class,
            $notificationMessage
        );
    }

    // ═══════════════════════════════════════════════════════════════
    //  COMMENT EVENTS
    // ═══════════════════════════════════════════════════════════════

    public function notifyCommentAdded(Task $task, TaskComment $comment, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        $this->recordActivity($task->id, $actor?->id, 'comment_added', ($actor?->name ?? 'User') . ' added a comment: "' . Str::limit($comment->comment, 50) . '"');

        $recipients = $task->assignees->pluck('id')->push($task->created_by)->unique()->reject(fn ($id) => $id === $actor?->id);

        foreach ($recipients as $recipientId) {
            $this->logNotification(
                $recipientId,
                'comment_added',
                $task->id,
                Task::class,
                ($actor?->name ?? 'User') . ' commented on task "' . $task->title . '".'
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  MEETING EVENTS
    // ═══════════════════════════════════════════════════════════════

    public function notifyMeetingScheduled(Meeting $meeting, array $participantIds, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        foreach ($participantIds as $userId) {
            $this->logNotification(
                $userId,
                'meeting_scheduled',
                $meeting->id,
                Meeting::class,
                'New meeting scheduled by HOD: ' . $meeting->title . ' on ' . $meeting->meeting_date->format('M d, Y H:i')
            );
        }
    }

    public function notifyMeetingUpdated(Meeting $meeting, array $participantIds, ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();

        foreach ($participantIds as $userId) {
            $this->logNotification(
                $userId,
                'meeting_updated',
                $meeting->id,
                Meeting::class,
                'Meeting details updated by HOD: ' . $meeting->title
            );
        }
    }
}
