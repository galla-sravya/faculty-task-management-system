<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\CoordinatorType;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalHod = User::where('role', 'hod')->count();
        $totalNba = User::where('role', 'nba_coordinator')->count();
        $totalCoordinators = User::where('role', 'coordinator')->count();
        $totalFaculty = User::where('role', 'faculty')->count();
        $totalDepartments = Department::count();

        // Department Overview
        $departments = Department::with(['hod', 'nbaCoordinator'])
            ->withCount('faculty')
            ->orderBy('name')
            ->get();

        // Dynamic Coordinator Overview
        $dynamicCoordinators = User::where('role', 'coordinator')
            ->with(['department', 'coordinatorType'])
            ->latest()
            ->take(5)
            ->get();

        // Recent Accounts (HOD, NBA, Coordinator, Faculty)
        $recentAccounts = User::whereIn('role', ['hod', 'nba_coordinator', 'coordinator', 'faculty'])
            ->with(['department', 'coordinatorType'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalHod',
            'totalNba',
            'totalCoordinators',
            'totalFaculty',
            'totalDepartments',
            'departments',
            'dynamicCoordinators',
            'recentAccounts'
        ));
    }
}
