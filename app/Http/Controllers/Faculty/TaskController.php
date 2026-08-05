<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->assignedTasks();
        
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('deadline', '<', now())
                      ->wherePivot('status', '!=', 'completed');
            } else {
                $query->wherePivot('status', $request->status);
            }
        }
        
        $tasks = $query->orderBy('deadline', 'asc')->paginate(10);
        
        return view('faculty.tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $this->authorize('updateProgress', $task);
        $task->load(['creator', 'meeting', 'assignees' => fn($q) => $q->withPivot('status', 'progress_percentage', 'remarks', 'completed_at', 'created_at', 'updated_at')]);
        
        $pivot = $task->assignees()->where('user_id', auth()->id())->first()->pivot;
        
        // Get latest version of each document uploaded by this user
        $documents = \App\Models\TaskDocument::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Group: root documents (original_document_id is null) and their latest versions
        $rootDocIds = $documents->whereNull('original_document_id')->pluck('id');
        $childDocs = $documents->whereNotNull('original_document_id');

        // For each root, find the latest version
        $latestDocuments = collect();
        foreach ($rootDocIds as $rootId) {
            $latestChild = $childDocs->where('original_document_id', $rootId)->sortByDesc('version')->first();
            if ($latestChild) {
                $latestDocuments->push($latestChild);
            } else {
                $latestDocuments->push($documents->firstWhere('id', $rootId));
            }
        }
        // Also add any standalone child docs whose root is from another user (shouldn't happen but be safe)
        $coveredRootIds = $latestDocuments->pluck('original_document_id')->merge($latestDocuments->pluck('id'))->filter()->unique();
        $orphans = $childDocs->filter(fn ($d) => !$coveredRootIds->contains($d->original_document_id));
        $latestDocuments = $latestDocuments->merge($orphans)->sortByDesc('created_at')->values();
        
        return view('faculty.tasks.show', compact('task', 'pivot', 'documents', 'latestDocuments'));
    }

    public function updateProgress(Request $request, Task $task)
    {
        $this->authorize('updateProgress', $task);
        
        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,pending_review,completed',
            'documents.*' => 'nullable|file|max:10240',
        ]);
        
        $updateData = [
            'progress_percentage' => $validated['progress_percentage'],
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
        ];
        
        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
            $updateData['progress_percentage'] = 100;
        }
        
        auth()->user()->assignedTasks()->updateExistingPivot($task->id, $updateData);

        // Record Activity Log entry
        $userName = auth()->user()->name;
        $progress = $updateData['progress_percentage'];
        $action = $validated['status'] === 'completed' ? 'completed' : 'progress_updated';
        $desc = "{$userName} updated progress to {$progress}% (" . ucfirst(str_replace('_', ' ', $validated['status'])) . ").";
        if (!empty($validated['remarks'])) {
            $desc .= " Remarks: \"{$validated['remarks']}\"";
        }

        \App\Models\TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $desc,
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('task_documents', 'public');
                \App\Models\TaskDocument::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }
        
        // Check if all assignees have completed the task, then mark main task as completed
        $allCompleted = true;
        foreach ($task->assignees as $assignee) {
            if ($assignee->id == auth()->id()) continue; // Skip current user as it's not refreshed yet
            if ($assignee->pivot->status !== 'completed') {
                $allCompleted = false;
                break;
            }
        }
        
        if ($allCompleted && $validated['status'] === 'completed') {
            $task->update(['status' => 'completed']);

            \App\Models\TaskActivity::create([
                'task_id'     => $task->id,
                'user_id'     => auth()->id(),
                'action'      => 'completed',
                'description' => "All assignees completed the task. Task marked as fully completed.",
            ]);
        } elseif ($task->status === 'pending' && $validated['status'] === 'in_progress') {
            $task->update(['status' => 'in_progress']);
        }
        
        return redirect()->route('faculty.tasks.show', $task)->with('success', 'Task progress updated successfully.');
    }
}
