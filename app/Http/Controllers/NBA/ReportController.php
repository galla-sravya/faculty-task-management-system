<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $departmentId = auth()->user()->department_id;

        $stats = [
            'total' => Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator')->count(),
            'completed' => Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator')->where('status', 'completed')->count(),
            'in_progress' => Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator')->where('status', 'in_progress')->count(),
            'overdue' => Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator')->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
        ];

        $tasks = Task::where('department_id', $departmentId)
            ->where('owner_role', 'nba_coordinator')
            ->where('status', 'completed')
            ->withCount('assignees')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('nba.reports.index', compact('tasks', 'stats'));
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
        
        return view('nba.reports.show', compact('task'));
    }

    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'tasks');
        $departmentId = auth()->user()->department_id;
        $fileName = 'nba_report_' . $type . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($type, $departmentId) {
            $file = fopen('php://output', 'w');

            if ($type === 'faculty') {
                fputcsv($file, ['Faculty Name', 'Email', 'Designation', 'Total NBA Tasks Assigned', 'Completed Tasks', 'In Progress Tasks', 'Overdue Tasks']);
                $faculties = User::where('department_id', $departmentId)->where('role', 'faculty')->get();
                foreach ($faculties as $f) {
                    $total = $f->assignedTasks()->where('owner_role', 'nba_coordinator')->count();
                    $completed = $f->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'completed')->count();
                    $inProgress = $f->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', 'in_progress')->count();
                    $overdue = $f->assignedTasks()->where('owner_role', 'nba_coordinator')->wherePivot('status', '!=', 'completed')->where('deadline', '<', now())->count();

                    fputcsv($file, [$f->name, $f->email, $f->designation ?? 'Faculty', $total, $completed, $inProgress, $overdue]);
                }
            } else {
                fputcsv($file, ['Task ID', 'Title', 'Category', 'Priority', 'Status', 'Assigned Date', 'Deadline', 'Overall Progress (%)']);
                $tasks = Task::where('department_id', $departmentId)->where('owner_role', 'nba_coordinator')->with('assignees')->get();
                foreach ($tasks as $t) {
                    fputcsv($file, [
                        '#TSK-' . $t->id,
                        $t->title,
                        $t->category ?? 'NBA Accreditation',
                        ucfirst($t->priority),
                        ucfirst(str_replace('_', ' ', $t->status)),
                        $t->assigned_date->format('Y-m-d'),
                        $t->deadline->format('Y-m-d'),
                        $t->overall_progress,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
