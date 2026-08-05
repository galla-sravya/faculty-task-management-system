<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Scope to NBA Coordinator's tasks only
        $query = Task::where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $userId));
        })->with(['assignees', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'          => (clone $query)->count(),
            'completed'      => (clone $query)->where('status', 'completed')->count(),
            'in_progress'    => (clone $query)->where('status', 'in_progress')->count(),
            'pending_review' => (clone $query)->where('status', 'pending_review')->count(),
            'overdue'        => (clone $query)->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
        ];

        return view('nba.reports.index', compact('tasks', 'stats'));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['creator', 'meeting', 'assignees', 'activities', 'auditLogs', 'documents', 'comments']);

        return view('nba.reports.show', compact('task'));
    }

    public function exportCsv(Request $request)
    {
        $userId = auth()->id();

        $tasks = Task::where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhereHas('assignees', fn ($aq) => $aq->where('user_id', $userId));
        })->with(['assignees', 'creator'])->latest()->get();

        $csvHeader = ['Task ID', 'Title', 'Category', 'Priority', 'Status', 'Overall Progress', 'Created By', 'Deadline', 'Assignees'];
        $csvRows = [];

        foreach ($tasks as $t) {
            $assigneeNames = $t->assignees->pluck('name')->implode(', ');
            $csvRows[] = [
                $t->id,
                $t->title,
                $t->category ?? 'NBA Accreditation',
                ucfirst($t->priority),
                ucfirst(str_replace('_', ' ', $t->status)),
                $t->overall_progress . '%',
                $t->creator->name ?? 'Unknown',
                $t->deadline->format('Y-m-d H:i'),
                $assigneeNames,
            ];
        }

        $filename = 'nba_report_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($csvHeader, $csvRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);
            foreach ($csvRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
