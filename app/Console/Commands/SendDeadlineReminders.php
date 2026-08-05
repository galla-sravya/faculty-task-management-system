<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\NotificationLog;
use App\Mail\DeadlineReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDeadlineReminders extends Command
{
    protected $signature = 'reminders:send-deadline';
    protected $description = 'Send 48-hour, 12-hour and 2-hour deadline reminders to faculty for their pending tasks';

    public function handle()
    {
        $this->info('Checking for approaching deadlines...');
        
        $now = Carbon::now();
        $in48Hours = $now->copy()->addHours(48);
        $in12Hours = $now->copy()->addHours(12);
        $in2Hours = $now->copy()->addHours(2);
        
        // Find tasks due in 48 hours that haven't had a 48h reminder
        $tasks48h = Task::whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('deadline', [$now, $in48Hours])
            ->whereNull('reminder_48h_sent_at')
            ->get();
            
        foreach ($tasks48h as $task) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->pivot->status !== 'completed') {
                    Mail::to($assignee->email)->send(new DeadlineReminder($task, '48h'));

                    NotificationLog::create([
                        'user_id' => $assignee->id,
                        'type' => 'deadline_reminder',
                        'reference_id' => $task->id,
                        'reference_type' => Task::class,
                        'message' => 'REMINDER: Task "' . $task->title . '" is due in less than 48 hours.',
                        'sent_at' => $now,
                    ]);
                }
            }
            $task->update(['reminder_48h_sent_at' => $now]);
            $this->info("Sent 48h reminder for task: {$task->id}");
        }

        // Find tasks due in 12 hours that haven't had a 12h reminder
        $tasks12h = Task::pending()
            ->orWhere('status', 'in_progress')
            ->whereBetween('deadline', [$now, $in12Hours])
            ->whereNull('reminder_12h_sent_at')
            ->get();
            
        foreach ($tasks12h as $task) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->pivot->status !== 'completed') {
                    NotificationLog::create([
                        'user_id' => $assignee->id,
                        'type' => 'deadline_reminder',
                        'reference_id' => $task->id,
                        'reference_type' => Task::class,
                        'message' => 'REMINDER: Task "' . $task->title . '" is due in less than 12 hours.',
                        'sent_at' => $now,
                    ]);
                }
            }
            $task->update(['reminder_12h_sent_at' => $now]);
            $this->info("Sent 12h reminder for task: {$task->id}");
        }
        
        // Find tasks due in 2 hours that haven't had a 2h reminder
        $tasks2h = Task::pending()
            ->orWhere('status', 'in_progress')
            ->whereBetween('deadline', [$now, $in2Hours])
            ->whereNull('reminder_2h_sent_at')
            ->get();
            
        foreach ($tasks2h as $task) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->pivot->status !== 'completed') {
                    NotificationLog::create([
                        'user_id' => $assignee->id,
                        'type' => 'deadline_reminder',
                        'reference_id' => $task->id,
                        'reference_type' => Task::class,
                        'message' => 'URGENT: Task "' . $task->title . '" is due in less than 2 hours!',
                        'sent_at' => $now,
                    ]);
                }
            }
            $task->update(['reminder_2h_sent_at' => $now]);
            $this->info("Sent 2h reminder for task: {$task->id}");
        }
        
        $this->info('Done sending reminders.');
    }
}
