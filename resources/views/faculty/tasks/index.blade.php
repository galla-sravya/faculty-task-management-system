@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Tasks Management</h1>
        <p class="text-muted small mb-0">Create, assign, track and collaborate on departmental tasks</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('faculty.tasks.archived') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-archive"></i> Archived Tasks
            @if($archivedCount > 0)
                <span class="badge bg-secondary ms-1">{{ $archivedCount }}</span>
            @endif
        </a>
        <a href="{{ route('faculty.tasks.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
    </div>
</div>

<!-- View Filter Tabs -->
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('faculty.tasks.index', ['view' => 'all']) }}"
       class="btn btn-sm {{ $viewType === 'all' ? 'btn-navy text-white fw-bold shadow-sm' : 'btn-light text-secondary border' }}"
       style="{{ $viewType === 'all' ? 'background-color: var(--navy);' : '' }}">
        <i class="bi bi-grid me-1"></i> All Tasks <span class="badge {{ $viewType === 'all' ? 'bg-light text-navy' : 'bg-secondary' }} ms-1">{{ $allCount }}</span>
    </a>
    <a href="{{ route('faculty.tasks.index', ['view' => 'assigned']) }}"
       class="btn btn-sm {{ $viewType === 'assigned' ? 'btn-navy text-white fw-bold shadow-sm' : 'btn-light text-secondary border' }}"
       style="{{ $viewType === 'assigned' ? 'background-color: var(--navy);' : '' }}">
        <i class="bi bi-person-check me-1"></i> Assigned to Me <span class="badge {{ $viewType === 'assigned' ? 'bg-light text-navy' : 'bg-secondary' }} ms-1">{{ $assignedCount }}</span>
    </a>
    <a href="{{ route('faculty.tasks.index', ['view' => 'created']) }}"
       class="btn btn-sm {{ $viewType === 'created' ? 'btn-navy text-white fw-bold shadow-sm' : 'btn-light text-secondary border' }}"
       style="{{ $viewType === 'created' ? 'background-color: var(--navy);' : '' }}">
        <i class="bi bi-person-plus me-1"></i> Created by Me <span class="badge {{ $viewType === 'created' ? 'bg-light text-navy' : 'bg-secondary' }} ms-1">{{ $createdCount }}</span>
    </a>
</div>

<div class="task-table-wrapper">
    <x-task-table-filters />
    <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3" data-filter-col="title">Task Title</th>
                            <th class="py-3" data-filter-col="priority">Priority</th>
                            <th class="py-3" data-filter-col="status">Status</th>
                            <th class="py-3" data-filter-col="progress">Progress</th>
                            <th class="py-3" data-filter-col="faculty">Assignees / Creator</th>
                            <th class="py-3" data-filter-col="deadline">Deadline</th>
                            <th class="pe-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        @php
                            $myPivot = $task->assignees()->where('user_id', auth()->id())->first()?->pivot;
                            $isCreator = ($task->created_by === auth()->id());
                        @endphp
                        <tr data-task-row
                            data-title="{{ $task->title }}"
                            data-priority="{{ $task->priority }}"
                            data-status="{{ $task->status }}"
                            data-progress="{{ $task->overall_progress }}"
                            data-assigned-date="{{ $task->created_at->format('Y-m-d') }}"
                            data-deadline-date="{{ $task->deadline ? $task->deadline->format('Y-m-d') : '' }}"
                            data-deadline-status="{{ $task->smart_deadline['type'] }}"
                            data-faculty="{{ $task->assignees->pluck('name')->implode(', ') }}"
                            data-category="{{ $task->category ?? '' }}"
                            onclick="window.location='{{ route('faculty.tasks.show', $task) }}'" style="cursor: pointer;">
                            <td class="ps-4">
                                <a href="{{ route('faculty.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                    {{ $task->title }}
                                </a>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    @if($task->category)
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ $task->category }}</span>
                                    @endif
                                    @if($isCreator)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Created by You</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $priorityMap = [
                                        'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Low'],
                                        'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Medium'],
                                        'high'   => ['bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'High'],
                                        'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Urgent'],
                                    ];
                                    $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
                                @endphp
                                <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                    {{ $p['label'] }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending'              => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                                        'in_progress'          => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                        'submitted_for_review' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'label' => 'In Review'],
                                        'completed'            => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                                        'overdue'              => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Overdue'],
                                    ];
                                    $s = $statusMap[$task->status] ?? $statusMap['pending'];
                                @endphp
                                <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                    {{ $task->formatted_status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px; min-width: 70px; background-color: #e9ecef; border-radius: 4px;">
                                        <div class="progress-bar" style="width: {{ $task->overall_progress }}%; background-color: {{ $task->overall_progress >= 100 ? '#2e7d32' : ($task->overall_progress >= 50 ? 'var(--navy)' : 'var(--gold)') }}; border-radius: 4px;"></div>
                                    </div>
                                    <span class="fw-semibold small text-dark" style="min-width: 32px;">{{ $task->overall_progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @foreach($task->assignees->take(3) as $assignee)
                                        <img src="{{ $assignee->profile_photo_url }}" alt="{{ $assignee->name }}"
                                             class="rounded-circle shadow-sm object-fit-cover"
                                             style="width: 28px; height: 28px; margin-left: {{ $loop->first ? '0' : '-8px' }}; border: 2px solid #fff; z-index: {{ 10 - $loop->index }};"
                                             title="{{ $assignee->name }}">
                                    @endforeach
                                    @if($task->assignees->count() > 3)
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                             style="width: 28px; height: 28px; background-color: #e9ecef; color: var(--navy); font-size: 0.65rem; margin-left: -8px; border: 2px solid #fff; z-index: 1;">
                                            +{{ $task->assignees->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted small mt-1" style="font-size:0.72rem;">
                                    By: {{ $task->creator->name ?? 'HOD' }}
                                </div>
                            </td>
                            <td>
                                <x-task-deadline-badge :task="$task" />
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1" onclick="event.stopPropagation();">
                                    <a href="{{ route('faculty.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-2 py-1" style="font-size: 0.78rem; border-color: var(--navy); color: var(--navy);">
                                        View
                                    </a>
                                    @can('update', $task)
                                    <a href="{{ route('faculty.tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary fw-medium px-2 py-1" style="font-size: 0.78rem;">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('delete', $task)
                                    <button type="button" class="btn btn-sm btn-outline-warning fw-medium text-dark px-2 py-1" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $task->id }}">
                                        Archive
                                    </button>
                                    <!-- Archive Modal -->
                                    <div class="modal fade" id="archiveModal{{ $task->id }}" tabindex="-1" aria-hidden="true" onclick="event.stopPropagation();">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-light border-bottom">
                                                    <h5 class="modal-title fw-bold text-navy">
                                                        <i class="bi bi-archive me-2 text-warning"></i>Archive Task?
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <p class="mb-0 text-secondary" style="font-size:0.92rem; line-height:1.6;">
                                                        This task contains progress, documents, comments, and history.<br><br>
                                                        Archiving will remove it from active lists but preserve all records. You can restore it later from Archived Tasks.
                                                    </p>
                                                </div>
                                                <div class="modal-footer bg-light border-top">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('faculty.tasks.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                                                            <i class="bi bi-archive me-1"></i>Archive Task
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-secondary"></i>
                                No tasks found matching your criteria.
                                <div class="mt-2">
                                    <a href="{{ route('faculty.tasks.create') }}" class="btn btn-sm text-white fw-medium px-3" style="background-color: var(--navy);">
                                        <i class="bi bi-plus-lg me-1"></i> Create a New Task
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tasks->hasPages())
            <div class="px-4 py-3 border-top" style="border-color: var(--border) !important;">
                {{ $tasks->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
