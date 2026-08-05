<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskDocument;
use App\Models\TaskActivity;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskDocumentController extends Controller
{
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
            'review_status'  => $action,
            'review_comments' => $request->input('review_comments'),
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        // Map action to human-readable label
        $actionLabels = [
            'approved'          => 'approved',
            'changes_requested' => 'requested changes for',
            'rejected'          => 'rejected',
        ];

        // Activity log
        $desc = auth()->user()->name . ' ' . $actionLabels[$action] . ' document "' . $document->file_name . '".';
        if ($request->input('review_comments')) {
            $desc .= ' Comments: "' . $request->input('review_comments') . '"';
        }

        TaskActivity::create([
            'task_id'     => $task->id,
            'user_id'     => auth()->id(),
            'action'      => 'document_' . ($action === 'changes_requested' ? 'changes_requested' : $action),
            'description' => $desc,
        ]);

        // Notify the document uploader
        $notificationType = match ($action) {
            'approved'          => 'document_approved',
            'changes_requested' => 'document_changes_requested',
            'rejected'          => 'document_rejected',
        };

        $notificationMessage = match ($action) {
            'approved'          => 'Your document "' . $document->file_name . '" for task "' . $task->title . '" has been approved.',
            'changes_requested' => 'Changes requested for your document "' . $document->file_name . '" on task "' . $task->title . '". Comments: "' . $request->input('review_comments') . '"',
            'rejected'          => 'Your document "' . $document->file_name . '" for task "' . $task->title . '" has been rejected. Reason: "' . $request->input('review_comments') . '"',
        };

        NotificationLog::create([
            'user_id'        => $document->user_id,
            'type'           => $notificationType,
            'reference_id'   => $task->id,
            'reference_type' => Task::class,
            'message'        => $notificationMessage,
            'sent_at'        => now(),
        ]);

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

                if ($approvedDocs > 0 && $task->status === 'pending_review') {
                    $task->update(['status' => 'in_progress']);
                }
            }
        }

        $successMessage = match ($action) {
            'approved'          => 'Document approved successfully.',
            'changes_requested' => 'Changes requested. Faculty has been notified.',
            'rejected'          => 'Document rejected. Faculty has been notified.',
        };

        return redirect()->route('hod.tasks.show', $task)->with('success', $successMessage);
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
                'download_url'    => route('hod.tasks.documents.download', [$task, $v]),
            ]),
        ]);
    }
}
