@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">HOD Dashboard</h1>
        <p class="text-muted small mb-0">Overview of department tasks, progress analytics, and upcoming meetings</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('hod.tasks.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
        <a href="{{ route('hod.meetings.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--maroon);">
            <i class="bi bi-calendar-plus"></i> Schedule Meeting
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4" id="stat-cards-row">
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Total Tasks" :value="$totalTasks" color="navy" icon="bi bi-list-task" link="{{ route('hod.tasks.index') }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Completed Tasks" :value="$completedTasks" color="success" icon="bi bi-check-circle" link="{{ route('hod.tasks.index', ['status' => 'completed']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="In Progress" :value="$inProgressTasks" color="warning" icon="bi bi-clock-history" link="{{ route('hod.tasks.index', ['status' => 'in_progress']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Overdue Tasks" :value="$overdueTasks" color="danger" icon="bi bi-exclamation-triangle" link="{{ route('hod.tasks.index', ['status' => 'overdue']) }}" />
    </div>
</div>

<!-- Task Status Doughnut & Progress Ring Row -->
<div class="row g-4 mb-4">
    <!-- Chart.js Doughnut for Task Status -->
    <div class="col-lg-7 col-xl-8">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-pie-chart me-2" style="color: var(--maroon);"></i>Task Status Distribution
                </h6>
                <span class="badge rounded-pill bg-light text-dark border" id="chart-badge">Live Data</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center p-4">
                <div style="width: 100%; max-width: 380px; max-height: 260px; position: relative;">
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Work Completion Progress Ring -->
    <div class="col-lg-5 col-xl-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-speedometer2 me-2" style="color: var(--gold);"></i>Overall Completion
                </h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4" id="completion-gauge-body">
                <x-progress-ring :percent="$completionRate" label="Total work completion" :size="180" />
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- FILTERED TASKS TABLE & UPCOMING MEETINGS                      -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="row g-4">
    <!-- Tasks Table (75-78% on XL screens) -->
    <div class="col-xl-9 col-lg-8 col-12">
<div class="task-table-wrapper">
    <div class="filter-chips-container mb-2" style="display: none;"></div>
    <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold" style="color: var(--navy);">
                <i class="bi bi-list-task me-2" style="color: var(--navy);"></i>Recent Tasks
            </h6>
            <a href="{{ route('hod.tasks.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="color: var(--navy);">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tasks-table">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3 py-3" data-filter-col="title">Task</th>
                            <th class="py-3" data-filter-col="faculty">Faculty</th>
                            <th class="py-3" data-filter-col="priority">Priority</th>
                            <th class="py-3" data-filter-col="status">Status</th>
                            <th class="py-3" data-filter-col="assigned_date">Assigned Date</th>
                            <th class="py-3" data-filter-col="deadline">Deadline</th>
                            <th class="py-3" data-filter-col="category">Category</th>
                            <th class="py-3" data-filter-col="progress">Progress</th>
                            <th class="pe-3 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-tbody">
                        @forelse($recentTasks as $task)
                        <tr data-task-row
                            data-title="{{ $task->title }}"
                            data-priority="{{ $task->priority }}"
                            data-status="{{ $task->status }}"
                            data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                            data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                            data-deadline-status="{{ $task->smart_deadline['type'] }}"
                            data-faculty="{{ $task->assignees->pluck('name')->implode(', ') }}"
                            data-category="{{ $task->category ?? 'General' }}"
                            data-progress="{{ $task->overall_progress }}"
                            onclick="window.location='{{ route('hod.tasks.show', $task) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <a href="{{ route('hod.tasks.show', $task) }}" class="text-decoration-none fw-semibold text-truncate d-inline-block" style="color: var(--navy); max-width: 180px;" title="{{ $task->title }}">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @foreach($task->assignees->take(2) as $assignee)
                                        <img src="{{ $assignee->profile_photo_url }}" class="rounded-circle" style="width:22px;height:22px;object-fit:cover;border:1px solid var(--navy);" title="{{ $assignee->name }}">
                                    @endforeach
                                    @if($task->assignees->count() > 2)
                                        <span class="badge bg-light text-dark border" style="font-size:0.6rem;">+{{ $task->assignees->count()-2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $task->priority_color }} px-2 py-1" style="font-size:0.7rem;">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $task->status_color }} px-2 py-1" style="font-size:0.7rem;">
                                    {{ $task->formatted_status }}
                                </span>
                            </td>
                            <td class="text-nowrap" style="font-size:0.78rem;">
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="text-nowrap" style="font-size:0.78rem;">
                                <x-task-deadline-badge :task="$task" />
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $task->category ?? 'General' }}</span>
                            </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height:6px;min-width:45px;background:#e9ecef;border-radius:3px;">
                                            <div class="progress-bar" style="width:{{ $task->overall_progress }}%;background:var(--navy);border-radius:3px;"></div>
                                        </div>
                                        <span class="fw-semibold small text-dark" style="min-width:28px;font-size:0.72rem;">{{ $task->overall_progress }}%</span>
                                    </div>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('hod.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-2 py-1" style="font-size: 0.75rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No tasks found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Upcoming Meetings & Documents Awaiting Review Sidebar Widget (22-25% on XL screens) -->
    <div class="col-xl-3 col-lg-4 col-12 d-flex flex-column gap-4">
        <!-- Upcoming Meetings -->
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-calendar-event me-2" style="color: var(--maroon);"></i>Upcoming Meetings
                </h6>
                <a href="{{ route('hod.meetings.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="color: var(--navy); font-size: 0.8rem;">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($upcomingMeetings as $meeting)
                        <a href="{{ route('hod.meetings.show', $meeting) }}" class="list-group-item list-group-item-action bg-white border-bottom py-2.5 px-1" style="cursor: pointer;">
                            <div class="d-flex w-100 justify-content-between align-items-start gap-1 mb-1">
                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.85rem;" title="{{ $meeting->title }}">{{ $meeting->title }}</h6>
                                <span class="badge bg-light text-primary border flex-shrink-0" style="font-size: 0.65rem;">
                                    {{ $meeting->scheduled_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.72rem;">
                                <span><i class="bi bi-geo-alt me-1 text-danger"></i>{{ $meeting->venue ?? 'TBA' }}</span>
                                <span><i class="bi bi-clock me-1 text-secondary"></i>{{ $meeting->scheduled_at->format('M d, H:i') }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-calendar-x fs-4 d-block mb-1 text-secondary"></i>
                            <p class="mb-0 small">No upcoming meetings scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Documents Awaiting Review Widget -->
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-file-earmark-arrow-up me-2" style="color: var(--gold);"></i>Awaiting Review
                </h6>
                @if(isset($documentsAwaitingReview) && $documentsAwaitingReview->count() > 0)
                    <span class="badge bg-primary rounded-pill" style="font-size: 0.68rem;">{{ $documentsAwaitingReview->count() }}</span>
                @endif
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($documentsAwaitingReview ?? [] as $doc)
                        <a href="{{ route('hod.tasks.show', $doc->task_id) }}" class="list-group-item list-group-item-action bg-white border-bottom py-2 px-1">
                            <div class="d-flex w-100 justify-content-between align-items-start gap-1 mb-1">
                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $doc->file_name }}">
                                    <i class="bi {{ $doc->file_icon }} me-1" style="color: {{ $doc->file_icon_color }};"></i>{{ $doc->file_name }}
                                </h6>
                                <span class="badge bg-light text-dark border flex-shrink-0" style="font-size: 0.62rem;">v{{ $doc->version }}</span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size: 0.72rem;">
                                Task: {{ $doc->task->title ?? 'Task' }}
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted mt-1" style="font-size: 0.7rem;">
                                <span><i class="bi bi-person me-1"></i>{{ $doc->user->name ?? 'Faculty' }}</span>
                                <span>{{ $doc->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-file-earmark-check fs-4 d-block mb-1 text-secondary"></i>
                            <p class="mb-0 small">No documents awaiting review.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let doughnutChart = null;

    /* ── Build initial Doughnut Chart ── */
    function initChart(stats) {
        const ctx = document.getElementById('taskStatusChart').getContext('2d');
        doughnutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Pending', 'Overdue'],
                datasets: [{
                    data: [stats.completed||0, stats.in_progress||0, stats.pending||0, stats.overdue||0],
                    backgroundColor: ['#1a8a4a','#f0a500','#12275a','#a32d2d'],
                    borderWidth: 2, borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position:'right', labels:{ usePointStyle:true, font:{ family:"'Inter',sans-serif", size:12 } } } },
                cutout: '68%'
            }
        });
    }

    /* ── Initial Chart Load ── */
    fetch("{{ route('hod.api.charts') }}")
        .then(r => r.json())
        .then(data => initChart(data.taskStatus || {}))
        .catch(err => console.error('Error loading chart:', err));
});
</script>
@endsection

