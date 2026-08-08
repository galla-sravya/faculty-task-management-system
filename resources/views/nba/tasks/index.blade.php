@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-award me-2 text-warning"></i>NBA Tasks</h1>
        <p class="text-muted small mb-0">Manage tasks created by or assigned to you for NBA Accreditation</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('nba.tasks.archived') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-archive"></i> Archived Tasks
        </a>
        <a href="{{ route('nba.tasks.create') }}" class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create NBA Task
        </a>
    </div>
</div>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <!-- Task Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            @if($tasks->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    No NBA tasks matching the criteria.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4" data-filter-col="title">Title</th>
                                <th data-filter-col="category">Category</th>
                                <th data-filter-col="priority">Priority</th>
                                <th data-filter-col="status">Status</th>
                                <th data-filter-col="progress">Progress</th>
                                <th data-filter-col="deadline">Deadline</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr data-task-row
                                data-title="{{ $task->title }}"
                                data-priority="{{ $task->priority }}"
                                data-status="{{ $task->status }}"
                                data-progress="{{ $task->overall_progress }}"
                                data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                                data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                                data-deadline-status="{{ $task->smart_deadline['type'] }}"
                                data-faculty="{{ $task->assignees->pluck('name')->implode(', ') }}"
                                data-category="{{ $task->category ?? 'NBA' }}"
                                style="cursor: pointer;" onclick="window.location='{{ route('nba.tasks.show', $task) }}'">
                                <td class="ps-4">
                                    <div class="fw-semibold text-navy">{{ $task->title }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Owner: {{ $task->creator->name ?? 'Self' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $task->category ?? 'NBA' }}</span></td>
                                <td><span class="badge bg-{{ $task->priority_color }}-subtle text-{{ $task->priority_color }} text-capitalize">{{ $task->priority }}</span></td>
                                <td><span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }} text-capitalize">{{ $task->formatted_status }}</span></td>
                                <td style="width: 130px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $task->overall_progress }}%"></div>
                                        </div>
                                        <span class="text-muted fw-medium" style="font-size:0.75rem;">{{ $task->overall_progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <x-task-deadline-badge :task="$task" />
                                </td>
                                <td class="pe-4 text-end" onclick="event.stopPropagation();">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('nba.tasks.show', $task) }}" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size: 0.8rem;">View</a>
                                        @can('update', $task)
                                        <a href="{{ route('nba.tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.8rem;">Edit</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
