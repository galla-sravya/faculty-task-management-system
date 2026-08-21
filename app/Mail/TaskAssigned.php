<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $task;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $logoPath = public_path('images/psg-logo-email.png');

        $mail = $this->subject('New Task Assigned: ' . $this->task->title)
            ->markdown('emails.task-assigned', [
                'task' => $this->task,
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
