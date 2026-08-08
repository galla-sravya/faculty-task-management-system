@extends('layouts.app')

@section('content')
@php
    $totalAssigned = $pendingTasks + $inProgressTasks + $completedTasks;
    $completionRate = $totalAssigned > 0 ? round(($completedTasks / $totalAssigned) * 100) : 0;
    $overdueCount = $upcomingDeadlines->filter(fn($task) => $task->deadline && $task->deadline->isPast())->count();
@endphp

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Faculty Dashboard</h1>
        <p class="text-muted small mb-0">Overview of your assigned tasks, personal progress, and department meetings</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('faculty.tasks.index') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-list-task"></i> View My Tasks
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Assigned Tasks" :value="$totalAssigned" color="navy" icon="bi bi-clipboard-check" link="{{ route('faculty.tasks.index') }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Completed Tasks" :value="$completedTasks" color="success" icon="bi bi-check-circle" link="{{ route('faculty.tasks.index', ['status' => 'completed']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="In Progress" :value="$inProgressTasks" color="warning" icon="bi bi-clock-history" link="{{ route('faculty.tasks.index', ['status' => 'in_progress']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Overdue Tasks" :value="$overdueCount" color="danger" icon="bi bi-exclamation-triangle" link="{{ route('faculty.tasks.index', ['status' => 'overdue']) }}" />
    </div>
</div>

<!-- Progress Ring & Upcoming Deadlines Row -->
<div class="row g-4 mb-4">
    <!-- Personal Completion Ring -->
    <div class="col-lg-5 col-xl-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-speedometer2 me-2" style="color: var(--gold);"></i>Personal Work Completion
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                <x-progress-ring :percent="$completionRate" label="Personal completion rate" :size="180" />
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines Table -->
    <div class="col-lg-7 col-xl-8">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-exclamation-circle me-2" style="color: var(--maroon);"></i>My Upcoming Deadlines
                </h6>
                <a href="{{ route('faculty.tasks.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="color: var(--navy);">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4 py-3">Task</th>
                                <th class="py-3">Priority</th>
                                <th class="py-3">Assigned Date</th>
                                <th class="py-3">Deadline</th>
                                <th class="pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingDeadlines as $task)
                            <tr class="{{ $task->owner_role === 'nba_coordinator' ? 'bg-warning-subtle border-start border-warning border-3' : ($task->owner_role !== 'hod' ? 'bg-light' : '') }}" onclick="window.location='{{ route('faculty.tasks.show', $task) }}'" style="cursor: pointer;">
                                <td class="ps-3">
                                    <a href="{{ route('faculty.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->owner_role !== 'hod')
                                        <span class="badge rounded-pill bg-secondary ms-2" style="font-size: 0.6rem; opacity: 0.85;">
                                            <i class="bi bi-person-badge me-1"></i>{{ ucwords(str_replace('_', ' ', $task->owner_role)) }}
                                        </span>
                                    @endif
                                    @if($task->pivot->is_reassigned)
                                        <span class="badge rounded-pill bg-warning text-dark ms-2 border border-warning" style="font-size: 0.65rem;">
                                            <i class="bi bi-arrow-repeat me-1"></i>Reassigned
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $task->priority_color }} px-2 py-1">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:0.82rem;">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $task->deadline->isPast() ? 'text-danger fw-semibold' : ($task->deadline->diffInDays(now()) < 3 ? 'text-warning fw-semibold' : 'text-muted') }}" style="font-size:0.82rem;">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y H:i') }}
                                        @if($task->deadline->isPast())
                                            <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Overdue by {{ (int) $task->deadline->diffInDays(now()) }} {{ (int) $task->deadline->diffInDays(now()) === 1 ? 'day' : 'days' }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No upcoming deadlines! Great job!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Meetings Row -->
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-calendar-event me-2" style="color: var(--navy);"></i>Upcoming Meetings
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @forelse($upcomingMeetings as $meeting)
                        <div class="col-md-4">
                            <a href="{{ route('faculty.meetings.show', $meeting) }}" class="p-3 border rounded bg-light d-block text-decoration-none" style="cursor: pointer;">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-semibold text-dark">{{ $meeting->title }}</h6>
                                    <span class="badge bg-white text-primary border" style="font-size: 0.7rem;">
                                        {{ $meeting->scheduled_at->diffForHumans() }}
                                    </span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i> {{ $meeting->venue ?? 'TBA' }}
                                </small>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center py-3 text-muted">
                            <i class="bi bi-calendar-x fs-3 d-block mb-1 text-secondary"></i>
                            <p class="mb-0 small">No upcoming meetings scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
