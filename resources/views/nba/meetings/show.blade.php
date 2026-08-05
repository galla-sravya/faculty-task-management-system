@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-{{ $meeting->status === 'completed' ? 'success' : 'primary' }}-subtle text-{{ $meeting->status === 'completed' ? 'success' : 'primary' }} px-3 py-1 text-capitalize">{{ $meeting->status }}</span>
        </div>
        <h1 class="h3 fw-bold text-navy mb-0">{{ $meeting->title }}</h1>
    </div>
    <div class="d-flex gap-2">
        @can('update', $meeting)
            @if($meeting->status !== 'completed')
                <a href="{{ route('nba.meetings.completeForm', $meeting) }}" class="btn btn-sm btn-success fw-medium d-flex align-items-center gap-1">
                    <i class="bi bi-check2-circle"></i> Complete & Minutes
                </a>
            @endif
        @endcan
        <a href="{{ route('nba.meetings.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to Meetings</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Meeting Details</h6>
                
                <div class="row g-3 mb-4 text-center border-bottom pb-3">
                    <div class="col-4">
                        <span class="text-muted d-block small">Scheduled Time</span>
                        <span class="fw-bold text-navy">{{ $meeting->scheduled_at->format('M d, Y g:i A') }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block small">Venue</span>
                        <span class="fw-bold text-navy">{{ $meeting->venue ?? 'Main Block' }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block small">Organizer</span>
                        <span class="fw-bold text-navy">{{ $meeting->organizer->name ?? 'NBA Coordinator' }}</span>
                    </div>
                </div>

                @if($meeting->agenda)
                <div class="mb-4">
                    <h6 class="fw-bold text-navy mb-2"><i class="bi bi-list-stars me-2 text-warning"></i>Agenda</h6>
                    <p class="text-secondary small bg-light p-3 rounded-3">{{ $meeting->agenda }}</p>
                </div>
                @endif

                @if($meeting->minutes)
                <div class="mb-4">
                    <h6 class="fw-bold text-navy mb-2"><i class="bi bi-journal-text me-2 text-success"></i>Minutes of Meeting (MoM)</h6>
                    <div class="p-3 bg-success-subtle rounded-3 text-dark small" style="white-space: pre-line;">
                        {{ $meeting->minutes }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-people text-primary fs-5"></i> Invited Attendees
                </h6>
            </div>
            <div class="card-body p-3">
                <ul class="list-group list-group-flush">
                    @foreach($meeting->attendees as $att)
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $att->profile_photo_url }}" class="rounded-circle" style="width:28px;height:28px;">
                            <span class="fw-medium text-navy small">{{ $att->name }}</span>
                        </div>
                        <span class="badge bg-{{ $att->pivot->attendance === 'attended' ? 'success' : ($att->pivot->attendance === 'absent' ? 'danger' : 'secondary') }}-subtle text-capitalize" style="font-size:0.68rem;">
                            {{ $att->pivot->attendance }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
