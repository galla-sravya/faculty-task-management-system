@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Assigned Tasks</h1>
        <p class="text-muted small mb-0">Track and update your assigned tasks</p>
    </div>
</div>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3" data-filter-col="title">Task Title</th>
                            <th class="py-3" data-filter-col="priority">Priority</th>
                            <th class="py-3" data-filter-col="status">My Status</th>
                            <th class="py-3" data-filter-col="progress">My Progress</th>
                            <th class="py-3" data-filter-col="assigned_date">Assigned Date</th>
                            <th class="py-3" data-filter-col="deadline">Deadline</th>
                            <th class="pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        @php $myPivot = $task->pivot; @endphp
                        <tr data-task-row
                            data-title="{{ $task->title }}"
                            data-priority="{{ $task->priority }}"
                            data-status="{{ $myPivot->status ?? $task->status }}"
                            data-progress="{{ $myPivot->progress_percentage ?? 0 }}"
                            data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                            data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                            data-deadline-status="{{ $task->smart_deadline['type'] }}"
                            data-faculty="{{ auth()->user()->name }}"
                            data-category="{{ $task->category ?? '' }}">
                            <td class="ps-4">
                                <a href="{{ route('faculty.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                    {{ $task->title }}
                                </a>
                                @if($task->category)
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">{{ $task->category }}</span>
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
                                <x-task-deadline-badge :task="$task" />
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
                                No tasks assigned to you right now.
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
</div>
@endsection
