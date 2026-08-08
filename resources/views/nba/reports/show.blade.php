@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>NBA Detailed Report</h1>
        <p class="text-muted small mb-0">Full audit, document, and progress report for: {{ $task->title }}</p>
    </div>
    <div>
        <a href="{{ route('nba.reports.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to NBA Reports</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-navy mb-0">{{ $task->title }}</h5>
            <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }} px-3 py-1 text-capitalize">{{ str_replace('_', ' ', $task->status) }}</span>
        </div>
        <p class="text-secondary small mb-4">{{ $task->description ?? 'No description provided.' }}</p>

        <div class="row g-3 text-center bg-light p-3 rounded-3 mb-4" style="font-size:0.85rem;">
            <div class="col-3">
                <span class="text-muted d-block small">Owner</span>
                <span class="fw-bold text-navy">{{ $task->creator->name ?? 'Self' }}</span>
            </div>
            <div class="col-3">
                <span class="text-muted d-block small">Priority</span>
                <span class="fw-bold text-navy text-capitalize">{{ $task->priority }}</span>
            </div>
            <div class="col-3">
                <span class="text-muted d-block small">Progress</span>
                <span class="fw-bold text-success">{{ $task->overall_progress }}%</span>
            </div>
            <div class="col-3">
                <span class="text-muted d-block small">Deadline</span>
                <span class="fw-bold {{ $task->is_overdue ? 'text-danger' : 'text-navy' }}">{{ $task->deadline->format('M d, Y') }}@if($task->is_overdue) <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>@endif</span>
            </div>
        </div>

        <h6 class="fw-bold text-navy mb-3">Assigned Faculty Breakdown</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle" style="font-size:0.83rem;">
                <thead class="bg-light text-muted">
                    <tr>
                        <th>Faculty</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($task->assignees as $assignee)
                    <tr>
                        <td class="fw-semibold text-navy">{{ $assignee->name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $assignee->pivot->role)) }}</td>
                        <td><span class="badge bg-{{ $assignee->pivot->status === 'completed' ? 'success' : 'warning' }}-subtle text-capitalize">{{ str_replace('_', ' ', $assignee->pivot->status) }}</span></td>
                        <td>{{ $assignee->pivot->progress_percentage }}%</td>
                        <td>{{ $assignee->pivot->remarks ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($task->documents->count() > 0)
        <h6 class="fw-bold text-navy mb-3">Submitted Documents</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.83rem;">
                <thead class="bg-light text-muted">
                    <tr>
                        <th>Document Name</th>
                        <th>Uploaded By</th>
                        <th>Version</th>
                        <th>Review Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($task->documents as $doc)
                    <tr>
                        <td class="fw-semibold text-navy">{{ $doc->file_name }}</td>
                        <td>{{ $doc->user->name ?? 'Faculty' }}</td>
                        <td>v{{ $doc->version }}</td>
                        <td><span class="badge bg-{{ $doc->review_status === 'approved' ? 'success' : 'warning' }}-subtle text-capitalize">{{ str_replace('_', ' ', $doc->review_status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
