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

<!-- Filters Row -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('nba.tasks.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-navy px-3 fw-medium">Filter</button>
                <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

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
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr style="cursor: pointer;" onclick="window.location='{{ route('nba.tasks.show', $task) }}'">
                            <td class="ps-4">
                                <div class="fw-semibold text-navy">{{ $task->title }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">Owner: {{ $task->creator->name ?? 'Self' }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $task->category ?? 'NBA' }}</span></td>
                            <td><span class="badge bg-{{ $task->priority_color }}-subtle text-{{ $task->priority_color }} text-capitalize">{{ $task->priority }}</span></td>
                            <td><span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }} text-capitalize">{{ str_replace('_', ' ', $task->status) }}</span></td>
                            <td style="width: 130px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $task->overall_progress }}%"></div>
                                    </div>
                                    <span class="text-muted fw-medium" style="font-size:0.75rem;">{{ $task->overall_progress }}%</span>
                                </div>
                            </td>
                            <td><span class="{{ $task->is_overdue ? 'text-danger fw-bold' : '' }}">{{ $task->deadline->format('M d, Y') }}@if($task->is_overdue) <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>@endif</span></td>
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
@endsection
