@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Tasks Management</h1>
        <p class="text-muted small mb-0">Assign, track and manage faculty tasks</p>
    </div>
    <div class="btn-toolbar gap-2 mb-2 mb-md-0">
        <a href="{{ route('hod.tasks.create') }}" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy);">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
    </div>
</div>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Task Title</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Assigned Date</th>
                        <th class="py-3">Deadline</th>
                        <th class="py-3">Assignees</th>
                        <th class="pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr onclick="window.location='{{ route('hod.tasks.show', $task) }}'" style="cursor: pointer;">
                        <td class="ps-4">
                            <a href="{{ route('hod.tasks.show', $task) }}" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                {{ $task->title }}
                            </a>
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
                                    'pending'     => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                                    'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                    'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                                    'overdue'     => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Overdue'],
                                ];
                                $s = $statusMap[$task->status] ?? $statusMap['pending'];
                            @endphp
                            <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar-event me-1"></i>{{ $task->created_at->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $task->is_overdue ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y') }}
                                @if($task->is_overdue)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue</span>
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @foreach($task->assignees->take(3) as $assignee)
                                    <img src="{{ $assignee->profile_photo_url }}" alt="{{ $assignee->name }}"
                                         class="rounded-circle shadow-sm object-fit-cover"
                                         style="width: 32px; height: 32px; margin-left: {{ $loop->first ? '0' : '-8px' }}; border: 2px solid #fff; z-index: {{ 10 - $loop->index }};"
                                         title="{{ $assignee->name }}">
                                @endforeach
                                @if($task->assignees->count() > 3)
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                         style="width: 32px; height: 32px; background-color: #e9ecef; color: var(--navy); font-size: 0.65rem; margin-left: -8px; border: 2px solid #fff; z-index: 1;">
                                        +{{ $task->assignees->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4">
                            <a href="{{ route('hod.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);" onclick="event.stopPropagation();">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-secondary"></i>
                            No tasks found. Create one!
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
@endsection
