<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function taskQuery(int $departmentId, string $ownerRole)
    {
        return Task::where('department_id', $departmentId)->where('owner_role', $ownerRole);
    }

    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;
        $coordinatorType = $user->coordinatorType;
        $ownerRole = $coordinatorType->slug;

        $baseQuery = $this->taskQuery($departmentId, $ownerRole);

        $totalTasks = (clone $baseQuery)->count();
        $completedTasks = (clone $baseQuery)->completed()->count();
        $inProgressTasks = (clone $baseQuery)->inProgress()->count();
        $overdueTasks = (clone $baseQuery)->overdue()->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $recentTasks = (clone $baseQuery)
            ->with(['assignees', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingDeadlines = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status', 'pending')->orWhere('status', 'in_progress');
            })
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        $recentActivities = \App\Models\TaskActivity::whereHas('task', fn ($q) => $q->where('department_id', $departmentId)->where('owner_role', $ownerRole))
            ->with(['user', 'task'])
            ->latest()
            ->take(6)
            ->get();

        return view('coordinator.dashboard', compact(
            'coordinatorType', 'totalTasks', 'completedTasks', 'inProgressTasks', 'overdueTasks',
            'completionRate', 'recentTasks', 'upcomingDeadlines', 'recentActivities'
        ));
    }
}
