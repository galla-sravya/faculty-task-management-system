@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Meetings</h1>
        <p class="text-muted small mb-0">Schedule and manage department meetings</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('hod.meetings.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-calendar-plus"></i> Schedule Meeting
        </a>
    </div>
</div>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Title</th>
                        <th class="py-3">Scheduled For</th>
                        <th class="py-3">Venue</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3">Attendees</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                    @php
                        $meetingStatusMap = [
                            'upcoming'  => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'icon' => 'bi-clock'],
                            'completed' => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'icon' => 'bi-check-circle'],
                            'cancelled' => ['bg' => '#fce4ec', 'text' => '#c62828', 'icon' => 'bi-x-circle'],
                        ];
                        $mStatus = $meeting->status ?? 'upcoming';
                        $ms = $meetingStatusMap[$mStatus] ?? $meetingStatusMap['upcoming'];
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background-color: #eef2ff; flex-shrink: 0;">
                                    <i class="bi bi-camera-video" style="color: var(--navy); font-size: 0.85rem;"></i>
                                </div>
                                <span class="fw-semibold text-dark">{{ $meeting->title }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>{{ Carbon\Carbon::parse($meeting->scheduled_at)->format('M d, Y') }}
                            </span>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ Carbon\Carbon::parse($meeting->scheduled_at)->format('h:i A') }}
                            </small>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $meeting->venue ?? 'TBA' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $ms['bg'] }}; color: {{ $ms['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                <i class="bi {{ $ms['icon'] }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $mStatus)) }}
                            </span>
                        </td>
                        <td class="pe-4">
                            <div class="d-flex align-items-center">
                                @foreach($meeting->attendees->take(3) as $attendee)
                                    <img src="{{ $attendee->profile_photo_url }}" alt="{{ $attendee->name }}"
                                         class="rounded-circle shadow-sm object-fit-cover"
                                         style="width: 30px; height: 30px; margin-left: {{ $loop->first ? '0' : '-8px' }}; border: 2px solid #fff; z-index: {{ 10 - $loop->index }};"
                                         title="{{ $attendee->name }}">
                                @endforeach
                                @if($meeting->attendees->count() > 3)
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                         style="width: 30px; height: 30px; background-color: #e9ecef; color: var(--navy); font-size: 0.6rem; margin-left: -8px; border: 2px solid #fff; z-index: 1;">
                                        +{{ $meeting->attendees->count() - 3 }}
                                    </div>
                                @endif
                                <span class="small text-muted ms-2">{{ $meeting->attendees->count() }} invited</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                            No meetings scheduled. Create one!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($meetings->hasPages())
        <div class="px-4 py-3 border-top" style="border-color: var(--border) !important;">
            {{ $meetings->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
