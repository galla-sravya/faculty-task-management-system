<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\CoordinatorType;

class DashboardController extends Controller
{
    public function index()
    {
        $totalHod = User::where('role', 'hod')->count();
        $totalNba = User::where('role', 'nba_coordinator')->count();
        $totalCoordinators = User::where('role', 'coordinator')->count();
        $totalFaculty = User::where('role', 'faculty')->count();
        $totalDepartments = Department::count();
        $totalTasks = Task::count();

        return view('admin.dashboard', compact(
            'totalHod', 'totalNba', 'totalCoordinators', 'totalFaculty', 'totalDepartments', 'totalTasks'
        ));
    }
}
