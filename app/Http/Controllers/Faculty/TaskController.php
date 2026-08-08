<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine active source tab (default: 'hod')
        $source = $request->input('source', 'hod');

        // Base query for assigned tasks
        $hodQuery = $user->assignedTasks()->where('owner_role', 'hod');
        $nbaQuery = $user->assignedTasks()->where('owner_role', 'nba_coordinator');

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $hodQuery->where('deadline', '<', now())->wherePivot('status', '!=', 'completed');
                $nbaQuery->where('deadline', '<', now())->wherePivot('status', '!=', 'completed');
            } else {
                $hodQuery->wherePivot('status', $request->status);
                $nbaQuery->wherePivot('status', $request->status);
            }
        }

        // Counts for tab badges
        $hodCount = (clone $hodQuery)->count();
        $nbaCount = (clone $nbaQuery)->count();

        // Get paginated tasks for active source
        if ($source === 'nba') {
            $tasks = $nbaQuery->orderBy('deadline', 'asc')->paginate(10)->withQueryString();
        } else {
            $tasks = $hodQuery->orderBy('deadline', 'asc')->paginate(10)->withQueryString();
        }

        return view('faculty.tasks.index', compact('tasks', 'source', 'hodCount', 'nbaCount'));
    }

    public function show(Task $task)
    {
        $this->authorize('updateProgress', $task);
        $task->load(['creator', 'meeting', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at', 'is_reassigned')]);
        
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()->pivot;
        
        // Shared Workspace: Get all documents uploaded for this task across all collaborators
        $allTaskDocuments = \App\Models\TaskDocument::where('task_id', $task->id)
            ->with(['user', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Find latest version of each document tree
        $rootDocIds = $allTaskDocuments->whereNull('original_document_id')->pluck('id');
        $childDocs = $allTaskDocuments->whereNotNull('original_document_id');

        $latestDocuments = collect();
        foreach ($rootDocIds as $rootId) {
            $latestChild = $childDocs->where('original_document_id', $rootId)->sortByDesc('version')->first();
            if ($latestChild) {
                $latestDocuments->push($latestChild);
            } else {
                $latestDocuments->push($allTaskDocuments->firstWhere('id', $rootId));
            }
        }
        
        $coveredRootIds = $latestDocuments->pluck('original_document_id')->merge($latestDocuments->pluck('id'))->filter()->unique();
        $orphans = $childDocs->filter(fn ($d) => !$coveredRootIds->contains($d->original_document_id));
        $latestDocuments = $latestDocuments->merge($orphans)->sortByDesc('created_at')->values();

        // Group latest documents by uploading faculty member
        $groupedDocuments = $latestDocuments->groupBy('user_id');
        
        return view('faculty.tasks.show', compact('task', 'pivot', 'allTaskDocuments', 'latestDocuments', 'groupedDocuments'));
    }

    public function updateProgress(Request $request, Task $task)
    {
        $this->authorize('updateProgress', $task);
        
        $validated = $request->validate([
            'remarks' => 'nullable|string',
            'status'  => 'required|in:not_started,collecting_resources,working_on_task,documents_uploaded,checklist_completed,submitted_for_review,pending,in_progress,pending_review',
        ]);
        
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()?->pivot;
        $oldProgress = $pivot ? $pivot->progress_percentage : 0;

        $task->update(['status' => $validated['status']]);
        
        // Recalculate progress automatically based on milestone rules
        $this->notificationService->updateAutomaticTaskProgress($task, 'status change to ' . str_replace('_', ' ', $validated['status']));

        if (!empty($validated['remarks'])) {
            auth()->user()->assignedTasks()->updateExistingPivot($task->id, ['remarks' => $validated['remarks']]);
        }

        $newProgress = $task->fresh(['assignees'])->overall_progress;

        // Centralized notification & activity/audit logging
        $this->notificationService->notifyProgressUpdated(
            $task,
            $oldProgress,
            $newProgress,
            $validated['status'],
            $validated['remarks']
        );

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('task_documents', 'public');
                \App\Models\TaskDocument::create([
                    'task_id'   => $task->id,
                    'user_id'   => auth()->id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }
        
        return redirect()->route('faculty.tasks.show', $task)->with('success', 'Task workflow stage updated successfully.');
    }
}
