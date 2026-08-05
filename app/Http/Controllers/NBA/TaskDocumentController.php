<?php

namespace App\Http\Controllers\NBA;

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

        $this->notificationService->notifyDocumentReviewed($task, $document, $action, $request->input('review_comments'));
        $this->notificationService->updateAutomaticTaskProgress($task, $action === 'approved' ? 'document approval' : 'document review');

        if ($action === 'approved') {
            $pendingDocs = TaskDocument::where('task_id', $task->id)
                ->whereIn('review_status', ['submitted', 'changes_requested'])
                ->count();

            if ($pendingDocs === 0) {
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

        return redirect()->route('nba.tasks.show', $task)->with('success', $successMessage);
    }

    public function download(Task $task, TaskDocument $document)
    {
        $this->authorize('view', $task);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function versions(Task $task, TaskDocument $document)
    {
        $this->authorize('view', $task);

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
                'download_url'    => route('nba.tasks.documents.download', [$task, $v]),
            ]),
        ]);
    }
}
