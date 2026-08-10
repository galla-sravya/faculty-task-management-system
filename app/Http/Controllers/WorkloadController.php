<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkloadController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
            'deadline' => 'required|date'
        ]);

        $warnings = [];
        $deadlineDate = Carbon::parse($request->deadline)->toDateString();

        foreach ($request->assignees as $userId) {
            $user = User::find($userId);
            
            // Count pending tasks
            $pendingTasksCount = $user->assignedTasks()
                ->where('tasks.status', '!=', 'completed')
                ->count();
                
            // Find tasks with the exact same deadline date
            $sameDeadlineTasksCount = $user->assignedTasks()
                ->where('tasks.status', '!=', 'completed')
                ->whereDate('tasks.deadline', $deadlineDate)
                ->count();

            $userWarnings = [];
            
            if ($pendingTasksCount >= 3) {
                $userWarnings[] = "has <strong>{$pendingTasksCount} pending tasks</strong>";
            }
            
            if ($sameDeadlineTasksCount > 0) {
                $userWarnings[] = "has <strong>{$sameDeadlineTasksCount} task(s) due on this exact date</strong>";
            }
            
            if (!empty($userWarnings)) {
                $warnings[] = "<strong>{$user->name}</strong> " . implode(' and ', $userWarnings) . ".";
            }
        }

        return response()->json([
            'warnings' => $warnings
        ]);
    }
}
