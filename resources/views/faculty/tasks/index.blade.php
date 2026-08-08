@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Assigned Tasks</h1>
        <p class="text-muted small mb-0">Track and update your assigned tasks from HOD and NBA Coordinator</p>
    </div>
</div>

<!-- Source Tabs: HOD / NBA -->
<ul class="nav nav-pills mb-4 gap-2" role="tablist">
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 px-4 py-2 fw-semibold {{ $source === 'hod' ? 'active' : '' }}"
           href="{{ route('faculty.tasks.index', ['source' => 'hod']) }}"
           style="{{ $source === 'hod' ? 'background-color: var(--navy); border-color: var(--navy);' : 'color: var(--navy); background-color: #f0f2f5; border: 1px solid #dee2e6;' }}">
            <i class="bi bi-building"></i> HOD Tasks
            <span class="badge rounded-pill {{ $source === 'hod' ? 'bg-white text-dark' : 'bg-secondary text-white' }}" style="font-size: 0.7rem;">{{ $hodCount }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 px-4 py-2 fw-semibold {{ $source === 'nba' ? 'active' : '' }}"
           href="{{ route('faculty.tasks.index', ['source' => 'nba']) }}"
           style="{{ $source === 'nba' ? 'background-color: var(--maroon, #8B0000); border-color: var(--maroon);' : 'color: var(--navy); background-color: #f0f2f5; border: 1px solid #dee2e6;' }}">
            <i class="bi bi-award"></i> NBA Coordinator Tasks
            <span class="badge rounded-pill {{ $source === 'nba' ? 'bg-white text-dark' : 'bg-secondary text-white' }}" style="font-size: 0.7rem;">{{ $nbaCount }}</span>
        </a>
    </li>
</ul>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Task Title</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">My Status</th>
                        <th class="py-3">My Progress</th>
                        <th class="py-3">Assigned Date</th>
                        <th class="py-3">Deadline</th>
                        <th class="pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    @php $myPivot = $task->pivot; @endphp
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('faculty.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                {{ $task->title }}
                            </a>
                            @if($myPivot->is_reassigned)
                                <span class="badge rounded-pill bg-warning text-dark ms-2 border border-warning" style="font-size: 0.65rem;">
                                    <i class="bi bi-arrow-repeat me-1"></i>Reassigned
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $priorityMap = [
                                    'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Low'],
                                    'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Medium'],
                                    'high'   => ['bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'High'],
                                    'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Urgent'],
                                ];
                                $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
                            @endphp
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                {{ $p['label'] }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusMap = [
                                    'pending'     => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                                    'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                    'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                                ];
                                $ms = $statusMap[$myPivot->status] ?? $statusMap['pending'];
                            @endphp
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $ms['bg'] }}; color: {{ $ms['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                {{ $ms['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; min-width: 80px; background-color: #e9ecef; border-radius: 4px;">
                                    <div class="progress-bar" style="width: {{ $myPivot->progress_percentage }}%; background-color: {{ $myPivot->progress_percentage >= 100 ? '#2e7d32' : ($myPivot->progress_percentage >= 50 ? 'var(--navy)' : 'var(--gold)') }}; border-radius: 4px;"></div>
                                </div>
                                <span class="fw-semibold small text-dark" style="min-width: 35px;">{{ $myPivot->progress_percentage }}%</span>
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
                            <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);">
                                Update
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-check fs-2 d-block mb-2 text-secondary"></i>
                            No {{ $source === 'nba' ? 'NBA Coordinator' : 'HOD' }} tasks assigned to you right now.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
        <div class="px-4 py-3 border-top" style="border-color: var(--border) !important;">
            {{ $tasks->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
