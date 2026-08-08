<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TaskFilterService
{
    /**
     * Build filtered tasks query using Eloquent when() clauses.
     * Accepts optional $ownerRole to scope tasks by owner_role.
     */
    public function buildQuery(int $departmentId, array $filters = [], ?Builder $baseQuery = null, ?string $ownerRole = null): Builder
    {
        $query = $baseQuery ?? Task::where('department_id', $departmentId);

        // Apply owner_role scope if provided
        if ($ownerRole) {
            $query->where('owner_role', $ownerRole);
        }

        // Filter by Faculty Assignee
        $query->when(!empty($filters['faculty_id']), function (Builder $q) use ($filters) {
            $q->whereHas('assignees', function (Builder $subQ) use ($filters) {
                $subQ->where('users.id', $filters['faculty_id']);
            });
        });

        // Filter by Task Status
        $query->when(!empty($filters['status']) && $filters['status'] !== 'all', function (Builder $q) use ($filters) {
            $status = $filters['status'];
            if ($status === 'overdue') {
                $q->overdue();
            } else {
                $q->where('status', $status);
            }
        });

        // Filter by Priority
        $query->when(!empty($filters['priority']) && $filters['priority'] !== 'all', function (Builder $q) use ($filters) {
            $q->where('priority', $filters['priority']);
        });

        // Filter by Category
        $query->when(!empty($filters['category']) && $filters['category'] !== 'all', function (Builder $q) use ($filters) {
            $q->where('category', $filters['category']);
        });

        // Filter by Deadline
        $query->when(!empty($filters['deadline_filter']) && $filters['deadline_filter'] !== 'all', function (Builder $q) use ($filters) {
            $deadline = $filters['deadline_filter'];
            if ($deadline === 'today') {
                $q->whereDate('deadline', Carbon::today());
            } elseif ($deadline === 'this_week') {
                $q->whereBetween('deadline', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($deadline === 'this_month') {
                $q->whereMonth('deadline', Carbon::now()->month)->whereYear('deadline', Carbon::now()->year);
            } elseif ($deadline === 'custom') {
                if (!empty($filters['custom_start'])) {
                    $q->whereDate('deadline', '>=', $filters['custom_start']);
                }
                if (!empty($filters['custom_end'])) {
                    $q->whereDate('deadline', '<=', $filters['custom_end']);
                }
            }
        });

        // Search Box (Task Title, Faculty Name, Description)
        $query->when(!empty($filters['search']), function (Builder $q) use ($filters) {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $q->where(function (Builder $subQ) use ($searchTerm) {
                $subQ->where('title', 'like', $searchTerm)
                     ->orWhere('description', 'like', $searchTerm)
                     ->orWhereHas('assignees', function (Builder $userQ) use ($searchTerm) {
                         $userQ->where('name', 'like', $searchTerm);
                     });
            });
        });

        return $query;
    }

    /**
     * Get filtered tasks with eager loaded relationships.
     */
    public function getFilteredTasks(int $departmentId, array $filters = [], ?string $ownerRole = null)
    {
        return $this->buildQuery($departmentId, $filters, null, $ownerRole)
            ->with(['assignees', 'creator', 'department'])
            ->latest('updated_at')
            ->get();
    }

    /**
     * Calculate task status distribution for filtered subset.
     */
    public function getStatusDistribution(int $departmentId, array $filters = [], ?string $ownerRole = null): array
    {
        // Clone query per status metric
        $completed = (clone $this->buildQuery($departmentId, $filters, null, $ownerRole))->completed()->count();
        $inProgress = (clone $this->buildQuery($departmentId, $filters, null, $ownerRole))->inProgress()->count();
        $pending = (clone $this->buildQuery($departmentId, $filters, null, $ownerRole))->pending()->count();
        $overdue = (clone $this->buildQuery($departmentId, $filters, null, $ownerRole))->overdue()->count();
        $total = (clone $this->buildQuery($departmentId, $filters, null, $ownerRole))->count();

        $completionRate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'overdue' => $overdue,
            'completion_rate' => $completionRate,
        ];
    }
}
