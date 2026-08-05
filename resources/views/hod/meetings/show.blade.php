@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Meeting Details</h1>
        <p class="text-muted small mb-0">View meeting information and attendees</p>
    </div>
    <a href="{{ route('hod.meetings.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Meetings
    </a>
</div>

<div class="row g-4">
    <!-- Meeting Info -->
    <div class="col-lg-7">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-info-circle me-2" style="color: var(--gold);"></i>Meeting Information
                </h6>
            </div>
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3" style="color: var(--navy);">{{ $meeting->title }}</h4>
                <div class="d-flex flex-wrap gap-4 mb-4 pb-4 border-bottom" style="border-color: var(--border) !important;">
                    <div>
                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Date &amp; Time</small>
                        <span class="text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>{{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('M d, Y \a\t g:i A') }}</span>
                    </div>
                    <div>
                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Venue</small>
                        <span class="text-dark"><i class="bi bi-geo-alt text-danger me-2"></i>{{ $meeting->venue ?? 'Not specified' }}</span>
                    </div>
                    <div>
                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Status</small>
                        <span class="badge bg-{{ $meeting->status === 'completed' ? 'success' : 'warning' }} px-3 py-2 rounded-pill">
                            {{ ucfirst($meeting->status) }}
                        </span>
                    </div>
                </div>

                @if($meeting->description)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Description</h6>
                    <p class="text-muted">{{ $meeting->description }}</p>
                </div>
                @endif

                @if($meeting->agenda)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Agenda</h6>
                    <div class="p-3 bg-light rounded text-muted">
                        {!! nl2br(e($meeting->agenda)) !!}
                    </div>
                </div>
                @endif

                @if($meeting->status === 'completed' && $meeting->minutes)
                <div>
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">Meeting Minutes</h6>
                    <div class="p-3 border rounded text-muted" style="border-color: var(--border) !important; background-color: #f8f9fa;">
                        {!! nl2br(e($meeting->minutes)) !!}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Attendees -->
    <div class="col-lg-5">
        <div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-people me-2" style="color: var(--gold);"></i>Attendees
                </h6>
            </div>
            <div class="card-body p-0">
                @if($meeting->attendees->isEmpty())
                    <div class="p-4 text-center text-muted">No attendees assigned.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($meeting->attendees as $attendee)
                            <li class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $attendee->profile_photo_url }}" alt="{{ $attendee->name }}"
                                         class="rounded-circle shadow-sm object-fit-cover"
                                         style="width: 36px; height: 36px; border: 2px solid var(--navy); flex-shrink: 0;"
                                         title="{{ $attendee->name }}">
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $attendee->name }}</div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $attendee->email }}</div>
                                    </div>
                                </div>
                                @if($meeting->status === 'completed')
                                    @if($attendee->pivot->attendance === 'present')
                                        <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-check-circle me-1"></i>Present</span>
                                    @elseif($attendee->pivot->attendance === 'absent')
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2 py-1">Pending</span>
                                    @endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Associated Tasks -->
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-list-task me-2" style="color: var(--gold);"></i>Associated Tasks
                </h6>
            </div>
            <div class="card-body p-0">
                @if($meeting->tasks->isEmpty())
                    <div class="p-4 text-center text-muted">No tasks linked to this meeting.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($meeting->tasks as $task)
                            <a href="{{ route('hod.tasks.show', $task) }}" class="list-group-item list-group-item-action px-4 py-3 border-bottom text-decoration-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold" style="color: var(--navy); font-size: 0.9rem;">{{ $task->title }}</span>
                                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }} rounded-pill" style="font-size: 0.7rem;">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                                <div class="small text-muted d-flex flex-wrap gap-3 mt-1" style="font-size: 0.75rem;">
                                    <span><i class="bi bi-calendar-event me-1 text-secondary"></i>Assigned: {{ $task->created_at->format('M d, Y') }}</span>
                                    <span><i class="bi bi-calendar3 me-1 text-primary"></i>Deadline: {{ $task->deadline->format('M d, Y') }}</span>
                                    <span><i class="bi bi-hourglass-split me-1 text-muted"></i>Duration: {{ $task->duration_in_days }}d</span>
                                </div>
                            </a>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
