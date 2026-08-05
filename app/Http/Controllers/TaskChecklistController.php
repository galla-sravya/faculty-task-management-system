<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Store subtask checklist item (HOD or Assigned Faculty).
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $item = TaskChecklistItem::create([
            'task_id' => $task->id,
            'title'   => trim($request->input('title')),
        ]);

        $this->notificationService->notifySubtaskCreated($task, $item);

        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask added successfully.');
    }

    /**
     * Toggle checklist item completion.
     */
    public function toggle(Request $request, Task $task, TaskChecklistItem $item)
    {
        $this->authorize('view', $task);

        $isCompleted = !$item->is_completed;

        $item->update([
            'is_completed' => $isCompleted,
            'completed_by' => $isCompleted ? auth()->id() : null,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        $this->notificationService->notifySubtaskCompleted($task, $item, $isCompleted);

        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask status updated.');
    }

    /**
     * Delete checklist item.
     */
    public function destroy(Task $task, TaskChecklistItem $item)
    {
        $this->authorize('view', $task);

        $title = $item->title;
        $item->delete();

        $this->notificationService->notifySubtaskDeleted($task, $title);

        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask removed.');
    }

    /**
     * Sync overall task progress automatically based on milestone engine.
     */
    protected function syncTaskProgress(Task $task): void
    {
        $this->notificationService->updateAutomaticTaskProgress($task, 'subtask update');
    }
}
