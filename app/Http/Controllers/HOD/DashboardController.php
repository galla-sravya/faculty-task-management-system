<?php

namespace App\Http\Controllers\HOD;

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

    public function index(): View
    {
        $hod = auth()->user();
        $departmentId = $hod->department_id;

        $totalTasks = Task::where('department_id', $departmentId)->count();
        $completedTasks = Task::where('department_id', $departmentId)->completed()->count();
        $inProgressTasks = Task::where('department_id', $departmentId)->inProgress()->count();
        $overdueTasks = Task::where('department_id', $departmentId)->overdue()->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $recentTasks = Task::where('department_id', $departmentId)
            ->with(['assignees', 'creator'])
            ->latest()
            ->get();

        $upcomingDeadlines = Task::where('department_id', $departmentId)
            ->pending()
            ->orWhere('status', 'in_progress')
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        $upcomingMeetings = Meeting::where('department_id', $departmentId)
            ->where('scheduled_at', '>=', Carbon::now())
            ->orderBy('scheduled_at', 'asc')
            ->take(3)
            ->get();

        // Data for filter dropdowns
        $faculties = User::where('department_id', $departmentId)
            ->where('role', 'faculty')
            ->orderBy('name')
            ->get(['id', 'name']);

        $categories = Task::where('department_id', $departmentId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        // Fallback categories if none exist in DB yet
        if ($categories->isEmpty()) {
            $categories = collect(['Academics', 'Research', 'NBA', 'NAAC', 'Placement', 'Department', 'Workshop', 'Seminar']);
        }

        // Documents awaiting HOD review
        $documentsAwaitingReview = TaskDocument::where('review_status', 'submitted')
            ->whereHas('task', fn ($q) => $q->where('department_id', $departmentId))
            ->with(['task', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('hod.dashboard', compact(
            'totalTasks', 'completedTasks', 'inProgressTasks', 'overdueTasks',
            'completionRate', 'recentTasks', 'upcomingDeadlines', 'upcomingMeetings',
            'faculties', 'categories', 'documentsAwaitingReview'
        ));
    }

    /**
     * AJAX endpoint: returns filtered dashboard data as JSON.
     */
    public function filterData(Request $request): JsonResponse
    {
        $hod = auth()->user();
        $departmentId = $hod->department_id;

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

        // Get filtered tasks
        $tasks = $this->filterService->getFilteredTasks($departmentId, $filters);

        // Get status distribution for charts
        $stats = $this->filterService->getStatusDistribution($departmentId, $filters);

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
                'category'         => $task->category ?? 'General',
                'overall_progress' => $task->overall_progress,
                'show_url'         => route('hod.tasks.show', $task),
            ];
        });

        return response()->json([
            'stats'  => $stats,
            'tasks'  => $tasksData,
        ]);
    }

    public function chartsData()
    {
        $hod = auth()->user();
        $departmentId = $hod->department_id;

        // Task Status Distribution
        $taskStatus = [
            'completed' => Task::where('department_id', $departmentId)->completed()->count(),
            'in_progress' => Task::where('department_id', $departmentId)->inProgress()->count(),
            'pending' => Task::where('department_id', $departmentId)->pending()->count(),
            'overdue' => Task::where('department_id', $departmentId)->overdue()->count(),
        ];

        // Faculty Performance (Tasks Completed vs Total Assigned)
        $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->get();
        $facultyPerformance = [];
        
        foreach ($faculties as $faculty) {
            $totalAssigned = $faculty->assignedTasks()->count();
            $completed = $faculty->assignedTasks()->wherePivot('status', 'completed')->count();
            
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
