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
     * Store subtask checklist item (HOD, NBA Coordinator, or Assigned Faculty).
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('manageChecklist', $task);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|string', // user ID or 'all'
        ]);

        $assignedToId = null;
        if (!empty($validated['assigned_to']) && $validated['assigned_to'] !== 'all') {
            $assignedToId = (int) $validated['assigned_to'];
            // Ensure assigned user belongs to task assignees
            if (!$task->assignees()->where('user_id', $assignedToId)->exists()) {
                $assignedToId = null;
            }
        }

        $item = TaskChecklistItem::create([
            'task_id'         => $task->id,
            'title'           => trim($validated['title']),
            'description'     => isset($validated['description']) ? trim($validated['description']) : null,
            'created_by'      => auth()->id(),
            'created_by_role' => auth()->user()->role,
            'assigned_to'     => $assignedToId,
            'status'          => 'pending',
            'is_completed'    => false,
        ]);

        $this->notificationService->notifySubtaskCreated($task, $item);
        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask added successfully.');
    }

    /**
     * Update subtask checklist item details (HOD, NBA Coordinator, or Item Creator).
     */
    public function update(Request $request, Task $task, TaskChecklistItem $item)
    {
        $this->authorize('updateChecklistItem', [$task, $item]);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed',
        ]);

        $assignedToId = null;
        if (!empty($validated['assigned_to']) && $validated['assigned_to'] !== 'all') {
            $assignedToId = (int) $validated['assigned_to'];
            if (!$task->assignees()->where('user_id', $assignedToId)->exists()) {
                $assignedToId = null;
            }
        }

        $changes = [];
        if ($item->title !== $validated['title']) {
            $changes['title'] = ['old' => $item->title, 'new' => $validated['title']];
        }
        if ($item->description !== ($validated['description'] ?? null)) {
            $changes['description'] = ['old' => $item->description ?? '—', 'new' => $validated['description'] ?? '—'];
        }
        if ($item->assigned_to !== $assignedToId) {
            $oldAssignee = $item->assignee ? $item->assignee->name : 'All Collaborators';
            $newAssigneeUser = $assignedToId ? \App\Models\User::find($assignedToId) : null;
            $newAssignee = $newAssigneeUser ? $newAssigneeUser->name : 'All Collaborators';
            $changes['assigned_to'] = ['old' => $oldAssignee, 'new' => $newAssignee];
        }

        $isCompleted = ($validated['status'] === 'completed');

        $item->update([
            'title'        => trim($validated['title']),
            'description'  => isset($validated['description']) ? trim($validated['description']) : null,
            'assigned_to'  => $assignedToId,
            'status'       => $validated['status'],
            'is_completed' => $isCompleted,
            'completed_by' => $isCompleted ? auth()->id() : null,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        if (!empty($changes)) {
            $this->notificationService->notifySubtaskUpdated($task, $item, $changes);
        }

        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask updated successfully.');
    }

    /**
     * Update checklist item status (Pending / In Progress / Completed).
     */
    public function updateStatus(Request $request, Task $task, TaskChecklistItem $item)
    {
        $this->authorize('updateChecklistItemStatus', [$task, $item]);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $oldStatus = $item->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back();
        }

        $isCompleted = ($newStatus === 'completed');

        $item->update([
            'status'       => $newStatus,
            'is_completed' => $isCompleted,
            'completed_by' => $isCompleted ? auth()->id() : null,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        $this->notificationService->notifySubtaskStatusChanged($task, $item, $oldStatus, $newStatus);
        $this->syncTaskProgress($task);

        return back()->with('success', 'Subtask status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.');
    }

    /**
     * Toggle checklist item completion (Checkbox backward compatibility).
     */
    public function toggle(Request $request, Task $task, TaskChecklistItem $item)
    {
        $this->authorize('updateChecklistItemStatus', [$task, $item]);

        $isCompleted = !$item->is_completed;
        $newStatus = $isCompleted ? 'completed' : 'pending';
        $oldStatus = $item->status;

        $item->update([
            'status'       => $newStatus,
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
        $this->authorize('deleteChecklistItem', [$task, $item]);

        try {
            $title = $item->title;
            $item->delete();

            $this->notificationService->notifySubtaskDeleted($task, $title);
            $this->syncTaskProgress($task);

            return back()->with('success', 'Work item deleted successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete work item', [
                'task_id' => $task->id,
                'item_id' => $item->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete work item: ' . $e->getMessage());
        }
    }

    /**
     * Sync overall task progress automatically based on milestone engine.
     */
    protected function syncTaskProgress(Task $task): void
    {
        $this->notificationService->updateAutomaticTaskProgress($task, 'subtask update');
    }
}
