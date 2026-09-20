<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\NBATask;
use App\Models\User;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Task|NBATask $task;
    public User $recipient;
    public object $sender;
    public ?string $customMessage;
    public bool $isOverdue;
    public bool $isAutomated;

    public function __construct(
        Task|NBATask $task, 
        User $recipient, 
        object $sender, 
        ?string $customMessage = null, 
        bool $isAutomated = false
    ) {
        $this->task          = $task;
        $this->recipient     = $recipient;
        $this->sender        = $sender;
        $this->customMessage = $customMessage;
        $this->isAutomated   = $isAutomated;
        $this->isOverdue     = $task->deadline && $task->deadline->isPast();
    }

    public function envelope(): Envelope
    {
        if ($this->isAutomated) {
            $prefix = $this->isOverdue ? '⚠️ [OVERDUE REMINDER]' : '⏰ [DAILY REMINDER]';
            $subject = "{$prefix} Task \"{$this->task->title}\" is pending completion";
        } else {
            $prefix = $this->isOverdue ? '⚠️ [Overdue Reminder]' : '⏰ [Task Reminder]';
            $subject = "{$prefix} Reminder from {$this->sender->name} for \"{$this->task->title}\"";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.task-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
