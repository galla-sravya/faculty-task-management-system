<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskReassigned extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public User $actor;
    public User $oldAssignee;
    public User $newAssignee;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $actor, User $oldAssignee, User $newAssignee, ?string $reason)
    {
        $this->task = $task;
        $this->actor = $actor;
        $this->oldAssignee = $oldAssignee;
        $this->newAssignee = $newAssignee;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Task Reassigned to You: ' . $this->task->title;

        $logoPath = public_path('images/psg-logo-email.png');

        $mail = $this->subject($subject)
                     ->markdown('emails.task-reassigned');

        if (file_exists($logoPath)) {
            $mail->withSymfonyMessage(function ($message) use ($logoPath) {
                $message->embed(
                    fopen($logoPath, 'r'),
                    'psg-logo.png',
                    'image/png'
                );
            });
        }

        return $mail;
    }
}
