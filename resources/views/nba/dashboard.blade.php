@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">NBA Coordinator Dashboard</h1>
        <p class="text-muted small mb-0">Overview of NBA accreditation tasks, progress analytics, and upcoming meetings</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('nba.tasks.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
        <a href="{{ route('nba.meetings.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--maroon);">
            <i class="bi bi-calendar-plus"></i> Schedule Meeting
        </a>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4" id="stat-cards-row">
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Total Tasks" :value="$totalTasks" color="navy" icon="bi bi-list-task" link="{{ route('nba.tasks.index') }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Completed Tasks" :value="$completedTasks" color="success" icon="bi bi-check-circle" link="{{ route('nba.tasks.index', ['status' => 'completed']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="In Progress" :value="$inProgressTasks" color="warning" icon="bi bi-clock-history" link="{{ route('nba.tasks.index', ['status' => 'in_progress']) }}" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="Overdue Tasks" :value="$overdueTasks" color="danger" icon="bi bi-exclamation-triangle" link="{{ route('nba.tasks.index', ['status' => 'overdue']) }}" />
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
<!-- QUICK FILTER BUTTONS                                          -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mb-3" style="border-radius: var(--radius, 8px);">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="fw-semibold small me-1" style="color: var(--navy);"><i class="bi bi-lightning-charge me-1"></i>Quick Filters:</span>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn active" data-filter="all" style="font-size: 0.78rem;">All</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="pending" style="font-size: 0.78rem;">Pending</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="completed" style="font-size: 0.78rem;">Completed</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="overdue" style="font-size: 0.78rem;">Overdue</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="today" style="font-size: 0.78rem;"><i class="bi bi-calendar-day me-1"></i>Today's Deadlines</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="this_week" style="font-size: 0.78rem;"><i class="bi bi-calendar-week me-1"></i>This Week</button>
            <button class="btn btn-sm rounded-pill fw-medium px-3 quick-filter-btn" data-filter="high_priority" style="font-size: 0.78rem;"><i class="bi bi-arrow-up-circle me-1"></i>High Priority</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- ADVANCED FILTER SECTION                                       -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);" id="advanced-filters-card">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-funnel me-2" style="color: var(--maroon);"></i>Advanced Filters
        </h6>
        <button class="btn btn-sm btn-link text-decoration-none p-0" id="toggle-filters-btn" style="color: var(--navy); font-size: 0.82rem;">
            <i class="bi bi-chevron-down" id="toggle-filters-icon"></i> Toggle
        </button>
    </div>
    <div class="card-body py-3 px-4" id="filter-body">
        <div class="row g-3 align-items-end">
            <!-- Faculty -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Faculty</label>
                <select class="form-select form-select-sm" id="filter-faculty">
                    <option value="">All Faculty</option>
                    @foreach($faculties as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Status -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Status</label>
                <select class="form-select form-select-sm" id="filter-status">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <!-- Priority -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Priority</label>
                <select class="form-select form-select-sm" id="filter-priority">
                    <option value="all">All</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <!-- Category -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Category</label>
                <select class="form-select form-select-sm" id="filter-category">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Deadline -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Deadline</label>
                <select class="form-select form-select-sm" id="filter-deadline">
                    <option value="all">All</option>
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <!-- Search -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label class="form-label fw-semibold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.5px;">Search</label>
                <input type="text" class="form-control form-control-sm" id="filter-search" placeholder="Task, Faculty, Description...">
            </div>
        </div>
        <!-- Custom Date Range (hidden by default) -->
        <div class="row g-3 mt-1 d-none" id="custom-date-row">
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.68rem;">Start Date</label>
                <input type="date" class="form-control form-control-sm" id="filter-custom-start">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.68rem;">End Date</label>
                <input type="date" class="form-control form-control-sm" id="filter-custom-end">
            </div>
        </div>
        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-sm text-white fw-medium px-4 shadow-sm" id="apply-filters-btn" style="background-color: var(--navy);">
                <i class="bi bi-funnel-fill me-1"></i>Apply Filters
            </button>
            <button class="btn btn-sm btn-outline-secondary fw-medium px-4" id="reset-filters-btn">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
            <span class="text-muted small align-self-center ms-2" id="result-count" style="font-size: 0.78rem;"></span>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- FILTERED TASKS TABLE & UPCOMING MEETINGS                      -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- FILTERED TASKS TABLE & UPCOMING MEETINGS                      -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="row g-4">
    <!-- Tasks Table (75-78% on XL screens) -->
    <div class="col-xl-9 col-lg-8 col-12">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-list-task me-2" style="color: var(--navy);"></i>Recent Tasks
                </h6>
                <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="color: var(--navy);">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tasks-table">
                        <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-3 py-3">Task</th>
                                <th class="py-3">Faculty</th>
                                <th class="py-3">Priority</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Assigned Date</th>
                                <th class="py-3">Deadline</th>
                                <th class="py-3">Category</th>
                                <th class="py-3">Progress</th>
                                <th class="pe-3 py-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-tbody">
                            @forelse($recentTasks as $task)
                            <tr onclick="window.location='{{ route('nba.tasks.show', $task) }}'" style="cursor: pointer;">
                                <td class="ps-3">
                                    <a href="{{ route('nba.tasks.show', $task) }}" class="text-decoration-none fw-semibold text-truncate d-inline-block" style="color: var(--navy); max-width: 180px;" title="{{ $task->title }}">
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
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td class="text-nowrap" style="font-size:0.78rem;">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="text-nowrap" style="font-size:0.78rem;">
                                    <span class="{{ $task->is_overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y') }}
                                        @if($task->is_overdue)
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>
                                        @endif
                                    </span>
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
                                    <a href="{{ route('nba.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-2 py-1" style="font-size: 0.75rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
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

    <!-- Upcoming Meetings & Documents Awaiting Review Sidebar Widget (22-25% on XL screens) -->
    <!-- Right Column: Meetings, Deadlines, Notifications -->

    <div class="col-xl-3 col-lg-4 col-12 d-flex flex-column gap-4">
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

        <!-- Documents Awaiting Review -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check text-warning fs-5"></i> Documents Awaiting Review
                </h6>
            </div>
            <div class="card-body p-3">
                @if($documentsAwaitingReview->isEmpty())
                    <div class="text-muted small text-center py-3">No documents pending review.</div>
                @else
                    @foreach($documentsAwaitingReview as $doc)
                    <div class="d-flex gap-2 align-items-start border-bottom pb-2 mb-2">
                        <i class="bi bi-file-earmark-text text-primary mt-1" style="font-size:0.85rem;"></i>
                        <div>
                            <div class="small text-dark fw-semibold">{{ $doc->file_name }}</div>
                            <div class="text-muted" style="font-size:0.7rem;">{{ $doc->user->name ?? 'Unknown' }} &bull; {{ $doc->task->title ?? '' }}</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<style>
    .quick-filter-btn {
        background: #f0f2f5; color: #555; border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    .quick-filter-btn:hover, .quick-filter-btn.active {
        background: var(--navy); color: #fff; border-color: var(--navy);
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const FILTER_URL = "{{ route('nba.api.filterTasks') }}";
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

    /* ── Update Doughnut Chart ── */
    function updateChart(stats) {
        if (!doughnutChart) { initChart(stats); return; }
        doughnutChart.data.datasets[0].data = [stats.completed||0, stats.in_progress||0, stats.pending||0, stats.overdue||0];
        doughnutChart.update();
    }

    /* ── Update Completion Gauge (SVG redraw) ── */
    function updateGauge(rate) {
        const body = document.getElementById('completion-gauge-body');
        const size = 180, stroke = 10, r = (size/2)-stroke, circ = 2*Math.PI*r;
        const offset = circ - (rate/100)*circ;
        const color = rate >= 75 ? '#1a8a4a' : rate >= 40 ? '#f0a500' : '#a32d2d';
        body.innerHTML = `
            <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="transform:rotate(-90deg)">
                <circle cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke="#e9ecef" stroke-width="${stroke}"/>
                <circle cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke="${color}" stroke-width="${stroke}"
                    stroke-dasharray="${circ}" stroke-dashoffset="${offset}" stroke-linecap="round"
                    style="transition:stroke-dashoffset 0.6s ease"/>
            </svg>
            <div style="position:absolute;display:flex;flex-direction:column;align-items:center;">
                <span style="font-size:2rem;font-weight:700;color:var(--navy);">${rate}%</span>
                <span style="font-size:0.72rem;text-transform:uppercase;color:#6c757d;letter-spacing:0.5px;">Completed</span>
            </div>`;
        body.style.position = 'relative';
    }

    /* ── Update Stat Cards ── */
    function updateStatCards(stats) {
        const cards = document.querySelectorAll('#stat-cards-row .card');
        const vals = [stats.total, stats.completed, stats.in_progress, stats.overdue];
        cards.forEach((card, i) => {
            const valEl = card.querySelector('.fs-3, .display-6, h3, [class*="fw-bold"]');
            if (valEl) valEl.textContent = vals[i] ?? 0;
        });
    }

    /* ── Build Tasks Table Rows ── */
    function renderTasksTable(tasks) {
        const tbody = document.getElementById('tasks-tbody');
        if (!tasks.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-clipboard-x fs-2 d-block mb-2 text-secondary"></i>No tasks match your current filters.</td></tr>';
            return;
        }
        tbody.innerHTML = tasks.map(t => {
            const assigneeBadges = (t.assignee_photos||[]).slice(0,2).map(a =>
                `<img src="${a.photo_url}" class="rounded-circle" style="width:22px;height:22px;object-fit:cover;border:1px solid var(--navy);" title="${a.name}">`
            ).join('');
            const extras = (t.assignee_photos||[]).length > 2 ? `<span class="badge bg-light text-dark border" style="font-size:0.6rem;">+${t.assignee_photos.length-2}</span>` : '';
            return `<tr onclick="window.location='${t.show_url}'" style="cursor:pointer;">
                <td class="ps-4"><a href="${t.show_url}" class="text-decoration-none fw-semibold" style="color:var(--navy);">${t.title}</a></td>
                <td><div class="d-flex align-items-center gap-1">${assigneeBadges}${extras}</div></td>
                <td><span class="badge rounded-pill bg-${t.priority_color} px-2 py-1">${t.priority.charAt(0).toUpperCase()+t.priority.slice(1)}</span></td>
                <td><span class="badge rounded-pill bg-${t.status_color} px-2 py-1">${t.status_label}</span></td>
                <td><span class="text-muted" style="font-size:0.82rem;"><i class="bi bi-calendar-event me-1"></i>${t.assigned_date}</span></td>
                <td><span class="${t.is_overdue?'text-danger fw-semibold':'text-muted'}" style="font-size:0.82rem;"><i class="bi bi-calendar3 me-1"></i>${t.deadline}${t.is_overdue && t.days_overdue > 0 ? ' <span class=\"badge bg-danger ms-1\" style=\"font-size:0.6rem;\">Overdue by '+t.days_overdue+' '+(t.days_overdue===1?'day':'days')+'</span>' : ''}</span></td>
                <td><span class="badge bg-light text-dark border" style="font-size:0.72rem;">${t.category}</span></td>
                <td><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:6px;min-width:50px;background:#e9ecef;border-radius:3px;"><div class="progress-bar" style="width:${t.overall_progress}%;background:var(--navy);border-radius:3px;"></div></div><span class="fw-semibold small text-dark" style="min-width:30px;font-size:0.75rem;">${t.overall_progress}%</span></div></td>
                <td class="pe-4"><a href="${t.show_url}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size:0.8rem;border-color:var(--navy);color:var(--navy);" onclick="event.stopPropagation();">View</a></td>
            </tr>`;
        }).join('');
    }

    /* ── Collect current filter values ── */
    function collectFilters() {
        return {
            faculty_id: document.getElementById('filter-faculty').value,
            status: document.getElementById('filter-status').value,
            priority: document.getElementById('filter-priority').value,
            category: document.getElementById('filter-category').value,
            deadline_filter: document.getElementById('filter-deadline').value,
            custom_start: document.getElementById('filter-custom-start').value,
            custom_end: document.getElementById('filter-custom-end').value,
            search: document.getElementById('filter-search').value.trim(),
        };
    }

    /* ── Apply Filters (AJAX) ── */
    function applyFilters() {
        const params = new URLSearchParams();
        const filters = collectFilters();
        Object.entries(filters).forEach(([k,v]) => { if(v) params.set(k,v); });

        document.getElementById('chart-badge').textContent = 'Filtering...';

        fetch(FILTER_URL + '?' + params.toString())
            .then(r => r.json())
            .then(data => {
                updateChart(data.stats);
                updateGauge(data.stats.completion_rate);
                updateStatCards(data.stats);
                renderTasksTable(data.tasks);
                document.getElementById('result-count').textContent = `${data.tasks.length} task(s) found`;
                document.getElementById('chart-badge').textContent = params.toString() ? 'Filtered' : 'Live Data';
            })
            .catch(err => {
                console.error('Filter error:', err);
                document.getElementById('chart-badge').textContent = 'Error';
            });
    }

    /* ── Reset Filters ── */
    function resetFilters() {
        document.getElementById('filter-faculty').value = '';
        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-priority').value = 'all';
        document.getElementById('filter-category').value = 'all';
        document.getElementById('filter-deadline').value = 'all';
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-custom-start').value = '';
        document.getElementById('filter-custom-end').value = '';
        document.getElementById('custom-date-row').classList.add('d-none');
        document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.quick-filter-btn[data-filter="all"]').classList.add('active');
        applyFilters();
    }

    /* ── Quick Filter Button Logic ── */
    document.querySelectorAll('.quick-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Reset advanced filters first
            document.getElementById('filter-faculty').value = '';
            document.getElementById('filter-search').value = '';
            document.getElementById('filter-custom-start').value = '';
            document.getElementById('filter-custom-end').value = '';
            document.getElementById('custom-date-row').classList.add('d-none');

            const filter = this.dataset.filter;
            switch(filter) {
                case 'all':
                    document.getElementById('filter-status').value = 'all';
                    document.getElementById('filter-priority').value = 'all';
                    document.getElementById('filter-category').value = 'all';
                    document.getElementById('filter-deadline').value = 'all';
                    break;
                case 'pending':
                case 'completed':
                case 'overdue':
                    document.getElementById('filter-status').value = filter;
                    document.getElementById('filter-priority').value = 'all';
                    document.getElementById('filter-category').value = 'all';
                    document.getElementById('filter-deadline').value = 'all';
                    break;
                case 'today':
                    document.getElementById('filter-status').value = 'all';
                    document.getElementById('filter-priority').value = 'all';
                    document.getElementById('filter-category').value = 'all';
                    document.getElementById('filter-deadline').value = 'today';
                    break;
                case 'this_week':
                    document.getElementById('filter-status').value = 'all';
                    document.getElementById('filter-priority').value = 'all';
                    document.getElementById('filter-category').value = 'all';
                    document.getElementById('filter-deadline').value = 'this_week';
                    break;
                case 'high_priority':
                    document.getElementById('filter-status').value = 'all';
                    document.getElementById('filter-priority').value = 'high';
                    document.getElementById('filter-category').value = 'all';
                    document.getElementById('filter-deadline').value = 'all';
                    break;
            }
            applyFilters();
        });
    });

    /* ── Toggle Filters Panel ── */
    document.getElementById('toggle-filters-btn').addEventListener('click', function() {
        const body = document.getElementById('filter-body');
        const icon = document.getElementById('toggle-filters-icon');
        body.classList.toggle('d-none');
        icon.classList.toggle('bi-chevron-down');
        icon.classList.toggle('bi-chevron-up');
    });

    /* ── Custom Date Range Toggle ── */
    document.getElementById('filter-deadline').addEventListener('change', function() {
        document.getElementById('custom-date-row').classList.toggle('d-none', this.value !== 'custom');
    });

    /* ── Wire Buttons ── */
    document.getElementById('apply-filters-btn').addEventListener('click', applyFilters);
    document.getElementById('reset-filters-btn').addEventListener('click', resetFilters);

    /* ── Search on Enter ── */
    document.getElementById('filter-search').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') applyFilters();
    });

    /* ── Initial Chart Load ── */
    fetch("{{ route('nba.api.charts') }}")
        .then(r => r.json())
        .then(data => initChart(data.taskStatus || {}))
        .catch(err => console.error('Error loading chart:', err));
});
</script>
@endsection
