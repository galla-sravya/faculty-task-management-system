@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-award me-2 text-warning"></i>NBA Coordinator Dashboard</h1>
        <p class="text-muted small mb-0">Overview of NBA Accreditation Tasks, Document Reviews, Deadlines & Meetings</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('nba.tasks.create') }}" class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create NBA Task
        </a>
        <a href="{{ route('nba.meetings.create') }}" class="btn btn-sm btn-outline-navy fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-calendar-plus"></i> Schedule NBA Meeting
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-primary">
            <div class="text-muted small text-uppercase fw-semibold mb-1">My NBA Tasks</div>
            <div class="h3 fw-bold mb-0 text-navy">{{ $totalTasks }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-warning">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Pending</div>
            <div class="h3 fw-bold mb-0 text-warning">{{ $totalTasks - $inProgressTasks - $completedTasks }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-info">
            <div class="text-muted small text-uppercase fw-semibold mb-1">In Progress</div>
            <div class="h3 fw-bold mb-0 text-info">{{ $inProgressTasks }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-purple">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Pending Review</div>
            <div class="h3 fw-bold mb-0 text-primary">{{ $documentsAwaitingReview->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-success">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Completed</div>
            <div class="h3 fw-bold mb-0 text-success">{{ $completedTasks }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white h-100 border-start border-4 border-danger">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Overdue</div>
            <div class="h3 fw-bold mb-0 text-danger">{{ $overdueTasks }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: My NBA Tasks & Documents Awaiting Approval -->
    <div class="col-lg-8">
        <!-- Documents Awaiting Approval -->
        @if($documentsAwaitingReview->count() > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check text-warning fs-5"></i> Documents Awaiting Review
                </h6>
                <span class="badge bg-warning text-dark">{{ $documentsAwaitingReview->count() }} Pending</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-3">Document</th>
                                <th>Task</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentsAwaitingReview as $doc)
                            <tr>
                                <td class="ps-3 fw-semibold text-navy">
                                    <i class="bi bi-file-earmark-pdf me-1 text-danger"></i> {{ $doc->file_name }}
                                </td>
                                <td>
                                    <a href="{{ route('nba.tasks.show', $doc->task_id) }}" class="text-decoration-none text-dark fw-medium">
                                        {{ Str::limit($doc->task->title ?? 'NBA Task', 30) }}
                                    </a>
                                </td>
                                <td>{{ $doc->user->name ?? 'Faculty' }}</td>
                                <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('nba.tasks.show', $doc->task_id) }}" class="btn btn-sm btn-psg-primary py-1 px-2" style="font-size:0.78rem;">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- My NBA Tasks Table -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-list-task text-primary fs-5"></i> My NBA Tasks
                </h6>
                <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-link text-navy fw-semibold p-0 text-decoration-none">View All &rarr;</a>
            </div>
            <div class="card-body p-0">
                @if($recentTasks->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No NBA tasks found. <a href="{{ route('nba.tasks.create') }}" class="text-navy fw-semibold">Create an NBA Task</a> to get started.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-3">Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Deadline</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTasks as $task)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('nba.tasks.show', $task) }}" class="fw-semibold text-navy text-decoration-none">
                                            {{ Str::limit($task->title, 35) }}
                                        </a>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            Owner: {{ $task->creator->name ?? 'Self' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->priority_color }}-subtle text-{{ $task->priority_color }} text-capitalize">
                                            {{ $task->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }} text-capitalize">
                                            {{ str_replace('_', ' ', $task->status) }}
                                        </span>
                                    </td>
                                    <td style="width: 120px;">
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
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('nba.tasks.show', $task) }}" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size:0.78rem;">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Meetings, Deadlines, Notifications -->
    <div class="col-lg-4">
        <!-- Upcoming Deadlines -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-danger fs-5"></i> Upcoming Deadlines (7 Days)
                </h6>
            </div>
            <div class="card-body p-3">
                @if($upcomingDeadlines->isEmpty())
                    <div class="text-muted small text-center py-3">No tasks due within the next 7 days.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($upcomingDeadlines as $ut)
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('nba.tasks.show', $ut) }}" class="text-dark fw-semibold text-decoration-none small d-block">
                                    {{ Str::limit($ut->title, 28) }}
                                </a>
                                <span class="badge bg-{{ $ut->priority_color }}-subtle text-{{ $ut->priority_color }}" style="font-size:0.65rem;">
                                    {{ ucfirst($ut->priority) }}
                                </span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger small">
                                {{ $ut->deadline->format('M d') }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- My Meetings -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-success fs-5"></i> My NBA Meetings
                </h6>
                <a href="{{ route('nba.meetings.index') }}" class="btn btn-sm btn-link text-navy fw-semibold p-0 text-decoration-none">View All</a>
            </div>
            <div class="card-body p-3">
                @if($upcomingMeetings->isEmpty())
                    <div class="text-muted small text-center py-3">No NBA meetings scheduled.</div>
                @else
                    @foreach($upcomingMeetings as $m)
                    <div class="border-bottom pb-2 mb-2 last-border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <a href="{{ route('nba.meetings.show', $m) }}" class="fw-semibold text-dark text-decoration-none small">
                                {{ $m->title }}
                            </a>
                            <span class="badge bg-{{ $m->status === 'completed' ? 'success' : 'primary' }}-subtle text-{{ $m->status === 'completed' ? 'success' : 'primary' }}" style="font-size:0.65rem;">
                                {{ ucfirst($m->status) }}
                            </span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            <i class="bi bi-clock me-1"></i> {{ $m->scheduled_at->format('M d, Y g:i A') }}
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>


    </div>
</div>
@endsection
