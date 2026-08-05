<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(NotificationLog $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        
        $notification->update(['is_read' => true]);
        
        // Redirect to reference if possible
        if ($notification->reference_type === \App\Models\Task::class) {
            if (auth()->user()->isHod()) {
                return redirect()->route('hod.tasks.show', $notification->reference_id);
            } else {
                return redirect()->route('faculty.tasks.show', $notification->reference_id);
            }
        } elseif ($notification->reference_type === \App\Models\Meeting::class) {
            if (auth()->user()->isHod()) {
                return redirect()->route('hod.meetings.show', $notification->reference_id);
            } else {
                // Faculty doesn't have meeting detail view yet, could just go to dashboard or add one
                return back()->with('success', 'Notification marked as read.');
            }
        }
        
        return back()->with('success', 'Notification marked as read.');
    }
    
    public function markAllAsRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
