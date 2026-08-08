<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorAdded extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public User $actor;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $actor)
    {
        $this->task = $task;
        $this->actor = $actor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Added as Collaborator: ' . $this->task->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.collaborator-added',
        );
    }
}
