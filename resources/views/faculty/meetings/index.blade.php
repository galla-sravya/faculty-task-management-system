@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Meetings</h1>
        <p class="text-muted small mb-0">View all meetings you are invited to</p>
    </div>
</div>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Meeting Title</th>
                        <th class="py-3">Scheduled At</th>
                        <th class="py-3">Venue</th>
                        <th class="pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                    <tr onclick="window.location='{{ route('faculty.meetings.show', $meeting) }}'" style="cursor: pointer;">
                        <td class="ps-4 fw-medium" style="color: var(--navy);">{{ $meeting->title }}</td>
                        <td>
                            <i class="bi bi-calendar3 me-1 text-muted"></i> 
                            {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('M d, Y h:i A') }}
                        </td>
                        <td><i class="bi bi-geo-alt me-1 text-danger"></i> {{ $meeting->venue ?? 'TBA' }}</td>
                        <td class="pe-4">
                            <a href="{{ route('faculty.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                            No meetings scheduled for you.
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
