<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

class DeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public string $stage;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, string $stage = '50pct')
    {
        $this->task = $task;
        $this->stage = $stage;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = match ($this->stage) {
            '75pct' => 'Time Running Out: "' . $this->task->title . '" — 75% of your time is used, deadline approaching',
            '50pct' => 'Halfway There: "' . $this->task->title . '" — 50% of your time is used',
            default => 'Reminder: Task "' . $this->task->title . '" deadline reminder',
        };

        $logoPath = public_path('images/psg-logo-email.png');

        $mail = $this->subject($subject)
            ->markdown('emails.deadline-reminder', [
                'task' => $this->task,
                'stage' => $this->stage,
            ]);

        // Embed logo as CID inline attachment
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
