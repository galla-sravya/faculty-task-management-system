<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $meetings = auth()->user()->meetings()
            ->latest('scheduled_at')
            ->paginate(10);
            
        return view('faculty.meetings.index', compact('meetings'));
    }

    public function show(Meeting $meeting)
    {
        // Ensure the faculty is an attendee
        if (!$meeting->attendees->contains(auth()->id())) {
            abort(403);
        }
        
        $meeting->load(['organizer', 'attendees', 'tasks']);
        return view('faculty.meetings.show', compact('meeting'));
    }
}
