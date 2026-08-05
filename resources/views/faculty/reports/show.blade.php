@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Report Archive Record</h1>
        <p class="text-muted small mb-0">Read-only transaction history</p>
    </div>
    <a href="{{ route('faculty.reports.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Archive
    </a>
</div>

@php
    $myPivot = $task->assignees->where('id', auth()->id())->first()->pivot;
@endphp

<!-- Summary Strip -->
<div class="row text-center mb-4 g-3">
    <div class="col-md-4">
        <div class="card bg-white shadow-sm border-0 py-3" style="border-radius: var(--radius, 8px); border-bottom: 3px solid var(--success) !important;">
            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem;">My Completion Date</div>
            <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($myPivot->completed_at)->format('M d, Y') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-white shadow-sm border-0 py-3" style="border-radius: var(--radius, 8px); border-bottom: 3px solid var(--navy) !important;">
            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem;">My Total Duration</div>
            <div class="fw-bold fs-5 text-dark">{{ \App\Support\TimelineFormatter::format($myPivot->created_at, $myPivot->completed_at) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-white shadow-sm border-0 py-3" style="border-radius: var(--radius, 8px); border-bottom: 3px solid var(--gold) !important;">
            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem;">Collaborators</div>
            <div class="fw-bold fs-5 text-dark">{{ $task->assignees->count() }} Faculties</div>
        </div>
    </div>
</div>

<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-2" style="color: var(--navy);">{{ $task->title }}</h4>
        <p class="text-muted mb-0">{{ $task->description ?? 'No description.' }}</p>
        
        @if($task->meeting)
            <div class="mt-3 pt-3 border-top text-muted small">
                <i class="bi bi-camera-video me-1 text-primary"></i> Linked to Meeting: <span class="fw-semibold">{{ $task->meeting->title }}</span>
            </div>
        @endif
    </div>
</div>

<x-task-timeline :task="$task" />

@endsection
