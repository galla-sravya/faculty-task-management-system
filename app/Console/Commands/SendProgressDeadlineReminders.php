<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\NotificationLog;
use App\Mail\DeadlineReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendProgressDeadlineReminders extends Command
{
    protected $signature = 'reminders:send-progress';
    protected $description = 'Send 50% and 75% elapsed time reminders for pending tasks approaching their deadline';

    public function handle()
    {
        $this->info('Checking percentage-of-elapsed-time for active tasks...');
        $now = Carbon::now();

        // Fetch all active tasks with future deadlines
        $tasks = Task::where('status', '!=', 'completed')
            ->where('deadline', '>=', $now)
            ->with('assignees')
            ->get();

        foreach ($tasks as $task) {
            $created = $task->created_at ?? $now;
            $totalSeconds = $created->diffInSeconds($task->deadline, false);
            
            // Skip if total time window is invalid or zero
            if ($totalSeconds <= 0) {
                continue;
            }

            $elapsedSeconds = $created->diffInSeconds($now, false);
            if ($elapsedSeconds <= 0) {
                $percentElapsed = 0;
            } else {
                $percentElapsed = ($elapsedSeconds / $totalSeconds) * 100;
            }

            $milestone50Time = $created->copy()->addSeconds($totalSeconds * 0.5);
            $milestone75Time = $created->copy()->addSeconds($totalSeconds * 0.75);

            // Check for 50% elapsed time threshold
            if ($percentElapsed >= 50 && is_null($task->reminder_50pct_sent_at)) {
                foreach ($task->assignees as $assignee) {
                    $assignedAt = \Carbon\Carbon::parse($assignee->pivot->assigned_at ?? $created);
                    if ($assignee->pivot->status !== 'completed' && $assignedAt->lessThanOrEqualTo($milestone50Time)) {
                        Mail::to($assignee->email)->send(new DeadlineReminder($task, '50pct'));

                        NotificationLog::create([
                            'user_id' => $assignee->id,
                            'type' => 'deadline_reminder',
                            'reference_id' => $task->id,
                            'reference_type' => Task::class,
                            'message' => 'REMINDER: Halfway through time for task "' . $task->title . '" — 50% time elapsed.',
                            'sent_at' => $now,
                        ]);
                    }
                }
                $task->update(['reminder_50pct_sent_at' => $now]);
                $this->info("Sent 50pct progress reminder for task: {$task->id}");
            }

            // Check for 75% elapsed time threshold (do not skip if 50% fired in the same run)
            if ($percentElapsed >= 75 && is_null($task->reminder_75pct_sent_at)) {
                foreach ($task->assignees as $assignee) {
                    $assignedAt = \Carbon\Carbon::parse($assignee->pivot->assigned_at ?? $created);
                    if ($assignee->pivot->status !== 'completed' && $assignedAt->lessThanOrEqualTo($milestone75Time)) {
                        Mail::to($assignee->email)->send(new DeadlineReminder($task, '75pct'));

                        NotificationLog::create([
                            'user_id' => $assignee->id,
                            'type' => 'deadline_reminder',
                            'reference_id' => $task->id,
                            'reference_type' => Task::class,
                            'message' => 'URGENT: Time running out for task "' . $task->title . '" — 75% time elapsed!',
                            'sent_at' => $now,
                        ]);
                    }
                }
                $task->update(['reminder_75pct_sent_at' => $now]);
                $this->info("Sent 75pct progress reminder for task: {$task->id}");
            }
        }

        $this->info('Done sending progress deadline reminders.');
        return 0;
    }
}
