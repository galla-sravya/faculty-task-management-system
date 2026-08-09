<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class WorkloadCheckService
{
    /**
     * Check if any of the provided faculty members will be overloaded (4 or more tasks on the same date).
     *
     * @param array $facultyIds
     * @param string $deadlineDate
     * @param string $ownerRole
     * @param int|null $excludeTaskId
     * @return array Array of overloaded faculty containing 'name' and 'count'
     */
    public function checkOverload(array $facultyIds, string $deadlineDate, string $ownerRole, ?int $excludeTaskId = null): array
    {
        $overloadedFaculty = [];
        
        try {
            $date = Carbon::parse($deadlineDate)->toDateString();
        } catch (\Exception $e) {
            return []; // Invalid date format, can't check
        }

        $query = Task::where('owner_role', $ownerRole)
            ->whereDate('deadline', $date)
            ->where('status', '!=', 'completed')
            ->whereHas('assignees', function ($q) use ($facultyIds) {
                $q->whereIn('users.id', $facultyIds);
            });

        if ($excludeTaskId) {
            $query->where('id', '!=', $excludeTaskId);
        }

        // Get the tasks with their assignees to count per faculty
        $tasks = $query->with(['assignees' => function ($q) use ($facultyIds) {
            $q->whereIn('users.id', $facultyIds);
        }])->get();

        $facultyTaskCounts = [];
        foreach ($facultyIds as $id) {
            $facultyTaskCounts[$id] = 0;
        }

        foreach ($tasks as $task) {
            foreach ($task->assignees as $assignee) {
                if (isset($facultyTaskCounts[$assignee->id])) {
                    $facultyTaskCounts[$assignee->id]++;
                }
            }
        }

        $users = User::whereIn('id', $facultyIds)->get()->keyBy('id');

        foreach ($facultyTaskCounts as $facultyId => $count) {
            if ($count >= 3) {
                $user = $users->get($facultyId);
                if ($user) {
                    $overloadedFaculty[] = [
                        'name' => $user->name,
                        'count' => $count
                    ];
                }
            }
        }

        return $overloadedFaculty;
    }
}
