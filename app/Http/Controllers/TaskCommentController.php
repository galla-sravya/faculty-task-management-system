<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $request->validate([
            'comment'    => 'required|string|max:2000',
            'parent_id'  => 'nullable|exists:task_comments,id',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('comment_attachments', 'public');
        }

        $comment = TaskComment::create([
            'task_id'         => $task->id,
            'user_id'         => auth()->id(),
            'parent_id'       => $request->input('parent_id'),
            'comment'         => $request->input('comment'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        // Centralized Notification & Activity Logging
        $this->notificationService->notifyCommentAdded($task, $comment);

        return back()->with('success', 'Comment posted successfully.');
    }
}
