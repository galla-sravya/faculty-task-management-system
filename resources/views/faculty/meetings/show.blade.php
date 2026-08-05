@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Meeting Details</h1>
        <p class="text-muted small mb-0">View meeting information and minutes</p>
    </div>
    <a href="{{ route('faculty.dashboard') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="row g-4">
    <!-- Meeting Info -->
    <div class="col-lg-8">
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
                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Date & Time</small>
                        <span class="text-dark"><i class="bi bi-calendar3 text-primary me-2"></i>{{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('l, F j, Y \a\t g:i A') }}</span>
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
</div>
@endsection
