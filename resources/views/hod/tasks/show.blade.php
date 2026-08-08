@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Task Details</h1>
        <p class="text-muted small mb-0">View task information and assignee progress</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($task->trashed())
            <span class="badge bg-warning text-dark px-3 py-2 me-1" style="font-size:0.8rem;"><i class="bi bi-archive-fill me-1"></i>Archived</span>
            @can('delete', $task)
            <form action="{{ route('hod.tasks.restore', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore task {{ $task->title }} to active status?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success fw-medium d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore Task
                </button>
            </form>
            @endcan
        @else
            @can('reassign', $task)
            <button class="btn btn-sm btn-outline-primary fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#reassignTaskModal" style="border-color: var(--navy); color: var(--navy);">
                <i class="bi bi-arrow-repeat"></i> Reassign Task
            </button>
            @endcan
            @can('addCollaborator', $task)
            <button class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal">
                <i class="bi bi-person-plus"></i> Add Collaborator
            </button>
            @endcan
            @can('delete', $task)
            <button class="btn btn-sm btn-outline-warning fw-medium text-dark d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#archiveTaskModal">
                <i class="bi bi-archive"></i> Archive Task
            </button>
            @endcan
        @endif
        <a href="{{ route('hod.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to Tasks
        </a>
    </div>
</div>

@if($task->trashed())
<div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm border-0 mb-4" role="alert">
    <div>
        <i class="bi bi-archive me-2 fs-5"></i>
        <strong>This task is currently archived.</strong> All progress, documents, comments, collaborators and history remain preserved.
    </div>
    <form action="{{ route('hod.tasks.restore', $task->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success fw-medium">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore Task Now
        </button>
    </form>
</div>
@endif

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveTaskModal" tabindex="-1" aria-labelledby="archiveTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-navy" id="archiveTaskModalLabel">
                    <i class="bi bi-archive me-2 text-warning"></i>Archive Task?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-0 text-secondary" style="font-size:0.95rem; line-height:1.6;">
                    This task contains progress, documents, comments, collaborators and history.<br><br>
                    Archiving will remove it from active dashboards but preserve all records.
                </p>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('hod.tasks.destroy', $task) }}" method="POST" class="d-inline">
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

@php
    $priorityMap = [
        'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
        'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17'],
        'high'   => ['bg' => '#fff3e0', 'text' => '#e65100'],
        'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828'],
    ];
    $statusMap = [
        'pending'     => ['bg' => '#fff8e1', 'text' => '#f57f17'],
        'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0'],
        'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
        'overdue'     => ['bg' => '#fce4ec', 'text' => '#c62828'],
    ];
    $roleMap = [
        'owner'           => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Owner'],
        'secondary_owner' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'label' => 'Secondary Owner'],
        'collaborator'    => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Collaborator'],
    ];
    $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
    $s = $statusMap[$task->status] ?? $statusMap['pending'];
@endphp

<!-- Assigned Faculty Photo Avatars Row -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small fw-semibold me-2"><i class="bi bi-people-fill me-1"></i>Assigned ({{ $task->assignees->count() }}):</span>
                @foreach($task->assignees as $assignee)
                    @php
                        $aRole = $assignee->pivot->role ?? 'owner';
                        $r = $roleMap[$aRole] ?? $roleMap['collaborator'];
                    @endphp
                    <div class="position-relative d-inline-block" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>{{ $assignee->name }}</strong><br>{{ $assignee->designation ?? 'Faculty' }}<br><em>{{ $r['label'] }}</em>">
                        <img src="{{ $assignee->profile_photo_url }}" alt="{{ $assignee->name }}" class="rounded-circle shadow-sm object-fit-cover" style="width: 40px; height: 40px; border: 2px solid {{ $r['text'] }}; cursor: pointer;">
                        <span class="position-absolute bottom-0 end-0 badge rounded-pill" style="font-size: 0.5rem; background-color: {{ $r['bg'] }}; color: {{ $r['text'] }}; border: 1px solid {{ $r['text'] }}; padding: 1px 4px; transform: translateY(2px);">
                            {{ $aRole === 'owner' ? 'O' : ($aRole === 'secondary_owner' ? 'SO' : 'C') }}
                        </span>
                    </div>
                @endforeach
            </div>
            @can('addCollaborator', $task)
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal" style="border-color: var(--navy); color: var(--navy);">
                <i class="bi bi-plus-lg"></i>
            </button>
            @endcan
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Task Info Card -->
    <div class="col-lg-7 mb-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-clipboard-data me-2" style="color: var(--gold);"></i>Task Information
                </h6>
            </div>
            <div class="card-body p-4">
                <h4 class="fw-bold mb-2" style="color: var(--navy);">{{ $task->title }}</h4>
                <p class="text-muted mb-0">{{ $task->description ?? 'No description provided.' }}</p>

                <!-- Status / Priority / Assigned Date / Deadline Pills Row -->
                <div class="row text-center mt-4 pt-3 border-top g-2" style="border-color: var(--border) !important;">
                    <div class="col-3">
                        <div class="text-muted small text-uppercase mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">Status</div>
                        <span class="badge rounded-pill px-2 py-2 w-100 text-truncate" style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; font-weight: 600; font-size: 0.75rem;">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                    <div class="col-3 border-start" style="border-color: var(--border) !important;">
                        <div class="text-muted small text-uppercase mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">Priority</div>
                        <span class="badge rounded-pill px-2 py-2 w-100 text-truncate" style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; font-weight: 600; font-size: 0.75rem;">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    <div class="col-3 border-start" style="border-color: var(--border) !important;">
                        <div class="text-muted small text-uppercase mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">Assigned</div>
                        <div class="fw-semibold text-dark" style="font-size: 0.82rem;">
                            <i class="bi bi-calendar-event text-secondary me-1"></i>{{ $task->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="col-3 border-start" style="border-color: var(--border) !important;">
                        <div class="text-muted small text-uppercase mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">Deadline</div>
                        <div class="fw-semibold {{ $task->is_overdue ? 'text-danger' : '' }}" style="{{ !$task->is_overdue ? 'color: var(--navy);' : '' }}; font-size: 0.82rem;">
                            <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y') }}
                            @if($task->is_overdue)
                                <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3 pt-2 border-top text-muted" style="font-size: 0.8rem; border-color: var(--border) !important;">
                    <i class="bi bi-hourglass-split me-1 text-primary"></i><strong>Total Duration:</strong> {{ $task->duration_in_days }} Days
                </div>

                <!-- Overall Progress Bar -->
                <div class="mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0 text-dark" style="font-size: 0.9rem;">Overall Progress</h6>
                        <span class="fw-bold" style="color: var(--navy);">{{ $task->overall_progress }}%</span>
                    </div>
                    <div class="progress" style="height: 10px; background-color: #e9ecef; border-radius: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $task->overall_progress }}%; background-color: {{ $task->overall_progress >= 100 ? '#2e7d32' : ($task->overall_progress >= 50 ? 'var(--navy)' : 'var(--gold)') }}; border-radius: 5px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Faculty Progress Card -->
    <div class="col-lg-5 mb-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-people me-2" style="color: var(--gold);"></i>Assigned Faculty Progress
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($task->assignees as $assignee)
                        @php
                            $aStatus = $assignee->pivot->status;
                            $aColor = $statusMap[$aStatus] ?? $statusMap['pending'];
                            $aRole = $assignee->pivot->role ?? 'owner';
                            $r = $roleMap[$aRole] ?? $roleMap['collaborator'];
                        @endphp
                        <li class="list-group-item border-bottom px-4 py-3" style="border-color: var(--border) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $assignee->profile_photo_url }}" alt="{{ $assignee->name }}"
                                         class="rounded-circle shadow-sm object-fit-cover"
                                         style="width: 36px; height: 36px; border: 2px solid var(--navy); flex-shrink: 0;"
                                         title="{{ $assignee->name }}">
                                    <div>
                                        <span class="fw-semibold text-dark">{{ $assignee->name }}</span>
                                        <span class="badge rounded-pill ms-1" style="background-color: {{ $r['bg'] }}; color: {{ $r['text'] }}; font-size: 0.6rem; font-weight: 600;">{{ $r['label'] }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $aColor['bg'] }}; color: {{ $aColor['text'] }}; font-weight: 600; font-size: 0.7rem;">
                                        {{ ucfirst(str_replace('_', ' ', $aStatus)) }}
                                    </span>
                                    @can('removeCollaborator', $task)
                                        @if($assignee->id !== $task->created_by)
                                            <form method="POST" action="{{ route('tasks.collaborators.destroy', [$task, $assignee]) }}" class="d-inline" onsubmit="return confirm('Remove {{ $assignee->name }} from this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link btn-sm text-danger p-0" title="Remove"><i class="bi bi-x-circle"></i></button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; background-color: #e9ecef; border-radius: 3px;">
                                    <div class="progress-bar" style="width: {{ $assignee->pivot->progress_percentage }}%; background-color: var(--navy); border-radius: 3px;"></div>
                                </div>
                                <span class="small fw-semibold text-muted" style="min-width: 35px">{{ $assignee->pivot->progress_percentage }}%</span>
                            </div>

                            @if($assignee->pivot->remarks)
                                <div class="small text-muted bg-light rounded p-2 mt-2" style="font-size: 0.8rem;">
                                    <i class="bi bi-chat-left-text me-1"></i> {{ $assignee->pivot->remarks }}
                                </div>
                            @endif

                            @php
                                $assigneeDocs = \App\Models\TaskDocument::where('task_id', $task->id)->where('user_id', $assignee->id)->get();
                            @endphp

                            @if($assigneeDocs->isNotEmpty())
                                <div class="mt-3">
                                    <div class="small fw-semibold text-dark mb-2">Attached Documents:</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($assigneeDocs as $doc)
                                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="font-size: 0.75rem; border-radius: 4px;">
                                                <i class="bi bi-file-earmark-text me-1 text-primary"></i> <span class="text-truncate" style="max-width: 150px;">{{ $doc->file_name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($assignee->pivot->completed_at)
                                <div class="small mt-2" style="color: #2e7d32; font-size: 0.8rem;">
                                    <i class="bi bi-check2-all"></i> Completed on {{ Carbon\Carbon::parse($assignee->pivot->completed_at)->format('M d, H:i') }}
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TASK SUBTASKS / CHECKLIST SECTION                             -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-card-checklist me-2" style="color: var(--gold);"></i>Subtasks & Checklist
        </h6>
        @php $items = $task->checklistItems; @endphp
        @if($items->isNotEmpty())
            <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                {{ $items->where('is_completed', true)->count() }} / {{ $items->count() }} Completed
            </span>
        @endif
    </div>
    <div class="card-body p-4">
        <!-- Add Subtask Form -->
        <form action="{{ route('tasks.checklist.store', $task) }}" method="POST" class="d-flex gap-2 mb-3">
            @csrf
            <input type="text" name="title" class="form-control border" placeholder="Add a new subtask / checklist item..." required style="font-size:0.85rem;">
            <button type="submit" class="btn btn-sm text-white fw-medium shadow-sm px-3" style="background-color: var(--navy);">
                <i class="bi bi-plus-lg me-1"></i>Add Item
            </button>
        </form>

        <!-- Checklist Items List -->
        <div class="list-group list-group-flush">
            @forelse($items as $item)
                <div class="list-group-item px-2 py-2.5 border-bottom d-flex align-items-center justify-content-between">
                    <form action="{{ route('tasks.checklist.toggle', [$task, $item]) }}" method="POST" class="d-flex align-items-center gap-2 flex-grow-1">
                        @csrf
                        <input type="checkbox" class="form-check-input mt-0" onchange="this.form.submit()" {{ $item->is_completed ? 'checked' : '' }} style="cursor:pointer;width:18px;height:18px;">
                        <span class="{{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark fw-medium' }}" style="font-size:0.88rem;">
                            {{ $item->title }}
                        </span>
                        @if($item->is_completed && $item->completedByUser)
                            <span class="badge bg-light text-muted border ms-2" style="font-size:0.65rem;">Done by {{ $item->completedByUser->name }}</span>
                        @endif
                    </form>
                    <form action="{{ route('tasks.checklist.destroy', [$task, $item]) }}" method="POST" class="m-0" onsubmit="return confirm('Delete subtask item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link btn-sm text-danger p-0 ms-2"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            @empty
                <div class="text-center py-3 text-muted small">No subtask items created yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- DISCUSSION & COMMENTS SECTION                                 -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-chat-dots me-2" style="color: var(--maroon);"></i>Task Discussion & Comments
        </h6>
    </div>
    <div class="card-body p-4">
        <!-- Post Comment Form -->
        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="mb-2">
                <textarea name="comment" class="form-control border" rows="3" placeholder="Write a comment or update for this task..." required style="font-size:0.88rem;"></textarea>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <input type="file" name="attachment" class="form-control form-control-sm border" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                </div>
                <button type="submit" class="btn btn-sm text-white fw-medium shadow-sm px-4" style="background-color: var(--navy);">
                    <i class="bi bi-send me-1"></i>Post Comment
                </button>
            </div>
        </form>

        <!-- Comments List -->
        <div class="list-group list-group-flush">
            @forelse($task->comments as $comment)
                <div class="list-group-item px-0 py-3 border-bottom">
                    <div class="d-flex align-items-start gap-2.5">
                        <img src="{{ $comment->user->profile_photo_url }}" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;border:1px solid var(--navy);">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark" style="font-size:0.85rem;">{{ $comment->user->name }}</span>
                                <span class="text-muted small" style="font-size:0.72rem;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-dark" style="font-size:0.85rem;">{{ $comment->comment }}</div>
                            @if($comment->attachment_path)
                                <div class="mt-2">
                                    <a href="{{ Storage::url($comment->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="font-size:0.75rem;">
                                        <i class="bi bi-paperclip text-primary"></i> {{ $comment->attachment_name ?? 'Attachment' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-3 text-muted small">No comments posted yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- AUDIT HISTORY TAB / SECTION (Collapsible)                     -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 cursor-pointer" 
         data-bs-toggle="collapse" 
         data-bs-target="#hodAuditHistoryCollapse" 
         aria-expanded="false" 
         aria-controls="hodAuditHistoryCollapse"
         style="cursor: pointer;">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: var(--navy);">
                <i class="bi bi-chevron-right collapse-icon text-muted" style="transition: transform 0.25s ease;"></i>
                <i class="bi bi-journal-text me-1" style="color: var(--navy);"></i>
                <span>Audit History</span>
                <span class="text-muted fw-normal" style="font-size: 0.85rem;">({{ $task->auditLogs->count() }} {{ Str::plural('Change', $task->auditLogs->count()) }})</span>
            </h6>
            <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">Click to expand</span>
        </div>
    </div>
    <div class="collapse" id="hodAuditHistoryCollapse">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.7rem;">
                        <tr>
                            <th class="ps-3 py-2.5">Date & Time</th>
                            <th class="py-2.5">Changed By</th>
                            <th class="py-2.5">Field Changed</th>
                            <th class="py-2.5">Old Value</th>
                            <th class="pe-3 py-2.5">New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($task->auditLogs as $log)
                        <tr>
                            <td class="ps-3 text-nowrap small text-muted">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                            <td class="fw-semibold text-dark" style="font-size:0.82rem;">{{ $log->user->name ?? 'System' }}</td>
                            <td><span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $log->formatted_field_name }}</span></td>
                            <td class="small text-muted" style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->old_value ?? '—' }}</td>
                            <td class="pe-3 small text-dark fw-medium" style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->new_value ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted small">No audit modifications recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- DOCUMENT REVIEW SECTION                                       -->
<!-- ═══════════════════════════════════════════════════════════════ -->
@php
    $allDocs = \App\Models\TaskDocument::where('task_id', $task->id)
        ->with(['user', 'reviewer'])
        ->orderBy('created_at', 'desc')
        ->get();
    $docsByUser = $allDocs->groupBy('user_id');

    // For each user+root doc, keep only the latest version
    $latestDocsPerUser = collect();
    foreach ($docsByUser as $userId => $userDocs) {
        $rootDocs = $userDocs->whereNull('original_document_id');
        $childDocs = $userDocs->whereNotNull('original_document_id');
        foreach ($rootDocs as $root) {
            $latestChild = $childDocs->where('original_document_id', $root->id)->sortByDesc('version')->first();
            $latestDocsPerUser->push($latestChild ?? $root);
        }
        // Orphan children (root from another user, shouldn't normally happen)
        $coveredRoots = $rootDocs->pluck('id');
        $orphans = $childDocs->filter(fn($d) => !$coveredRoots->contains($d->original_document_id));
        foreach ($orphans as $o) {
            $latestDocsPerUser->push($o);
        }
    }
    $latestDocsGrouped = $latestDocsPerUser->groupBy('user_id');
@endphp

@if($allDocs->isNotEmpty())
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-file-earmark-check me-2" style="color: var(--gold);"></i>Document Review
            @if($allDocs->where('review_status', 'submitted')->count() > 0)
                <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">{{ $allDocs->where('review_status', 'submitted')->count() }} Pending</span>
            @endif
        </h6>
    </div>
    <div class="card-body p-4">
        @foreach($latestDocsGrouped as $userId => $docs)
            @php $docUser = $docs->first()->user; @endphp
            <div class="mb-4 {{ !$loop->last ? 'pb-4 border-bottom' : '' }}" style="border-color: var(--border) !important;">
                <!-- Faculty Header -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ $docUser->profile_photo_url }}" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;border:1px solid var(--navy);">
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $docUser->name }}</span>
                    <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">{{ $docs->count() }} document(s)</span>
                </div>

                <!-- Documents -->
                @foreach($docs as $doc)
                    @php $badge = $doc->review_status_badge; @endphp
                    <div class="d-flex align-items-start justify-content-between p-3 rounded mb-2 {{ $doc->review_status === 'submitted' ? 'border border-primary' : 'bg-light' }}" style="{{ $doc->review_status === 'submitted' ? 'border-color: var(--navy) !important; background-color: #f8f9ff;' : '' }}">
                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                            <i class="bi {{ $doc->file_icon }}" style="color: {{ $doc->file_icon_color }}; font-size: 1.4rem; margin-top: 2px;"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $doc->file_name }}</span>
                                    <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">v{{ $doc->version }}</span>
                                    <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; font-weight: 600; font-size: 0.68rem;">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $doc->created_at->format('M d, Y g:i A') }} · {{ $doc->formatted_file_size }}
                                </div>
                                @if($doc->remarks)
                                    <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $doc->remarks }}
                                    </div>
                                @endif
                                @if($doc->review_comments && in_array($doc->review_status, ['approved', 'changes_requested', 'rejected']))
                                    <div class="small mt-1" style="color: {{ $doc->review_status === 'approved' ? '#2e7d32' : ($doc->review_status === 'rejected' ? '#c62828' : '#e65100') }}; font-size: 0.78rem;">
                                        <i class="bi bi-reply me-1"></i><strong>Review:</strong> {{ $doc->review_comments }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-3">
                            {{-- Preview --}}
                            @if($doc->is_previewable)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 0.7rem;" title="Preview">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                            {{-- Download --}}
                            <a href="{{ route('hod.tasks.documents.download', [$task, $doc]) }}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.7rem; border-color: var(--navy); color: var(--navy);" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            {{-- Version History --}}
                            <button class="btn btn-sm btn-outline-info px-2 py-1" style="font-size: 0.7rem;" title="Version History" onclick="loadVersionHistoryHOD({{ $doc->original_document_id ?? $doc->id }}, {{ $task->id }})">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            {{-- Review Actions (only for submitted documents) --}}
                            @if($doc->review_status === 'submitted')
                                <button class="btn btn-sm btn-outline-success px-2 py-1" style="font-size: 0.7rem;" title="Approve" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $doc->id }}" onclick="setReviewAction({{ $doc->id }}, 'approved')">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning px-2 py-1" style="font-size: 0.7rem;" title="Request Changes" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $doc->id }}" onclick="setReviewAction({{ $doc->id }}, 'changes_requested')">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 0.7rem;" title="Reject" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $doc->id }}" onclick="setReviewAction({{ $doc->id }}, 'rejected')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Review Modal for this document --}}
                    @if($doc->review_status === 'submitted')
                    <div class="modal fade" id="reviewModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" action="{{ route('hod.tasks.documents.review', [$task, $doc]) }}">
                                    @csrf
                                    <input type="hidden" name="action" id="reviewAction{{ $doc->id }}" value="approved">
                                    <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                                        <h6 class="modal-title text-white fw-bold" id="reviewModalTitle{{ $doc->id }}">
                                            <i class="bi bi-file-earmark-check me-2"></i>Review Document
                                        </h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-info py-2 px-3 small mb-3">
                                            <i class="bi bi-file-earmark me-1"></i><strong>{{ $doc->file_name }}</strong> (v{{ $doc->version }}) by {{ $docUser->name }}
                                        </div>
                                        <div class="mb-3" id="reviewCommentsDiv{{ $doc->id }}">
                                            <label class="form-label fw-semibold text-dark small">Review Comments</label>
                                            <textarea name="review_comments" class="form-control border" rows="3" placeholder="Provide feedback or reasons..." style="border-color: var(--border) !important;"></textarea>
                                            <small class="text-muted">Required for "Request Changes" and "Reject" actions.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-sm text-white fw-medium" id="reviewSubmitBtn{{ $doc->id }}" style="background-color: var(--navy);">
                                            <i class="bi bi-check2 me-1"></i>Submit Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Version History Modal (HOD) -->
<div class="modal fade" id="versionHistoryModalHOD" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="bi bi-clock-history me-2"></i>Version History
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="versionHistoryContentHOD">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-task-timeline :task="$task" />

<!-- Activity Log Timeline (Collapsible) -->
@if($task->activities->count() > 0)
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 cursor-pointer" 
         data-bs-toggle="collapse" 
         data-bs-target="#hodActivityLogCollapse" 
         aria-expanded="false" 
         aria-controls="hodActivityLogCollapse"
         style="cursor: pointer;">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: var(--navy);">
                <i class="bi bi-chevron-right collapse-icon text-muted" style="transition: transform 0.25s ease;"></i>
                <i class="bi bi-clock-history me-1" style="color: var(--gold);"></i>
                <span>Activity Log</span>
                <span class="text-muted fw-normal" style="font-size: 0.85rem;">({{ $task->activities->count() }} {{ Str::plural('Event', $task->activities->count()) }})</span>
            </h6>
            <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">Click to expand</span>
        </div>
    </div>
    <div class="collapse" id="hodActivityLogCollapse">
        <div class="card-body p-4">
            <div class="position-relative" style="padding-left: 24px;">
                @foreach($task->activities as $activity)
                    @php
                        $iconMap = [
                            'assigned'             => ['icon' => 'bi-person-check-fill', 'color' => '#1565c0'],
                            'collaborator_added'   => ['icon' => 'bi-person-plus-fill',  'color' => '#f57f17'],
                            'collaborator_removed' => ['icon' => 'bi-person-dash-fill',  'color' => '#c62828'],
                            'progress_updated'     => ['icon' => 'bi-arrow-up-circle-fill', 'color' => '#2e7d32'],
                            'status_changed'       => ['icon' => 'bi-arrow-repeat',      'color' => '#7b1fa2'],
                            'completed'            => ['icon' => 'bi-check-circle-fill',  'color' => '#2e7d32'],
                            'document_uploaded'    => ['icon' => 'bi-file-earmark-plus',  'color' => '#1565c0'],
                            'document_replaced'    => ['icon' => 'bi-file-earmark-diff',  'color' => '#f57f17'],
                            'document_submitted'   => ['icon' => 'bi-file-earmark-arrow-up', 'color' => '#7b1fa2'],
                            'document_approved'    => ['icon' => 'bi-file-earmark-check', 'color' => '#2e7d32'],
                            'document_changes_requested' => ['icon' => 'bi-file-earmark-arrow-down', 'color' => '#e65100'],
                            'document_rejected'    => ['icon' => 'bi-file-earmark-x',     'color' => '#c62828'],
                        ];
                        $ai = $iconMap[$activity->action] ?? ['icon' => 'bi-circle-fill', 'color' => '#6b7280'];
                    @endphp
                    <div class="d-flex align-items-start mb-3 position-relative">
                        <!-- Vertical line -->
                        @if(!$loop->last)
                            <div style="position: absolute; left: -14px; top: 20px; bottom: -12px; width: 2px; background: #e9ecef;"></div>
                        @endif
                        <!-- Dot -->
                        <div style="position: absolute; left: -20px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #fff; border: 2px solid {{ $ai['color'] }}; display: flex; align-items: center; justify-content: center; z-index: 2;">
                            <i class="bi {{ $ai['icon'] }}" style="font-size: 0.5rem; color: {{ $ai['color'] }};"></i>
                        </div>
                        <div class="ms-2">
                            <div class="fw-medium text-dark" style="font-size: 0.85rem;">{{ $activity->description }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-clock me-1"></i>{{ $activity->created_at->diffForHumans() }} · {{ $activity->created_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Add Collaborator Modal -->
@can('addCollaborator', $task)
<div class="modal fade" id="addCollaboratorModal" tabindex="-1" aria-labelledby="addCollaboratorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('tasks.collaborators.store', $task) }}">
                @csrf
                <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                    <h5 class="modal-title text-white fw-bold" id="addCollaboratorModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Add Collaborator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Multi-select Faculty -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Select Faculty <span class="text-danger">*</span></label>
                        <select name="collaborators[]" multiple class="form-select" required style="min-height: 120px;">
                            @php
                                $existingIds = $task->assignees->pluck('id')->toArray();
                                $availableFaculty = \App\Models\User::where('department_id', $task->department_id)
                                    ->where('role', 'faculty')
                                    ->whereNotIn('id', $existingIds)
                                    ->get();
                            @endphp
                            @forelse($availableFaculty as $f)
                                <option value="{{ $f->id }}">{{ $f->name }} — {{ $f->designation ?? 'Faculty' }}</option>
                            @empty
                                <option disabled>No available faculty to add</option>
                            @endforelse
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple faculty members.</div>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="collaborator" selected>Collaborator</option>
                            <option value="secondary_owner">Secondary Owner</option>
                        </select>
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason for adding collaborator..."></textarea>
                    </div>

                    <!-- HOD Notification Note -->
                    @if(!auth()->user()->isHod())
                    <div class="alert alert-info py-2 px-3 mb-2 small d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-info"></i>
                        <span>The task HOD will be automatically notified about this collaborator assignment.</span>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-psg-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-person-plus"></i> Assign Collaborator(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Reassign Task Modal -->
@can('reassign', $task)
<div class="modal fade" id="reassignTaskModal" tabindex="-1" aria-labelledby="reassignTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('tasks.reassign', $task) }}">
                @csrf
                <div class="modal-header border-bottom py-3" style="background: linear-gradient(135deg, var(--navy) 0%, #1a3a7a 100%);">
                    <h5 class="modal-title text-white fw-bold" id="reassignTaskModalLabel">
                        <i class="bi bi-arrow-repeat me-2"></i>Reassign Task
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning mt-1"></i>
                        <span>Reassigning will <strong>completely transfer</strong> this task from the selected assignee to a new person. The original assignee will be removed from the task.</span>
                    </div>

                    <!-- Select Current Assignee to Replace -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Replace Current Assignee <span class="text-danger">*</span></label>
                        <select name="old_assignee_id" class="form-select" required>
                            <option value="">— Select who to replace —</option>
                            @foreach($task->assignees as $assignee)
                                <option value="{{ $assignee->id }}">{{ $assignee->name }} — {{ $assignee->designation ?? 'Faculty' }} ({{ ucfirst($assignee->pivot->role ?? 'owner') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select New Assignee -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Assign To <span class="text-danger">*</span></label>
                        <select name="new_assignee_id" class="form-select" required>
                            <option value="">— Select new assignee —</option>
                            @php
                                $availableForReassign = \App\Models\User::where('department_id', $task->department_id)
                                    ->where('role', 'faculty')
                                    ->get();
                            @endphp
                            @forelse($availableForReassign as $f)
                                <option value="{{ $f->id }}">{{ $f->name }} — {{ $f->designation ?? 'Faculty' }}</option>
                            @empty
                                <option disabled>No available faculty</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Reason for Reassignment</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Why is this task being reassigned?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm d-flex align-items-center gap-1 text-white fw-medium" style="background-color: var(--navy);">
                        <i class="bi bi-arrow-repeat"></i> Reassign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('scripts')
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });

    // Set review action on modal open
    function setReviewAction(docId, action) {
        document.getElementById('reviewAction' + docId).value = action;
        const titleEl = document.getElementById('reviewModalTitle' + docId);
        const btnEl = document.getElementById('reviewSubmitBtn' + docId);

        const labels = {
            'approved': { title: 'Approve Document', btnText: '<i class="bi bi-check2-circle me-1"></i>Approve', btnColor: '#2e7d32' },
            'changes_requested': { title: 'Request Changes', btnText: '<i class="bi bi-arrow-repeat me-1"></i>Request Changes', btnColor: '#f57f17' },
            'rejected': { title: 'Reject Document', btnText: '<i class="bi bi-x-circle me-1"></i>Reject', btnColor: '#c62828' },
        };

        const cfg = labels[action] || labels['approved'];
        titleEl.innerHTML = '<i class="bi bi-file-earmark-check me-2"></i>' + cfg.title;
        btnEl.innerHTML = cfg.btnText;
        btnEl.style.backgroundColor = cfg.btnColor;
    }

    // Load version history for HOD view
    function loadVersionHistoryHOD(docId, taskId) {
        const modal = new bootstrap.Modal(document.getElementById('versionHistoryModalHOD'));
        const content = document.getElementById('versionHistoryContentHOD');
        content.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>';
        modal.show();

        fetch(`/hod/tasks/${taskId}/documents/${docId}/versions`)
            .then(r => r.json())
            .then(data => {
                if (!data.versions || data.versions.length === 0) {
                    content.innerHTML = '<div class="text-center py-3 text-muted">No version history available.</div>';
                    return;
                }
                let html = '<div class="list-group list-group-flush">';
                data.versions.forEach(v => {
                    html += `<div class="list-group-item px-0 py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:0.9rem;">
                                    <span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;">v${v.version}</span>
                                    ${v.file_name}
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-person me-1"></i>${v.uploaded_by} · <i class="bi bi-clock me-1"></i>${v.uploaded_at} · ${v.file_size}
                                </div>
                                ${v.remarks ? '<div class="text-muted small mt-1"><i class="bi bi-chat-left-text me-1"></i>' + v.remarks + '</div>' : ''}
                                ${v.review_comments ? '<div class="small mt-1" style="color:#e65100;"><i class="bi bi-reply me-1"></i>Review: ' + v.review_comments + '</div>' : ''}
                                ${v.reviewed_by ? '<div class="text-muted small mt-1"><i class="bi bi-person-check me-1"></i>Reviewed by: ' + v.reviewed_by + (v.reviewed_at ? ' on ' + v.reviewed_at : '') + '</div>' : ''}
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-2 py-1" style="background-color:${v.review_status.bg};color:${v.review_status.text};font-weight:600;font-size:0.68rem;">${v.review_status.label}</span>
                                <a href="${v.download_url}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size:0.7rem;"><i class="bi bi-download"></i></a>
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                content.innerHTML = html;
            })
            .catch(() => {
                content.innerHTML = '<div class="text-center py-3 text-danger">Failed to load version history.</div>';
            });
    }
</script>
@endsection
