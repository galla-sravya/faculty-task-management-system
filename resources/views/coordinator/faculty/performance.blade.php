@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $faculty->profile_photo_url }}" alt="{{ $faculty->name }}" class="rounded-circle shadow-sm object-fit-cover" style="width: 52px; height: 52px; border: 2px solid var(--navy);">
        <div>
            <h1 class="h3 fw-bold mb-0" style="color: var(--navy);">{{ $faculty->name }}</h1>
            <p class="text-muted small mb-0">
                <span class="fw-semibold text-dark">{{ $faculty->designation ?? 'Faculty Member' }}</span> &bull; {{ $faculty->email }}
            </p>
        </div>
    </div>
    <a href="{{ route('coordinator.faculty.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Faculty List
    </a>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Assigned Tasks" :value="$totalTasks" color="navy" icon="bi bi-clipboard-data" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Completed" :value="$completedTasks" color="success" icon="bi bi-check-circle" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="In Progress" :value="$inProgressTasks" color="warning" icon="bi bi-clock-history" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Pending" :value="$pendingTasks" color="danger" icon="bi bi-hourglass-split" />
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Progress Ring Card -->
    <div class="col-lg-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-speedometer2 me-2" style="color: var(--gold);"></i>Performance Completion Rate
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                <x-progress-ring :percent="$completionRate" label="Work Completion" :size="160" />
            </div>
        </div>
    </div>

    <!-- Quick Info & Stats -->
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-person-vcard me-2" style="color: var(--navy);"></i>Faculty Profile Details
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded border" style="border-color: var(--border) !important;">
                            <small class="text-muted text-uppercase d-block fw-semibold mb-1" style="font-size: 0.7rem;">Full Name</small>
                            <span class="fw-semibold text-dark">{{ $faculty->name }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded border" style="border-color: var(--border) !important;">
                            <small class="text-muted text-uppercase d-block fw-semibold mb-1" style="font-size: 0.7rem;">Designation</small>
                            <span class="fw-semibold text-dark">{{ $faculty->designation ?? 'Faculty' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="p-3 bg-light rounded border" style="border-color: var(--border) !important;">
                            <small class="text-muted text-uppercase d-block fw-semibold mb-1" style="font-size: 0.7rem;">Email Address</small>
                            <span class="fw-semibold text-dark">{{ $faculty->email }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Tasks Table -->
<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-list-task me-2" style="color: var(--navy);"></i>{{ auth()->user()->coordinatorType->name ?? 'Coordinator' }} Assigned Tasks & Progress
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Task Title</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Progress</th>
                        <th class="py-3">Assigned Date</th>
                        <th class="py-3">Deadline</th>
                        <th class="pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faculty->assignedTasks as $task)
                    @php
                        $myPivot = $task->pivot;
                        $statusMap = [
                            'pending'     => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                            'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                            'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                        ];
                        $priorityMap = [
                            'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Low'],
                            'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Medium'],
                            'high'   => ['bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'High'],
                            'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Urgent'],
                        ];
                        $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
                        $s = $statusMap[$myPivot->status ?? 'pending'] ?? $statusMap['pending'];
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('coordinator.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                {{ $task->title }}
                            </a>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                {{ $p['label'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; min-width: 80px; background-color: #e9ecef; border-radius: 4px;">
                                    <div class="progress-bar" style="width: {{ $myPivot->progress_percentage ?? 0 }}%; background-color: var(--navy); border-radius: 4px;"></div>
                                </div>
                                <span class="fw-semibold small text-dark" style="min-width: 35px;">{{ $myPivot->progress_percentage ?? 0 }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $task->is_overdue ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y') }}
                                @if($task->is_overdue)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="pe-4">
                            <a href="{{ route('coordinator.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);">
                                View Task
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-secondary"></i>
                            No {{ auth()->user()->coordinatorType->name ?? 'Coordinator' }} tasks assigned to this faculty member yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
