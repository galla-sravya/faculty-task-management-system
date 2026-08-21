@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Tasks</h1>
        <p class="text-muted small mb-0">Track your assigned tasks and tasks you've created</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('faculty.tasks.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-plus-lg"></i> Create Task
        </a>
    </div>
</div>

<ul class="nav nav-tabs mb-4 border-bottom" style="border-color: var(--border) !important;">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'assigned' ? 'active fw-bold' : 'text-secondary' }}" 
           href="{{ route('faculty.tasks.index', ['tab' => 'assigned']) }}"
           style="{{ $tab === 'assigned' ? 'color: var(--navy) !important; border-bottom: 3px solid var(--gold); border-bottom-left-radius: 0; border-bottom-right-radius: 0;' : '' }}">
            <i class="bi bi-inbox me-2"></i>Assigned to Me
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'created' ? 'active fw-bold' : 'text-secondary' }}" 
           href="{{ route('faculty.tasks.index', ['tab' => 'created']) }}"
           style="{{ $tab === 'created' ? 'color: var(--navy) !important; border-bottom: 3px solid var(--gold); border-bottom-left-radius: 0; border-bottom-right-radius: 0;' : '' }}">
            <i class="bi bi-send me-2"></i>Created by Me
        </a>
    </li>
</ul>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3" data-filter-col="title">Task Title</th>
                            @if($tab === 'created')
                                <th class="py-3" data-filter-col="assignees">Assignees</th>
                            @endif
                            <th class="py-3" data-filter-col="priority">Priority</th>
                            <th class="py-3" data-filter-col="status">{{ $tab === 'assigned' ? 'My Status' : 'Status' }}</th>
                            <th class="py-3" data-filter-col="progress">{{ $tab === 'assigned' ? 'My Progress' : 'Progress' }}</th>
                            <th class="py-3" data-filter-col="assigned_date">Assigned Date</th>
                            <th class="py-3" data-filter-col="deadline">Deadline</th>
                            <th class="pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        @php $myPivot = $task->pivot; @endphp
                        <tr data-task-row
                            onclick="window.location='{{ route('faculty.tasks.show', $task) }}'" style="cursor: pointer;"
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
                            </td>
                            @if($tab === 'created')
                            <td>
                                @if($task->assignees->count() > 0)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-group me-2" style="display: flex;">
                                            @foreach($task->assignees->take(3) as $assignee)
                                                <div title="{{ $assignee->name }}" style="width: 24px; height: 24px; border-radius: 50%; background-color: var(--navy); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: bold; margin-right: -8px; border: 1px solid white;">
                                                    {{ substr($assignee->name, 0, 1) }}
                                                </div>
                                            @endforeach
                                            @if($task->assignees->count() > 3)
                                                <div style="width: 24px; height: 24px; border-radius: 50%; background-color: #e9ecef; color: var(--navy); display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: bold; border: 1px solid white; z-index: 10;">
                                                    +{{ $task->assignees->count() - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            @endif
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
                                        'working_on_task' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                        'documents_uploaded' => ['bg' => '#6f42c1', 'text' => '#ffffff', 'label' => 'Documents Uploaded'],
                                        'submitted_for_review' => ['bg' => '#ffc107', 'text' => '#000', 'label' => 'In Review'],
                                        'not_started' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Not Started'],
                                    ];
                                    $statusKey = $tab === 'assigned' ? ($myPivot->status ?? 'pending') : $task->status;
                                    $ms = $statusMap[$statusKey] ?? $statusMap['pending'];
                                @endphp
                                <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $ms['bg'] }}; color: {{ $ms['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                    {{ $ms['label'] ?? ucfirst(str_replace('_', ' ', $statusKey)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $progressVal = $tab === 'assigned' ? ($myPivot->progress_percentage ?? 0) : $task->overall_progress;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px; min-width: 80px; background-color: #e9ecef; border-radius: 4px;">
                                        <div class="progress-bar" style="width: {{ $progressVal }}%; background-color: {{ $progressVal >= 100 ? '#2e7d32' : ($progressVal >= 50 ? 'var(--navy)' : 'var(--gold)') }}; border-radius: 4px;"></div>
                                    </div>
                                    <span class="fw-semibold small text-dark" style="min-width: 35px;">{{ $progressVal }}%</span>
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
                                <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                    {{ $tab === 'assigned' ? 'Update' : 'View' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $tab === 'created' ? '8' : '7' }}" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-check fs-2 d-block mb-2 text-secondary"></i>
                                {{ $tab === 'assigned' ? 'No tasks assigned to you right now.' : 'You have not created any tasks.' }}
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
