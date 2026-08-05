<?php

namespace App\Http\Controllers\Faculty;

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
     * Upload one or more documents to a task (as draft).
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('uploadDocument', $task);

        $request->validate([
            'documents'   => 'required|array|min:1',
            'documents.*' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg',
            'remarks'     => 'nullable|string|max:500',
        ]);

        $uploaded = [];

        foreach ($request->file('documents') as $file) {
            $path = $file->store('task_documents', 'public');

            $doc = TaskDocument::create([
                'task_id'       => $task->id,
                'user_id'       => auth()->id(),
                'file_name'     => $file->getClientOriginalName(),
                'file_path'     => $path,
                'version'       => 1,
                'remarks'       => $request->input('remarks'),
                'review_status' => 'draft',
                'file_size'     => $file->getSize(),
                'file_type'     => $file->getClientMimeType(),
            ]);

            $uploaded[] = $doc;
        }

        if (!empty($uploaded)) {
            $this->notificationService->notifyDocumentUploaded($task, count($uploaded), $uploaded[0]);
            $this->notificationService->updateAutomaticTaskProgress($task, 'document upload');
        }

        return redirect()->route('faculty.tasks.show', $task)
            ->with('success', count($uploaded) . ' document(s) uploaded successfully.');
    }

    /**
     * Upload a replacement version of an existing document.
     */
    public function replace(Request $request, Task $task, TaskDocument $document)
    {
        $this->authorize('view', $task);

        if ($document->user_id !== auth()->id()) {
            abort(403, 'Only the document owner may replace their own document.');
        }

        $request->validate([
            'document' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg',
            'remarks'  => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $path = $file->store('task_documents', 'public');

        // Determine root document and next version number
        $rootId = $document->original_document_id ?? $document->id;
        $maxVersion = TaskDocument::where(function ($q) use ($rootId) {
            $q->where('id', $rootId)->orWhere('original_document_id', $rootId);
        })->max('version');

        $newDoc = TaskDocument::create([
            'task_id'              => $task->id,
            'user_id'              => auth()->id(),
            'file_name'            => $file->getClientOriginalName(),
            'file_path'            => $path,
            'version'              => $maxVersion + 1,
            'original_document_id' => $rootId,
            'remarks'              => $request->input('remarks'),
            'review_status'        => 'draft',
            'file_size'            => $file->getSize(),
            'file_type'            => $file->getClientMimeType(),
        ]);

        // Centralized notification & activity logging
        $this->notificationService->notifyDocumentReuploaded($task, $newDoc);
        $this->notificationService->updateAutomaticTaskProgress($task, 'document re-upload');

        return redirect()->route('faculty.tasks.show', $task)
            ->with('success', 'Document replaced successfully (v' . $newDoc->version . ').');
    }

    /**
     * Download a document.
     */
    public function download(Task $task, TaskDocument $document)
    {
        $this->authorize('view', $task);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Submit draft documents for HOD review.
     */
    public function submit(Request $request, Task $task)
    {
        $this->authorize('updateProgress', $task);

        // Find all draft documents by this user for this task
        $draftDocs = TaskDocument::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->where('review_status', 'draft')
            ->get();

        if ($draftDocs->isEmpty()) {
            return redirect()->route('faculty.tasks.show', $task)
                ->with('error', 'No draft documents to submit.');
        }

        foreach ($draftDocs as $doc) {
            $doc->update(['review_status' => 'submitted']);
        }

        // Update task status to pending_review if not already completed
        if (!in_array($task->status, ['completed', 'pending_review'])) {
            $task->update(['status' => 'pending_review']);
        }

        // Centralized notification & activity logging
        $this->notificationService->notifySubmittedForReview($task, $draftDocs->count());
        $this->notificationService->updateAutomaticTaskProgress($task, 'submission for review');

        return redirect()->route('faculty.tasks.show', $task)
            ->with('success', $draftDocs->count() . ' document(s) submitted for review.');
    }

    /**
     * Get version history for a document (JSON).
     */
    public function versions(Task $task, TaskDocument $document)
    {
        $this->authorize('view', $task);

        $allVersions = $document->getAllVersions();

        return response()->json([
            'versions' => $allVersions->map(fn ($v) => [
                'id'            => $v->id,
                'version'       => $v->version,
                'file_name'     => $v->file_name,
                'file_size'     => $v->formatted_file_size,
                'remarks'       => $v->remarks,
                'review_status' => $v->review_status_badge,
                'uploaded_by'   => $v->user->name ?? 'Unknown',
                'uploaded_at'   => $v->created_at->format('M d, Y g:i A'),
                'review_comments' => $v->review_comments,
                'download_url'  => route('faculty.tasks.documents.download', [$task, $v]),
            ]),
        ]);
    }
}
