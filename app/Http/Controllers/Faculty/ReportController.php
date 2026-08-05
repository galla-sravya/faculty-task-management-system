<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Tasks where the current faculty member has a pivot status of 'completed'
        $tasks = auth()->user()->assignedTasks()
            ->wherePivot('status', 'completed')
            ->withCount('assignees')
            ->orderByPivot('completed_at', 'desc')
            ->get();
            
        return view('faculty.reports.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        // Ensure this faculty member is assigned and their pivot is completed
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()->pivot;
        abort_if(!$pivot || $pivot->status !== 'completed', 404);
        
        $this->authorize('updateProgress', $task);
        
        $task->load([
            'creator', 
            'meeting', 
            'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')
        ]);
        
        return view('faculty.reports.show', compact('task'));
    }
}
