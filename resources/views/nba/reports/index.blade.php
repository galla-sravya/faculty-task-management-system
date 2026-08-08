@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-bar-chart-line me-2 text-primary"></i>NBA Reports</h1>
        <p class="text-muted small mb-0">Progress and accreditation reports for your NBA Accreditation tasks</p>
    </div>
    <div>
        <a href="{{ route('nba.reports.export') }}" class="btn btn-sm btn-success fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i> Export CSV Report
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center bg-white border-start border-4 border-primary">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Total NBA Tasks</div>
            <div class="h3 fw-bold mb-0 text-navy">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center bg-white border-start border-4 border-success">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Completed Tasks</div>
            <div class="h3 fw-bold mb-0 text-success">{{ $stats['completed'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center bg-white border-start border-4 border-info">
            <div class="text-muted small text-uppercase fw-semibold mb-1">In Progress</div>
            <div class="h3 fw-bold mb-0 text-info">{{ $stats['in_progress'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center bg-white border-start border-4 border-danger">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Overdue Tasks</div>
            <div class="h3 fw-bold mb-0 text-danger">{{ $stats['overdue'] }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        @if($tasks->isEmpty())
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                No NBA task report data available.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Task Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Overall Progress</th>
                            <th>Deadline</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $t)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-navy">{{ $t->title }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">Assigned: {{ $t->created_at->format('M d, Y') }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $t->category ?? 'NBA' }}</span></td>
                            <td><span class="badge bg-{{ $t->priority_color }}-subtle text-{{ $t->priority_color }}">{{ ucfirst($t->priority) }}</span></td>
                            <td><span class="badge bg-{{ $t->status_color }}-subtle text-{{ $t->status_color }}">{{ str_replace('_', ' ', $t->status) }}</span></td>
                            <td style="width:140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $t->overall_progress }}%"></div>
                                    </div>
                                    <span class="fw-semibold small">{{ $t->overall_progress }}%</span>
                                </div>
                            </td>
                            <td><span class="{{ $t->is_overdue ? 'text-danger fw-bold' : '' }}">{{ $t->deadline->format('M d, Y') }}@if($t->is_overdue) <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $t->days_overdue }} {{ $t->days_overdue === 1 ? 'day' : 'days' }}</span>@endif</span></td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('nba.reports.show', $t) }}" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size:0.78rem;">Detailed Report</a>
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
