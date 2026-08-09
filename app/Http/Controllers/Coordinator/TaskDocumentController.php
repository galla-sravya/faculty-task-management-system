<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskDocument;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskDocumentController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Review a document: approve, request_changes, or reject.
     */
    public function review(Request $request, Task $task, TaskDocument $document)
    {
        $this->authorize('reviewDocument', $task);

        $request->validate([
            'action'          => 'required|in:approved,changes_requested,rejected',
            'review_comments' => 'nullable|required_unless:action,approved|string|max:1000',
        ]);

        $action = $request->input('action');

        $document->update([
            'review_status'   => $action,
            'review_comments' => $request->input('review_comments'),
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => now(),
        ]);

        // Centralized notification & activity logging
        $this->notificationService->notifyDocumentReviewed($task, $document, $action, $request->input('review_comments'));
        $this->notificationService->updateAutomaticTaskProgress($task, $action === 'approved' ? 'document approval' : 'document review');

        // If changes requested, return status to working_on_task
        if ($action === 'changes_requested') {
            $task->update(['status' => 'working_on_task']);
        }

        // If all submitted documents for this task are approved, update task status
        if ($action === 'approved') {
            $pendingDocs = TaskDocument::where('task_id', $task->id)
                ->whereIn('review_status', ['submitted', 'changes_requested'])
                ->count();

            if ($pendingDocs === 0) {
                // All documents reviewed — check if there are approved docs
                $approvedDocs = TaskDocument::where('task_id', $task->id)
                    ->where('review_status', 'approved')
                    ->count();

                if ($approvedDocs > 0 && in_array($task->status, ['submitted_for_review', 'pending_review'])) {
                    $task->update(['status' => 'working_on_task']);
                }
            }
        }

        $successMessage = match ($action) {
            'approved'          => 'Document approved successfully.',
            'changes_requested' => 'Changes requested. Faculty has been notified.',
            'rejected'          => 'Document rejected. Faculty has been notified.',
        };

        return redirect()->route('coordinator.tasks.show', $task)->with('success', $successMessage);
    }

    /**
     * Download a document.
     */
    public function download(Task $task, TaskDocument $document)
    {
        $this->authorize('reviewDocument', $task);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Get version history for a document (JSON).
     */
    public function versions(Task $task, TaskDocument $document)
    {
        $this->authorize('reviewDocument', $task);

        $allVersions = $document->getAllVersions();

        return response()->json([
            'versions' => $allVersions->map(fn ($v) => [
                'id'              => $v->id,
                'version'         => $v->version,
                'file_name'       => $v->file_name,
                'file_size'       => $v->formatted_file_size,
                'remarks'         => $v->remarks,
                'review_status'   => $v->review_status_badge,
                'uploaded_by'     => $v->user->name ?? 'Unknown',
                'uploaded_at'     => $v->created_at->format('M d, Y g:i A'),
                'review_comments' => $v->review_comments,
                'reviewed_by'     => $v->reviewer->name ?? null,
                'reviewed_at'     => $v->reviewed_at?->format('M d, Y g:i A'),
                'download_url'    => route('coordinator.tasks.documents.download', [$task, $v]),
            ]),
        ]);
    }
}
