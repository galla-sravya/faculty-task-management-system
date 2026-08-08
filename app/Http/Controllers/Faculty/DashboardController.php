<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $faculty = auth()->user();
        
        // HOD Tasks Stats
        $hodPendingTasks = $faculty->assignedTasks()->where('owner_role', 'hod')->wherePivot('status', 'pending')->count();
        $hodInProgressTasks = $faculty->assignedTasks()->where('owner_role', 'hod')->wherePivot('status', 'in_progress')->count();
        $hodCompletedTasks = $faculty->assignedTasks()->where('owner_role', 'hod')->wherePivot('status', 'completed')->count();

        // NBA Tasks Stats
        $nbaPendingTasks = $faculty->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'pending')->count();
        $nbaInProgressTasks = $faculty->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'in_progress')->count();
        $nbaCompletedTasks = $faculty->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'completed')->count();

        // Combined totals for stat cards
        $pendingTasks = $hodPendingTasks + $nbaPendingTasks;
        $inProgressTasks = $hodInProgressTasks + $nbaInProgressTasks;
        $completedTasks = $hodCompletedTasks + $nbaCompletedTasks;

        // HOD Upcoming Deadlines
        $hodUpcomingDeadlines = $faculty->assignedTasks()
            ->where('owner_role', 'hod')
            ->wherePivotIn('status', ['pending', 'in_progress'])
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // NBA Upcoming Deadlines
        $nbaUpcomingDeadlines = $faculty->assignedTasks()
            ->where('owner_role', 'nba_coordinator')
            ->wherePivotIn('status', ['pending', 'in_progress'])
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // Combined for backward compatibility
        $upcomingDeadlines = $hodUpcomingDeadlines->merge($nbaUpcomingDeadlines)->sortBy('deadline')->take(5);
            
        $upcomingMeetings = $faculty->meetings()
            ->where('scheduled_at', '>=', Carbon::now())
            ->orderBy('scheduled_at', 'asc')
            ->take(3)
            ->get();

        return view('faculty.dashboard', compact(
            'pendingTasks', 'inProgressTasks', 'completedTasks', 
            'hodUpcomingDeadlines', 'nbaUpcomingDeadlines',
            'upcomingDeadlines', 'upcomingMeetings',
            'hodPendingTasks', 'hodInProgressTasks', 'hodCompletedTasks',
            'nbaPendingTasks', 'nbaInProgressTasks', 'nbaCompletedTasks'
        ));
    }
}
