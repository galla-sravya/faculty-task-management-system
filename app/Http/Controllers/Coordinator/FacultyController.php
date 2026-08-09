<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    private function getOwnerRole()
    {
        return auth()->user()->coordinatorType->slug;
    }

    public function index()
    {
        $ownerRole = $this->getOwnerRole();

        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->withCount(['assignedTasks as completed_tasks_count' => function ($query) use ($ownerRole) {
                $query->where('task_user.status', 'completed')
                      ->where('tasks.owner_role', $ownerRole);
            }])
            ->withCount(['assignedTasks as total_tasks' => function ($query) use ($ownerRole) {
                $query->where('tasks.owner_role', $ownerRole);
            }])
            ->get();
            
        return view('coordinator.faculty.index', compact('faculties'));
    }

    public function performance(User $faculty)
    {
        if ($faculty->department_id !== auth()->user()->department_id || $faculty->role !== 'faculty') {
            abort(403);
        }

        $ownerRole = $this->getOwnerRole();

        $faculty->load(['assignedTasks' => function ($query) use ($ownerRole) {
            $query->where('owner_role', $ownerRole)->orderBy('deadline', 'desc');
        }]);
        
        $totalTasks = $faculty->assignedTasks->count();
        $completedTasks = $faculty->assignedTasks->where('pivot.status', 'completed')->count();
        $inProgressTasks = $faculty->assignedTasks->where('pivot.status', 'in_progress')->count();
        $pendingTasks = $faculty->assignedTasks->where('pivot.status', 'pending')->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return view('coordinator.faculty.performance', compact(
            'faculty', 'totalTasks', 'completedTasks', 'inProgressTasks', 'pendingTasks', 'completionRate'
        ));
    }
}
