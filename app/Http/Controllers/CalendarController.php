<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Meeting;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        if ($user->isHod()) {
            $tasks = Task::where('department_id', $departmentId)->with('assignees')->get();
            $meetings = Meeting::where('department_id', $departmentId)->get();
        } elseif ($user->isNbaCoordinator()) {
            $tasks = Task::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $user->id));
            })->with('assignees')->get();

            $meetings = Meeting::where('organized_by', $user->id)
                ->orWhereHas('attendees', fn ($aq) => $aq->where('user_id', $user->id))
                ->get();
        } else {
            $tasks = $user->assignedTasks()->with('assignees')->get();
            $meetings = Meeting::where('department_id', $departmentId)->get();
        }

        // Transform to Calendar events array
        $events = collect();

        foreach ($tasks as $task) {
            $isOverdue = $task->is_overdue || $task->status === 'overdue';

            $statusColor = match ($task->status) {
                'completed'                   => '#2e7d32',
                'in_progress'                 => '#1565c0',
                'pending_review', 'submitted' => '#7b1fa2',
                'overdue'                     => '#c62828',
                default                       => $isOverdue ? '#c62828' : '#f57f17',
            };

            $categories = ['tasks'];
            if ($task->status === 'completed') {
                $categories[] = 'completed';
            } elseif ($task->status === 'in_progress') {
                $categories[] = 'in_progress';
            } elseif (in_array($task->status, ['pending_review', 'submitted'])) {
                $categories[] = 'pending_review';
            } elseif ($task->status === 'pending') {
                $categories[] = 'pending';
            }

            if ($isOverdue && $task->status !== 'completed') {
                $categories[] = 'overdue';
            }

            $taskUrl = match (true) {
                $user->isHod()            => route('hod.tasks.show', $task),
                $user->isNbaCoordinator() => route('nba.tasks.show', $task),
                default                   => route('faculty.tasks.show', $task),
            };

            $events->push([
                'id'            => 'task-' . $task->id,
                'title'         => '📋 ' . $task->title,
                'start'         => $task->deadline->format('Y-m-d'),
                'url'           => $taskUrl,
                'color'         => $statusColor,
                'type'          => 'Task Deadline',
                'status'        => ucfirst(str_replace('_', ' ', $task->status)),
                'assigned_date' => $task->assigned_date->format('M d, Y'),
                'deadline'      => $task->deadline->format('M d, Y'),
                'categories'    => $categories,
                'days_overdue'  => $task->days_overdue,
                'overdue_label' => $task->days_overdue > 0
                    ? 'Overdue by ' . $task->days_overdue . ' ' . ($task->days_overdue === 1 ? 'day' : 'days')
                    : null,
            ]);
        }

        foreach ($meetings as $meeting) {
            $meetingUrl = match (true) {
                $user->isHod()            => route('hod.meetings.show', $meeting),
                $user->isNbaCoordinator() => route('nba.meetings.show', $meeting),
                default                   => route('faculty.meetings.show', $meeting),
            };

            $events->push([
                'id'         => 'meeting-' . $meeting->id,
                'title'      => '📅 ' . $meeting->title,
                'start'      => $meeting->scheduled_at->format('Y-m-d'),
                'url'        => $meetingUrl,
                'color'      => '#7b1fa2',
                'type'       => 'Meeting',
                'venue'      => $meeting->venue ?? 'TBA',
                'time'       => $meeting->scheduled_at->format('h:i A'),
                'categories' => ['meetings'],
            ]);
        }

        return view('calendar.index', compact('events'));
    }
}
