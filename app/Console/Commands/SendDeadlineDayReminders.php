<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\NotificationLog;
use App\Mail\DeadlineReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDeadlineDayReminders extends Command
{
    protected $signature = 'reminders:send-deadline-day {period : morning|evening}';
    protected $description = 'Send morning or evening deadline day reminders for tasks due today';

    public function handle()
    {
        $period = strtolower($this->argument('period'));

        if (!in_array($period, ['morning', 'evening'])) {
            $this->error('Invalid period specified. Must be "morning" or "evening".');
            return 1;
        }

        $sentColumn = $period === 'morning' ? 'deadline_day_morning_sent_at' : 'deadline_day_evening_sent_at';

        $this->info("Checking for tasks due today for {$period} deadline day reminders...");

        $now = Carbon::now();

        $tasks = Task::whereIn('status', ['pending', 'in_progress'])
            ->whereDate('deadline', Carbon::today())
            ->whereNull($sentColumn)
            ->get();

        foreach ($tasks as $task) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->pivot->status !== 'completed') {
                    Mail::to($assignee->email)->send(new DeadlineReminder($task, $period));

                    $prefix = $period === 'morning' ? 'TODAY DUE' : 'EVENING REMINDER';
                    NotificationLog::create([
                        'user_id' => $assignee->id,
                        'type' => 'deadline_reminder',
                        'reference_id' => $task->id,
                        'reference_type' => Task::class,
                        'message' => "{$prefix}: Task \"{$task->title}\" is due today.",
                        'sent_at' => $now,
                    ]);
                }
            }
            $task->update([$sentColumn => $now]);
            $this->info("Sent {$period} deadline day reminder for task: {$task->id}");
        }

        $this->info("Done sending {$period} deadline day reminders.");
        return 0;
    }
}
