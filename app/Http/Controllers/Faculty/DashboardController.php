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
        
        $pendingTasks = $faculty->assignedTasks()->wherePivot('status', 'pending')->count();
        $inProgressTasks = $faculty->assignedTasks()->wherePivot('status', 'in_progress')->count();
        $completedTasks = $faculty->assignedTasks()->wherePivot('status', 'completed')->count();

        // Upcoming Deadlines
        $upcomingDeadlines = $faculty->assignedTasks()
            ->wherePivotIn('status', ['pending', 'in_progress'])
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();
            
        $upcomingMeetings = $faculty->meetings()
            ->where('scheduled_at', '>=', Carbon::now())
            ->orderBy('scheduled_at', 'asc')
            ->take(3)
            ->get();

        return view('faculty.dashboard', compact(
            'pendingTasks', 'inProgressTasks', 'completedTasks', 
            'upcomingDeadlines', 'upcomingMeetings'
        ));
    }
}
