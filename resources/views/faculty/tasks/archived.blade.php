@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Archived Tasks</h1>
        <p class="text-muted small mb-0">View archived tasks created by you and restore them when needed</p>
    </div>
    <div>
        <a href="{{ route('faculty.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Active Tasks
        </a>
    </div>
</div>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3 py-3" data-filter-col="title">Task Name</th>
                            <th class="py-3" data-filter-col="faculty">Assigned Faculty</th>
                            <th class="py-3" data-filter-col="assigned_date">Assigned Date</th>
                            <th class="py-3">Archived Date</th>
                            <th class="py-3" data-filter-col="status">Status</th>
                            <th class="pe-3 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedTasks as $task)
                        @php
                            $statusMap = [
                                'pending'              => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                                'in_progress'          => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                'submitted_for_review' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'label' => 'In Review'],
                                'completed'            => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                            ];
                            $st = $statusMap[$task->status] ?? $statusMap['pending'];
                        @endphp
                        <tr data-task-row
                            data-title="{{ $task->title }}"
                            data-priority="{{ $task->priority }}"
                            data-status="{{ $task->status }}"
                            data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                            data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                            data-faculty="{{ $task->assignees ? $task->assignees->pluck('name')->implode(', ') : '' }}">
                        <td class="ps-3">
                            <a href="{{ route('faculty.tasks.show', $task->id) }}" class="fw-semibold text-navy text-decoration-none">
                                {{ $task->title }}
                            </a>
                            <div class="text-muted small">ID: #TSK-{{ $task->id }} · {{ $task->category ?? 'General' }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                @foreach($task->assignees as $assignee)
                                    <img src="{{ $assignee->profile_photo_url }}" class="rounded-circle shadow-sm" style="width:26px;height:26px;object-fit:cover;border:1px solid var(--navy);" title="{{ $assignee->name }}">
                                @endforeach
                                <span class="small text-muted ms-1">({{ $task->assignees->count() }})</span>
                            </div>
                        </td>
                        <td class="text-nowrap small text-muted">
                            <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                        </td>
                        <td class="text-nowrap small text-muted">
                            <i class="bi bi-clock-history me-1"></i>{{ $task->deleted_at->format('M d, Y g:i A') }}
                        </td>
                        <td>
                            <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $st['bg'] }}; color: {{ $st['text'] }}; font-weight: 600; font-size: 0.72rem;">
                                {{ $st['label'] }} ({{ $task->overall_progress }}%)
                            </span>
                        </td>
                        <td class="pe-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('faculty.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-secondary fw-medium px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-eye me-1"></i>Details
                                </a>
                                @can('restore', $task)
                                <form action="{{ route('faculty.tasks.restore', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore task {{ $task->title }} to active status?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-medium px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-2 d-block mb-2 text-secondary"></i>
                            No archived tasks found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($archivedTasks->hasPages())
    <div class="mt-4">
        {{ $archivedTasks->links() }}
    </div>
@endif
@endsection
