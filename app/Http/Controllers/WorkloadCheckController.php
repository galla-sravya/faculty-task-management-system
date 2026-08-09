<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\WorkloadCheckService;
use Illuminate\Http\Request;

class WorkloadCheckController extends Controller
{
    protected WorkloadCheckService $workloadCheckService;

    public function __construct(WorkloadCheckService $workloadCheckService)
    {
        $this->workloadCheckService = $workloadCheckService;
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'faculty_ids'   => 'required|array',
            'faculty_ids.*' => 'integer|exists:users,id',
            'deadline'      => 'nullable|string',
            'task_id'       => 'nullable|integer|exists:tasks,id',
        ]);

        $facultyIds = $validated['faculty_ids'];
        $excludeTaskId = $validated['task_id'] ?? null;
        $deadlineDate = $validated['deadline'] ?? null;

        if (!$deadlineDate && $excludeTaskId) {
            $task = Task::find($excludeTaskId);
            if ($task && $task->deadline) {
                $deadlineDate = $task->deadline->toDateString();
            }
        }

        if (!$deadlineDate) {
            return response()->json(['overloaded' => []]);
        }

        $user = auth()->user();
        
        // Resolve owner_role
        $ownerRole = 'hod'; // default fallback
        if ($user->isHod()) {
            $ownerRole = 'hod';
        } elseif ($user->isNbaCoordinator()) {
            $ownerRole = 'nba_coordinator';
        } elseif ($user->isCoordinator()) {
            $ownerRole = $user->coordinatorType->slug ?? 'coordinator';
        }

        $overloaded = $this->workloadCheckService->checkOverload(
            $facultyIds,
            $deadlineDate,
            $ownerRole,
            $excludeTaskId
        );

        return response()->json(['overloaded' => $overloaded]);
    }
}
