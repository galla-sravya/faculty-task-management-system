<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Services\TaskFilterService;
use App\Models\Task;
use App\Models\User;
use App\Models\Meeting;
use App\Models\TaskDocument;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected TaskFilterService $filterService
    ) {}

    private function nbaTaskQuery(int $departmentId)
    {
        return Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator');
    }

    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $baseQuery = $this->nbaTaskQuery($departmentId);

        // Stats summary for ALL NBA tasks in department
        $totalTasks = (clone $baseQuery)->count();
        $completedTasks = (clone $baseQuery)->completed()->count();
        $inProgressTasks = (clone $baseQuery)->inProgress()->count();
        $overdueTasks = (clone $baseQuery)->overdue()->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Recent Tasks for the table
        $recentTasks = (clone $baseQuery)
            ->with(['assignees', 'creator'])
            ->latest()
            ->get();

        $upcomingDeadlines = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status', 'pending')->orWhere('status', 'in_progress');
            })
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // Upcoming Meetings (organized by or attended by NBA Coordinator)
        $upcomingMeetings = Meeting::where('department_id', $departmentId)
            ->where('scheduled_at', '>=', Carbon::now())
            ->where(function ($q) use ($user) {
                $q->where('organized_by', $user->id)
                  ->orWhereHas('attendees', fn ($aq) => $aq->where('user_id', $user->id));
            })
            ->orderBy('scheduled_at', 'asc')
            ->take(3)
            ->get();

        // Data for filter dropdowns
        $faculties = User::where('department_id', $departmentId)
            ->where('role', 'faculty')
            ->orderBy('name')
            ->get(['id', 'name']);

        $categories = (clone $baseQuery)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        if ($categories->isEmpty()) {
            $categories = collect(['NBA Accreditation', 'Academics', 'Research', 'Department']);
        }

        // Documents awaiting review (NBA-owned tasks only)
        $documentsAwaitingReview = TaskDocument::where('review_status', 'submitted')
            ->whereHas('task', fn ($q) => $q->where('department_id', $departmentId)->where('owner_role', 'nba_coordinator'))
            ->with(['task', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Recently Completed Tasks (NBA-owned only)
        $recentlyCompletedTasks = (clone $baseQuery)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Recent Activity Log Stream (NBA-owned tasks only)
        $recentActivities = \App\Models\TaskActivity::whereHas('task', fn ($q) => $q->where('department_id', $departmentId)->where('owner_role', 'nba_coordinator'))
            ->with(['user', 'task'])
            ->latest()
            ->take(6)
            ->get();

        return view('nba.dashboard', compact(
            'totalTasks', 'completedTasks', 'inProgressTasks', 'overdueTasks',
            'completionRate', 'recentTasks', 'upcomingDeadlines', 'upcomingMeetings',
            'faculties', 'categories', 'documentsAwaitingReview',
            'recentlyCompletedTasks', 'recentActivities'
        ));
    }

    public function getChartsData(): JsonResponse
    {
        $departmentId = auth()->user()->department_id;
        $baseQuery = $this->nbaTaskQuery($departmentId);

        $taskStatus = [
            'completed' => (clone $baseQuery)->completed()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
        ];

        // Faculty Performance (NBA tasks only)
        $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->get();
        $facultyPerformance = [];
        
        foreach ($faculties as $faculty) {
            $totalAssigned = $faculty->assignedTasks()->where('owner_role', 'nba_coordinator')->count();
            $completed = $faculty->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'completed')->count();
            
            $facultyPerformance['labels'][] = $faculty->name;
            $facultyPerformance['total'][] = $totalAssigned;
            $facultyPerformance['completed'][] = $completed;
        }

        return response()->json([
            'taskStatus' => $taskStatus,
            'facultyPerformance' => $facultyPerformance,
        ]);
    }

    public function filterTasks(Request $request): JsonResponse
    {
        $departmentId = auth()->user()->department_id;

        $filters = [
            'faculty_id'      => $request->input('faculty_id'),
            'status'          => $request->input('status'),
            'priority'        => $request->input('priority'),
            'category'        => $request->input('category'),
            'deadline_filter' => $request->input('deadline_filter'),
            'custom_start'    => $request->input('custom_start'),
            'custom_end'      => $request->input('custom_end'),
            'search'          => $request->input('search'),
        ];

        // Get filtered tasks — pass owner_role for scoping
        $tasks = $this->filterService->getFilteredTasks($departmentId, $filters, 'nba_coordinator');

        // Get status distribution for charts
        $stats = $this->filterService->getStatusDistribution($departmentId, $filters, 'nba_coordinator');

        // Build tasks array for JSON response
        $tasksData = $tasks->map(function (Task $task) {
            $assigneePhotos = $task->assignees->map(fn ($a) => [
                'name' => $a->name,
                'photo_url' => $a->profile_photo_url,
                'initial' => strtoupper(substr($a->name, 0, 1)),
            ])->values();

            return [
                'id'               => $task->id,
                'title'            => $task->title,
                'assignees'        => $task->assignees->pluck('name')->implode(', '),
                'assignee_photos'  => $assigneePhotos,
                'priority'         => $task->priority,
                'priority_color'   => $task->priority_color,
                'status'           => $task->status,
                'status_label'     => ucfirst(str_replace('_', ' ', $task->status)),
                'status_color'     => $task->status_color,
                'assigned_date'    => $task->assigned_date->format('M d, Y'),
                'deadline'         => $task->deadline->format('M d, Y'),
                'duration_days'    => $task->duration_in_days,
                'is_overdue'       => $task->is_overdue,
                'days_overdue'     => $task->days_overdue,
                'category'         => $task->category ?? 'General',
                'overall_progress' => $task->overall_progress,
                'show_url'         => route('nba.tasks.show', $task),
            ];
        });

        return response()->json([
            'stats'  => $stats,
            'tasks'  => $tasksData,
        ]);
    }
}
