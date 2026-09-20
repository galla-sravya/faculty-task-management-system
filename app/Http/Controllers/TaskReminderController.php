<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Task;
use App\Models\NBATask;
use App\Models\User;
use App\Models\NotificationLog;
use App\Models\TaskActivity;
use App\Models\NBATaskActivity;
use App\Mail\TaskReminderMail;

class TaskReminderController extends Controller
{
    /**
     * Send manual reminder emails to selected faculty assignees of a task.
     */
    public function sendReminder(Request $request, Task $task)
    {
        $user = auth()->user();

        // Ensure user is the creator or HOD
        if ($task->created_by !== $user->id && !$user->isHod()) {
            abort(403, 'Only the task creator or HOD can send task reminders.');
        }

        $validated = $request->validate([
            'faculty_ids' => 'required|array|min:1',
            'faculty_ids.*' => 'required|exists:users,id',
            'message'     => 'nullable|string|max:1000',
        ]);

        $recipients = User::whereIn('id', $validated['faculty_ids'])->get();
        $customMessage = $validated['message'] ?? null;
        $sentCount = 0;
        $sentNames = [];

        foreach ($recipients as $recipient) {
            $isAssignee = $task->assignees()->where('users.id', $recipient->id)->exists();
            if (!$isAssignee) continue;

            Mail::to($recipient->email)->send(
                new TaskReminderMail($task, $recipient, $user, $customMessage, false)
            );

            NotificationLog::create([
                'user_id'        => $recipient->id,
                'type'           => 'task_reminder',
                'reference_id'   => $task->id,
                'reference_type' => Task::class,
                'message'        => "Reminder from {$user->name} regarding task: \"{$task->title}\"." . ($customMessage ? " Note: \"{$customMessage}\"" : ""),
                'sent_at'        => now(),
            ]);

            $sentCount++;
            $sentNames[] = $recipient->name;
        }

        if ($sentCount > 0) {
            TaskActivity::create([
                'task_id'     => $task->id,
                'user_id'     => $user->id,
                'action'      => 'reminder_sent',
                'description' => "{$user->name} sent a reminder email to " . implode(', ', $sentNames) . ".",
            ]);

            return back()->with('success', "Reminder email sent to {$sentCount} faculty member(s) successfully.");
        }

        return back()->with('error', 'No eligible faculty assignees were selected.');
    }

    /**
     * Send manual reminder emails to selected faculty assignees of an NBA task.
     */
    public function sendNbaReminder(Request $request, NBATask $task)
    {
        $user = auth()->user();

        if ($task->created_by !== $user->id && !$user->isNbaCoordinator() && !$user->isHod()) {
            abort(403, 'Only the coordinator or HOD can send NBA task reminders.');
        }

        $validated = $request->validate([
            'faculty_ids' => 'required|array|min:1',
            'faculty_ids.*' => 'required|exists:users,id',
            'message'     => 'nullable|string|max:1000',
        ]);

        $recipients = User::whereIn('id', $validated['faculty_ids'])->get();
        $customMessage = $validated['message'] ?? null;
        $sentCount = 0;
        $sentNames = [];

        foreach ($recipients as $recipient) {
            $isAssignee = $task->assignees()->where('users.id', $recipient->id)->exists();
            if (!$isAssignee) continue;

            Mail::to($recipient->email)->send(
                new TaskReminderMail($task, $recipient, $user, $customMessage, false)
            );

            NotificationLog::create([
                'user_id'        => $recipient->id,
                'type'           => 'task_reminder',
                'reference_id'   => $task->id,
                'reference_type' => NBATask::class,
                'message'        => "Reminder from {$user->name} regarding NBA task: \"{$task->title}\"." . ($customMessage ? " Note: \"{$customMessage}\"" : ""),
                'sent_at'        => now(),
            ]);

            $sentCount++;
            $sentNames[] = $recipient->name;
        }

        if ($sentCount > 0) {
            NBATaskActivity::create([
                'nba_task_id' => $task->id,
                'user_id'     => $user->id,
                'action'      => 'reminder_sent',
                'description' => "{$user->name} sent a reminder email to " . implode(', ', $sentNames) . ".",
            ]);

            return back()->with('success', "Reminder email sent to {$sentCount} faculty member(s) successfully.");
        }

        return back()->with('error', 'No eligible faculty assignees were selected.');
    }       
     /**
     * Mark a task as fully completed by Creator or HOD (offline completion / manual override).
     * Keeps each faculty's actual progress percentage intact on record while closing the task.
     */
    public function markAsCompleted(Request $request, Task $task)
    {
        $user = auth()->user();

        if ($task->created_by !== $user->id && !$user->isHod()) {
            abort(403, 'Only the task creator or HOD can mark this task as completed.');
        }

        $remarks = $request->input('completion_remarks');

        // 1. Mark main task as completed (closes task & stops all automated reminders)
        $task->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // 2. Log task activity
        $description = "{$user->name} marked the task as fully completed (Manual Override).";
        if (!empty($remarks)) {
            $description .= " Reason: \"{$remarks}\"";
        }

        TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => $user->id,
            'action'      => 'completed',
            'description' => $description,
        ]);

        return back()->with('success', 'Task has been marked as completed. Submissions are now closed and reminders stopped.');
    }

}
