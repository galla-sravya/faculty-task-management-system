<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->withCount(['assignedTasks as completed_tasks_count' => function ($query) {
                $query->where('task_user.status', 'completed');
            }])
            ->withCount('assignedTasks as total_tasks')
            ->get();
            
        return view('nba.faculty.index', compact('faculties'));
    }

    public function performance(User $faculty)
    {
        if ($faculty->department_id !== auth()->user()->department_id || $faculty->role !== 'faculty') {
            abort(403);
        }

        $faculty->load(['assignedTasks' => function ($query) {
            $query->where('owner_role', 'nba_coordinator')->orderBy('deadline', 'desc');
        }]);
        
        $totalTasks = $faculty->assignedTasks->count();
        $completedTasks = $faculty->assignedTasks->where('pivot.status', 'completed')->count();
        $inProgressTasks = $faculty->assignedTasks->where('pivot.status', 'in_progress')->count();
        $pendingTasks = $faculty->assignedTasks->where('pivot.status', 'pending')->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return view('nba.faculty.performance', compact(
            'faculty', 'totalTasks', 'completedTasks', 'inProgressTasks', 'pendingTasks', 'completionRate'
        ));
    }
}
