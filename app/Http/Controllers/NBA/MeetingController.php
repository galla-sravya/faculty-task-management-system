<?php

namespace App\Http\Controllers\NBA;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $userId = auth()->id();

        // Scope to meetings organized by or attended by NBA Coordinator
        $meetings = Meeting::where('organized_by', $userId)
            ->orWhereHas('attendees', fn ($aq) => $aq->where('user_id', $userId))
            ->latest('scheduled_at')
            ->paginate(10);

        return view('nba.meetings.index', compact('meetings'));
    }

    public function create()
    {
        $this->authorize('create', Meeting::class);

        $faculties = User::where('department_id', auth()->user()->department_id)
            ->where('id', '!=', auth()->id())
            ->get();

        return view('nba.meetings.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Meeting::class);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'scheduled_at' => 'required|date',
            'venue'        => 'nullable|string|max:255',
            'agenda'       => 'nullable|string',
            'attendees'    => 'required|array',
            'attendees.*'  => 'exists:users,id',
        ]);

        $meeting = Meeting::create([
            'title'         => '[NBA] ' . $validated['title'],
            'description'   => $validated['description'] ?? null,
            'scheduled_at'  => $validated['scheduled_at'],
            'venue'         => $validated['venue'] ?? null,
            'agenda'        => $validated['agenda'] ?? null,
            'organized_by'  => auth()->id(),
            'department_id' => auth()->user()->department_id,
            'status'        => 'scheduled',
        ]);

        $meeting->attendees()->attach($validated['attendees'], ['attendance' => 'invited']);

        $this->notificationService->notifyMeetingScheduled($meeting, $validated['attendees']);

        return redirect()->route('nba.meetings.index')->with('success', 'NBA Meeting scheduled successfully.');
    }

    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        $meeting->load(['attendees', 'organizer', 'tasks']);

        return view('nba.meetings.show', compact('meeting'));
    }

    public function completeForm(Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $meeting->load('attendees');

        return view('nba.meetings.complete', compact('meeting'));
    }

    public function complete(Request $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'minutes'    => 'required|string',
            'attendance' => 'required|array',
        ]);

        $meeting->update([
            'minutes'  => $validated['minutes'],
            'status'   => 'completed',
            'ended_at' => now(),
        ]);

        foreach ($validated['attendance'] as $userId => $status) {
            $meeting->attendees()->updateExistingPivot($userId, ['attendance' => $status]);
        }

        $attendeeIds = $meeting->attendees->pluck('id')->toArray();
        $this->notificationService->notifyMeetingUpdated($meeting, $attendeeIds);

        return redirect()->route('nba.meetings.show', $meeting)->with('success', 'NBA Meeting minutes saved.');
    }
}
