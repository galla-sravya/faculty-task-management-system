<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $tasks = Task::where('department_id', auth()->user()->department_id)
            ->where('status', 'completed')
            ->withCount('assignees')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('hod.reports.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        // Must be completed to view in reports
        abort_if($task->status !== 'completed', 404);
        
        $this->authorize('view', $task);
        
        $task->load([
            'creator', 
            'meeting', 
            'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')
        ]);
        
        return view('hod.reports.show', compact('task'));
    }
}
