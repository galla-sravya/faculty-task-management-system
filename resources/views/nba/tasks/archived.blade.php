@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-archive me-2 text-warning"></i>Archived NBA Tasks</h1>
        <p class="text-muted small mb-0">View and restore soft-deleted NBA Accreditation tasks</p>
    </div>
    <div>
        <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to NBA Tasks</a>
    </div>
</div>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            @if($archivedTasks->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    No archived NBA tasks found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4" data-filter-col="title">Title</th>
                                <th data-filter-col="category">Category</th>
                                <th data-filter-col="priority">Priority</th>
                                <th>Archived At</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedTasks as $task)
                            <tr data-task-row
                                data-title="{{ $task->title }}"
                                data-priority="{{ $task->priority }}"
                                data-status="{{ $task->status }}"
                                data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                                data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                                data-deadline-status="{{ $task->smart_deadline['type'] }}"
                                data-faculty="{{ $task->assignees ? $task->assignees->pluck('name')->implode(', ') : '' }}"
                                data-category="{{ $task->category ?? 'NBA' }}">
                                <td class="ps-4">
                                    <div class="fw-semibold text-navy">{{ $task->title }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Created: {{ $task->created_at->format('M d, Y') }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $task->category ?? 'NBA' }}</span></td>
                                <td><span class="badge bg-{{ $task->priority_color }}-subtle text-{{ $task->priority_color }}">{{ ucfirst($task->priority) }}</span></td>
                                <td class="text-muted">{{ $task->deleted_at->format('M d, Y H:i') }}</td>
                                <td class="pe-4 text-end">
                                    <form action="{{ route('nba.tasks.restore', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-medium py-1 px-2" style="font-size:0.78rem;">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $archivedTasks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
