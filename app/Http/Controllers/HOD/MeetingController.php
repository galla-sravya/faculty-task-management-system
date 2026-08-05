<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Models\NotificationLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = Meeting::where('department_id', auth()->user()->department_id)
            ->latest('scheduled_at')
            ->paginate(10);
            
        return view('hod.meetings.index', compact('meetings'));
    }

    public function create()
    {
        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('role', 'faculty')
            ->get();
            
        return view('hod.meetings.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'venue' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'attendees' => 'required|array',
            'attendees.*' => 'exists:users,id',
        ]);

        $meeting = Meeting::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'venue' => $validated['venue'] ?? null,
            'agenda' => $validated['agenda'] ?? null,
            'organized_by' => auth()->id(),
            'department_id' => auth()->user()->department_id,
            'status' => 'scheduled',
        ]);

        $meeting->attendees()->attach($validated['attendees'], ['attendance' => 'invited']);

        foreach ($validated['attendees'] as $userId) {
            NotificationLog::create([
                'user_id' => $userId,
                'type' => 'meeting_invite',
                'reference_id' => $meeting->id,
                'reference_type' => Meeting::class,
                'message' => 'You are invited to a meeting: ' . $meeting->title . ' at ' . Carbon::parse($meeting->scheduled_at)->format('M d, Y H:i'),
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('hod.meetings.index')->with('success', 'Meeting scheduled successfully.');
    }

    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);
        $meeting->load(['attendees', 'organizer', 'tasks']);
        return view('hod.meetings.show', compact('meeting'));
    }
    
    public function completeForm(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $meeting->load('attendees');
        return view('hod.meetings.complete', compact('meeting'));
    }
    
    public function complete(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        
        $validated = $request->validate([
            'minutes' => 'required|string',
            'attendance' => 'required|array', // user_id => attendance_status ('attended' or 'absent')
        ]);
        
        $meeting->update([
            'minutes' => $validated['minutes'],
            'status' => 'completed',
            'ended_at' => now(),
        ]);
        
        foreach ($validated['attendance'] as $userId => $status) {
            $meeting->attendees()->updateExistingPivot($userId, ['attendance' => $status]);
        }
        
        // Notify attendees about minutes
        foreach ($meeting->attendees as $attendee) {
            NotificationLog::create([
                'user_id' => $attendee->id,
                'type' => 'meeting_minutes',
                'reference_id' => $meeting->id,
                'reference_type' => Meeting::class,
                'message' => 'Minutes for meeting "' . $meeting->title . '" have been published.',
                'sent_at' => now(),
            ]);
        }
        
        return redirect()->route('hod.meetings.show', $meeting)->with('success', 'Meeting marked as completed and minutes saved.');
    }
}
