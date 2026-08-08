@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-{{ $task->priority_color }}-subtle text-{{ $task->priority_color }} text-capitalize px-3 py-1">{{ $task->priority }} Priority</span>
            <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }} text-capitalize px-3 py-1">{{ str_replace('_', ' ', $task->status) }}</span>
            <span class="badge bg-light text-dark border px-3 py-1">{{ $task->category ?? 'NBA Accreditation' }}</span>
        </div>
        <h1 class="h3 fw-bold text-navy mb-0">{{ $task->title }}</h1>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($task->trashed())
            <span class="badge bg-warning text-dark px-3 py-2 me-1" style="font-size:0.8rem;"><i class="bi bi-archive-fill me-1"></i>Archived</span>
            @can('restore', $task)
                <form action="{{ route('nba.tasks.restore', $task->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success fw-medium"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore Task</button>
                </form>
            @endcan
        @else
            @can('addCollaborator', $task)
            <button class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal">
                <i class="bi bi-person-plus"></i> Add Collaborator
            </button>
            @endcan

            @can('update', $task)
            <a href="{{ route('nba.tasks.edit', $task) }}" class="btn btn-sm btn-outline-navy fw-medium d-flex align-items-center gap-1">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endcan

            @can('delete', $task)
            <button class="btn btn-sm btn-outline-danger fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#archiveTaskModal">
                <i class="bi bi-archive"></i> Archive
            </button>
            @endcan
        @endif

        <a href="{{ route('nba.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to Tasks</a>
    </div>
</div>

@if($task->trashed())
<div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm border-0 mb-4" role="alert">
    <div>
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <strong>This NBA task is currently archived.</strong> It is hidden from active dashboards and faculty lists.
    </div>
    @can('restore', $task)
    <form action="{{ route('nba.tasks.restore', $task->id) }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-success fw-medium px-3"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore Now</button>
    </form>
    @endcan
</div>
@endif

<div class="row g-4">
    <!-- Left Column: Task Overview & Activity -->
    <div class="col-lg-8">
        <!-- Task Details Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-2"><i class="bi bi-file-text me-2 text-primary"></i>Task Description</h6>
                <p class="text-secondary small mb-4" style="line-height:1.6;">{{ $task->description ?? 'No description provided.' }}</p>

                <!-- Task Progress Bar -->
                <div class="mb-4 p-3 bg-light rounded-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-navy small">Overall Completion Progress</span>
                        <span class="badge bg-success text-white fw-bold">{{ $task->overall_progress }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: {{ $task->overall_progress }}%"></div>
                    </div>
                </div>

                <div class="row g-3 text-center border-top pt-3" style="font-size: 0.82rem;">
                    <div class="col-4">
                        <span class="text-muted d-block">Created By</span>
                        <span class="fw-bold text-navy">{{ $task->creator->name ?? 'System' }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block">Deadline</span>
                        <span class="fw-bold {{ $task->is_overdue ? 'text-danger' : 'text-navy' }}">{{ $task->deadline->format('M d, Y g:i A') }}</span>
                    </div>
                    <div class="col-4">
                        <span class="text-muted d-block">Assigned Date</span>
                        <span class="fw-bold text-navy">{{ $task->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subtasks & Checklist Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-check2-square text-primary fs-5"></i> Subtasks & Checklist
                </h6>
                <span class="badge bg-light text-dark border">
                    {{ $task->checklistItems->where('is_completed', true)->count() }} / {{ $task->checklistItems->count() }} Done
                </span>
            </div>
            <div class="card-body p-3">
                <!-- Add Subtask Form -->
                <form action="{{ route('tasks.checklist.store', $task) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="text" name="title" class="form-control" placeholder="Add subtask / checklist item..." required>
                        <button type="submit" class="btn btn-navy px-3"><i class="bi bi-plus-lg me-1"></i>Add Subtask</button>
                    </div>
                </form>

                @if($task->checklistItems->isEmpty())
                    <div class="text-muted small text-center py-2">No subtasks added yet.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($task->checklistItems as $item)
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <form action="{{ route('tasks.checklist.toggle', [$task, $item]) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none border-0 bg-transparent">
                                        <i class="bi {{ $item->is_completed ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5"></i>
                                    </button>
                                </form>
                                <span class="{{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'fw-medium text-dark' }} small">
                                    {{ $item->title }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($item->is_completed)
                                    <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">Completed</span>
                                @endif
                                <form action="{{ route('tasks.checklist.destroy', [$task, $item]) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-link p-0 text-danger text-decoration-none border-0 bg-transparent" title="Delete Subtask" onclick="if (confirm('Are you sure you want to remove this work item?')) { this.closest('form').submit(); }">
                                        <i class="bi bi-trash small"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <!-- Task Discussion & Comments -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text text-info fs-5"></i> Task Discussion & Comments
                </h6>
            </div>
            <div class="card-body p-4">
                <!-- Post Comment Form -->
                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <div class="mb-2">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Write a comment or update for the team..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <input type="file" name="attachment" class="form-control form-control-sm w-50" style="font-size:0.78rem;">
                        <button type="submit" class="btn btn-sm btn-psg-primary px-3"><i class="bi bi-send me-1"></i> Post Comment</button>
                    </div>
                </form>

                <!-- Comments List -->
                @if($task->comments->isEmpty())
                    <div class="text-muted small text-center py-3">No comments posted yet. Be the first to comment!</div>
                @else
                    <div class="comments-list">
                        @foreach($task->comments as $comment)
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <img src="{{ $comment->user->profile_photo_url }}" class="rounded-circle" style="width:36px;height:36px;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-navy small">{{ $comment->user->name }}</span>
                                    <span class="text-muted" style="font-size:0.7rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-secondary small mb-2" style="white-space: pre-line;">{{ $comment->comment }}</div>
                                @if($comment->attachment_path)
                                    <a href="{{ asset('storage/' . $comment->attachment_path) }}" target="_blank" class="badge bg-light text-dark border text-decoration-none">
                                        <i class="bi bi-paperclip me-1"></i> {{ $comment->attachment_name ?? 'Attachment' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Audit History (Collapsible) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom cursor-pointer"
                 data-bs-toggle="collapse" 
                 data-bs-target="#nbaAuditHistoryCollapse" 
                 aria-expanded="false" 
                 aria-controls="nbaAuditHistoryCollapse"
                 style="cursor: pointer;">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                        <i class="bi bi-chevron-right collapse-icon text-muted" style="transition: transform 0.25s ease;"></i>
                        <i class="bi bi-journal-text text-secondary fs-5"></i>
                        <span>Audit History</span>
                        <span class="text-muted fw-normal" style="font-size: 0.85rem;">({{ $task->auditLogs->count() }} {{ Str::plural('Change', $task->auditLogs->count()) }})</span>
                    </h6>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">Click to expand</span>
                </div>
            </div>
            <div class="collapse" id="nbaAuditHistoryCollapse">
                <div class="card-body p-0">
                    @if($task->auditLogs->isEmpty())
                        <div class="p-3 text-muted small text-center">No audit logs recorded yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-3">Date</th>
                                        <th>User</th>
                                        <th>Field Changed</th>
                                        <th>Old Value</th>
                                        <th>New Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->auditLogs as $log)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $log->created_at->format('M d, H:i') }}</td>
                                        <td class="fw-semibold text-navy">{{ $log->user->name ?? 'System' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $log->field_name)) }}</span></td>
                                        <td class="text-danger small">{{ $log->old_value ?? 'None' }}</td>
                                        <td class="text-success small">{{ $log->new_value ?? 'None' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Assignees & Document Review -->
    <div class="col-lg-4">
        <!-- Assignees List Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-people text-primary fs-5"></i> Assigned Faculty
                </h6>
                <span class="badge bg-primary-subtle text-primary">{{ $task->assignees->count() }} Assigned</span>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @foreach($task->assignees as $assignee)
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $assignee->profile_photo_url }}" class="rounded-circle" style="width:28px;height:28px;">
                                <div>
                                    <div class="fw-semibold text-navy small">{{ $assignee->name }}</div>
                                    <div class="text-muted" style="font-size:0.7rem;">{{ ucfirst(str_replace('_', ' ', $assignee->pivot->role)) }}</div>
                                </div>
                            </div>
                            <span class="badge bg-{{ $assignee->pivot->status === 'completed' ? 'success' : ($assignee->pivot->status === 'in_progress' ? 'info' : 'warning') }}-subtle text-capitalize" style="font-size:0.68rem;">
                                {{ str_replace('_', ' ', $assignee->pivot->status) }}
                            </span>
                        </div>
                        <div class="progress mt-2" style="height:4px;">
                            <div class="progress-bar bg-success" style="width: {{ $assignee->pivot->progress_percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submitted Documents Review Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-up text-warning fs-5"></i> Task Documents
                </h6>
            </div>
            <div class="card-body p-3">
                @if($task->documents->isEmpty())
                    <div class="text-muted small text-center py-3">No documents submitted yet.</div>
                @else
                    @foreach($task->documents as $doc)
                    <div class="p-3 border rounded-3 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-navy small"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> {{ $doc->file_name }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">By {{ $doc->user->name ?? 'Faculty' }} &bull; v{{ $doc->version }}</div>
                            </div>
                            <span class="badge bg-{{ $doc->review_status === 'approved' ? 'success' : ($doc->review_status === 'rejected' ? 'danger' : 'warning') }}-subtle text-capitalize" style="font-size:0.68rem;">
                                {{ str_replace('_', ' ', $doc->review_status) }}
                            </span>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-2">
                            <a href="{{ route('nba.tasks.documents.download', [$task, $doc]) }}" class="btn btn-xs btn-outline-secondary py-1 px-2" style="font-size:0.75rem;">
                                <i class="bi bi-download me-1"></i> Download
                            </a>

                            @can('reviewDocument', $task)
                            <button class="btn btn-xs btn-psg-primary py-1 px-2" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#reviewDocModal{{ $doc->id }}">
                                Review Document
                            </button>
                            @endcan
                        </div>
                    </div>

                    <!-- Review Modal for Doc -->
                    @can('reviewDocument', $task)
                    <div class="modal fade" id="reviewDocModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('nba.tasks.documents.review', [$task, $doc]) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold text-navy">Review Document: {{ $doc->file_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-navy">Decision</label>
                                            <select name="action" class="form-select" required>
                                                <option value="approved">Approve Document</option>
                                                <option value="changes_requested">Request Changes</option>
                                                <option value="rejected">Reject Document</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-navy">Review Comments</label>
                                            <textarea name="review_comments" class="form-control" rows="3" placeholder="Provide feedback or remarks..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-psg-primary btn-sm">Submit Review</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endcan
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Collaborator Modal -->
@can('addCollaborator', $task)
<div class="modal fade" id="addCollaboratorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tasks.collaborators.store', $task) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-navy"><i class="bi bi-person-plus me-1"></i> Add Collaborator to NBA Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-navy">Select Faculty</label>
                        <select name="collaborators[]" class="form-select" required>
                            @foreach(\App\Models\User::where('department_id', auth()->user()->department_id)->get() as $f)
                                @if(!$task->assignees->pluck('id')->contains($f->id))
                                    <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->designation }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-navy">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="collaborator">Collaborator</option>
                            <option value="secondary_owner">Secondary Owner</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-navy">Reason / Remarks</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Why is this collaborator being assigned?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-psg-primary btn-sm">Add Collaborator</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- Archive Task Modal -->
@can('delete', $task)
<div class="modal fade" id="archiveTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('nba.tasks.destroy', $task) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-archive me-1"></i> Archive NBA Task</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to archive <strong>"{{ $task->title }}"</strong>?</p>
                    <p class="text-muted small mt-2">Archiving will hide this task from active lists while preserving all audit history and uploaded documents for restoration if needed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Archive Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
