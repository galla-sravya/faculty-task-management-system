<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Services\TaskFilterService;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskDocument;
use App\Models\User;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected TaskFilterService $filterService
    ) {}

    private function getOwnerRole()
    {
        return auth()->user()->coordinatorType->slug;
    }

    private function coordinatorTaskQuery(int $departmentId)
    {
        return Task::where('department_id', $departmentId)->where('owner_role', $this->getOwnerRole());
    }

    public function index(): View
    {
        $user = auth()->user();
        $departmentId = $user->department_id;
        $coordinatorType = $user->coordinatorType;

        $baseQuery = $this->coordinatorTaskQuery($departmentId);

        $totalTasks = (clone $baseQuery)->count();
        $completedTasks = (clone $baseQuery)->completed()->count();
        $inProgressTasks = (clone $baseQuery)->inProgress()->count();
        $overdueTasks = (clone $baseQuery)->overdue()->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

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

        $upcomingMeetings = collect(); // Coordinators don't schedule meetings currently, but keeping the variable to prevent view errors

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

        // Fallback categories if none exist in DB yet
        if ($categories->isEmpty()) {
            $categories = collect(['Academics', 'Research', 'NBA', 'NAAC', 'Placement', 'Department', 'Workshop', 'Seminar']);
        }

        // Documents awaiting review (only for coordinator-owned tasks)
        $documentsAwaitingReview = TaskDocument::where('review_status', 'submitted')
            ->whereHas('task', fn ($q) => $q->where('department_id', $departmentId)->where('owner_role', $this->getOwnerRole()))
            ->with(['task', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Recently Completed Tasks (coordinator-owned only)
        $recentlyCompletedTasks = (clone $baseQuery)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Recent Activity Log Stream (coordinator-owned tasks only)
        $recentActivities = \App\Models\TaskActivity::whereHas('task', fn ($q) => $q->where('department_id', $departmentId)->where('owner_role', $this->getOwnerRole()))
            ->with(['user', 'task'])
            ->latest()
            ->take(6)
            ->get();

        return view('coordinator.dashboard', compact(
            'coordinatorType', 'totalTasks', 'completedTasks', 'inProgressTasks', 'overdueTasks',
            'completionRate', 'recentTasks', 'upcomingDeadlines', 'upcomingMeetings',
            'faculties', 'categories', 'documentsAwaitingReview',
            'recentlyCompletedTasks', 'recentActivities'
        ));
    }

    /**
     * AJAX endpoint: returns filtered dashboard data as JSON.
     */
    public function filterData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

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
        $tasks = $this->filterService->getFilteredTasks($departmentId, $filters, $this->getOwnerRole());

        // Get status distribution for charts
        $stats = $this->filterService->getStatusDistribution($departmentId, $filters, $this->getOwnerRole());

        // Build tasks array for JSON response
        $tasksData = $tasks->map(function (Task $task) {
            $assigneeNames = $task->assignees->pluck('name')->implode(', ');
            $assigneePhotos = $task->assignees->map(fn ($a) => [
                'name' => $a->name,
                'photo_url' => $a->profile_photo_url,
                'initial' => strtoupper(substr($a->name, 0, 1)),
            ])->values();

            return [
                'id'               => $task->id,
                'title'            => $task->title,
                'assignees'        => $assigneeNames,
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
                'smart_deadline'   => $task->smart_deadline,
                'category'         => $task->category ?? 'General',
                'overall_progress' => $task->overall_progress,
                'show_url'         => route('coordinator.tasks.show', $task),
            ];
        });

        return response()->json([
            'stats'  => $stats,
            'tasks'  => $tasksData,
        ]);
    }

    public function chartsData()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $baseQuery = $this->coordinatorTaskQuery($departmentId);

        // Task Status Distribution
        $taskStatus = [
            'completed' => (clone $baseQuery)->completed()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
        ];

        // Faculty Performance (Tasks Completed vs Total Assigned — coordinator tasks only)
        $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->get();
        $facultyPerformance = [];
        
        foreach ($faculties as $faculty) {
            $totalAssigned = $faculty->assignedTasks()->where('owner_role', $this->getOwnerRole())->count();
            $completed = $faculty->assignedTasks()->where('owner_role', $this->getOwnerRole())->wherePivot('status', 'completed')->count();
            
            $facultyPerformance['labels'][] = $faculty->name;
            $facultyPerformance['total'][] = $totalAssigned;
            $facultyPerformance['completed'][] = $completed;
        }

        return response()->json([
            'taskStatus' => $taskStatus,
            'facultyPerformance' => $facultyPerformance
        ]);
    }
}
