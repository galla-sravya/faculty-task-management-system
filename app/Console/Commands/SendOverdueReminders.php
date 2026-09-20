<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\NotificationLog;
use App\Mail\TaskReminderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendOverdueReminders extends Command
{
    protected $signature = 'reminders:send-overdue';
    protected $description = 'Send daily 9:00 AM automated reminder emails to faculty with incomplete overdue tasks';

    public function handle()
    {
        $this->info('Checking for overdue tasks needing daily 9:00 AM reminders...');
        $now = Carbon::now();
        $totalSent = 0;

        // 1. Regular Tasks Overdue
        $overdueTasks = Task::where('deadline', '<', $now)
            ->where('status', '!=', 'completed')
            ->with(['creator', 'assignees'])
            ->get();

        foreach ($overdueTasks as $task) {
            $creator = $task->creator ?? (object)['name' => 'Department Head', 'designation' => 'HOD'];

            foreach ($task->assignees as $assignee) {
                if ($assignee->pivot->status !== 'completed' && (int)$assignee->pivot->progress_percentage < 100) {
                    try {
                        Mail::to($assignee->email)->send(
                            new TaskReminderMail($task, $assignee, $creator, null, true)
                        );

                        NotificationLog::create([
                            'user_id'        => $assignee->id,
                            'type'           => 'overdue_reminder',
                            'reference_id'   => $task->id,
                            'reference_type' => Task::class,
                            'message'        => "DAILY 9:00 AM OVERDUE REMINDER: Task \"{$task->title}\" is past deadline. Please submit your progress.",
                            'sent_at'        => $now,
                        ]);

                        $totalSent++;
                        $this->line("Sent daily overdue reminder to {$assignee->email} for Task: {$task->title}");
                    } catch (\Exception $e) {
                        $this->error("Failed to send email to {$assignee->email}: " . $e->getMessage());
                    }
                }
            }
        }

        // 2. NBA Tasks Overdue (if NBA module exists)
        $nbaModel = class_exists('App\Models\NBATask') ? 'App\Models\NBATask' : (class_exists('App\Models\NbaTask') ? 'App\Models\NbaTask' : null);

        if ($nbaModel) {
            $overdueNbaTasks = $nbaModel::where('deadline', '<', $now)
                ->where('status', '!=', 'completed')
                ->with(['creator', 'assignees'])
                ->get();

            foreach ($overdueNbaTasks as $task) {
                $creator = $task->creator ?? (object)['name' => 'NBA Coordinator', 'designation' => 'Coordinator'];

                foreach ($task->assignees as $assignee) {
                    if ($assignee->pivot->status !== 'completed' && (int)$assignee->pivot->progress_percentage < 100) {
                        try {
                            Mail::to($assignee->email)->send(
                                new TaskReminderMail($task, $assignee, $creator, null, true)
                            );

                            NotificationLog::create([
                                'user_id'        => $assignee->id,
                                'type'           => 'overdue_reminder',
                                'reference_id'   => $task->id,
                                'reference_type' => $nbaModel,
                                'message'        => "DAILY 9:00 AM OVERDUE REMINDER (NBA): Task \"{$task->title}\" is past deadline. Please submit your progress.",
                                'sent_at'        => $now,
                            ]);

                            $totalSent++;
                            $this->line("Sent daily overdue reminder to {$assignee->email} for NBA Task: {$task->title}");
                        } catch (\Exception $e) {
                            $this->error("Failed to send email to {$assignee->email}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        $this->info("Completed daily overdue reminder check. Total emails dispatched: {$totalSent}.");
        return 0;
    }
}
