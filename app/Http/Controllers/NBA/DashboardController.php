<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Meeting;
use App\Models\TaskDocument;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 1. My NBA Tasks (created by or assigned to me)
        $myTasksQuery = Task::where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $userId));
        })->with(['assignees', 'creator']);

        $myTasks = (clone $myTasksQuery)->latest()->take(10)->get();

        // 2. Pending Reviews (tasks created by me with status pending_review or documents submitted)
        $pendingReviews = Task::where('created_by', $userId)
            ->where(function ($q) {
                $q->where('status', 'pending_review')
                  ->orWhereHas('documents', fn ($dq) => $dq->where('review_status', 'submitted'));
            })
            ->with(['assignees', 'documents'])
            ->latest()
            ->get();

        // 3. Documents Awaiting Approval
        $documentsAwaitingApproval = TaskDocument::whereHas('task', fn ($tq) => $tq->where('created_by', $userId))
            ->where('review_status', 'submitted')
            ->with(['task', 'user'])
            ->latest()
            ->get();

        // 4. Upcoming Deadlines (due within next 7 days, not completed)
        $upcomingDeadlines = (clone $myTasksQuery)
            ->where('status', '!=', 'completed')
            ->where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays(7))
            ->orderBy('deadline', 'asc')
            ->get();

        // 5. My Meetings (organized by or attended by me)
        $myMeetings = Meeting::where('organized_by', $userId)
            ->orWhereHas('attendees', fn ($aq) => $aq->where('user_id', $userId))
            ->latest('scheduled_at')
            ->take(5)
            ->get();

        // 6. My Notifications
        $myNotifications = NotificationLog::where('user_id', $userId)
            ->latest('sent_at')
            ->take(10)
            ->get();

        // Stats summary for NBA Coordinator ONLY (their own tasks)
        $stats = [
            'total'           => (clone $myTasksQuery)->count(),
            'pending'         => (clone $myTasksQuery)->where('status', 'pending')->count(),
            'in_progress'     => (clone $myTasksQuery)->where('status', 'in_progress')->count(),
            'pending_review'  => (clone $myTasksQuery)->where('status', 'pending_review')->count(),
            'completed'       => (clone $myTasksQuery)->where('status', 'completed')->count(),
            'overdue'         => (clone $myTasksQuery)->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
        ];

        return view('nba.dashboard', compact(
            'myTasks',
            'pendingReviews',
            'documentsAwaitingApproval',
            'upcomingDeadlines',
            'myMeetings',
            'myNotifications',
            'stats'
        ));
    }
}
