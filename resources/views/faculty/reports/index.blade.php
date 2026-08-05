@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Completed Reports</h1>
        <p class="text-muted small mb-0">Archive of tasks you have fully completed</p>
    </div>
</div>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4 border-0 py-3 text-uppercase">Task Title</th>
                    <th class="border-0 py-3 text-uppercase text-center">Collaborators</th>
                    <th class="border-0 py-3 text-uppercase">My Completion Date</th>
                    <th class="border-0 py-3 text-uppercase">My Duration</th>
                    <th class="pe-4 border-0 py-3 text-uppercase text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    @php
                        $myPivot = $task->pivot;
                    @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-semibold text-dark" style="font-size: 0.95rem;">{{ $task->title }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $task->description }}</div>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $task->assignees_count }}</span>
                        </td>
                        <td class="py-3 text-muted" style="font-size: 0.9rem;">
                            <i class="bi bi-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($myPivot->completed_at)->format('M d, Y') }}
                        </td>
                        <td class="py-3 text-muted" style="font-size: 0.9rem;">
                            <i class="bi bi-stopwatch me-1"></i>{{ \App\Support\TimelineFormatter::format($myPivot->created_at, $myPivot->completed_at) }}
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('faculty.reports.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium rounded-pill px-3">
                                View Record <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                            You have no completed tasks in your archive yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
