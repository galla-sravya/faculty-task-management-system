@extends('layouts.app')

@section('content')
@php
    $priorityMap = [
        'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
        'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17'],
        'high'   => ['bg' => '#fff3e0', 'text' => '#e65100'],
        'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828'],
    ];
    $roleMap = [
        'owner'           => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Owner'],
        'secondary_owner' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'label' => 'Secondary Owner'],
        'collaborator'    => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Collaborator'],
    ];
    $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
@endphp

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Update Task Progress</h1>
        <p class="text-muted small mb-0">Report your progress, add remarks, and manage task collaborators</p>
    </div>
    <div class="d-flex gap-2">
        @can('addCollaborator', $task)
        <button class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal">
            <i class="bi bi-person-plus"></i> Reassign / Add Collaborator
        </button>
        @endcan
        <a href="{{ route('faculty.tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to Tasks
        </a>
    </div>
</div>

<!-- Assigned Faculty Photo Avatars Row -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small fw-semibold me-2"><i class="bi bi-people-fill me-1"></i>Assigned Faculty ({{ $task->assignees->count() }}):</span>
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
                <i class="bi bi-plus-lg"></i> Add
            </button>
            @endcan
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Task Details Card -->
    <div class="col-lg-6 mb-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-clipboard-data me-2" style="color: var(--gold);"></i>Task Details
                </h6>
            </div>
            <div class="card-body p-4">
                <h4 class="fw-bold mb-2" style="color: var(--navy);">{{ $task->title }}</h4>
                <p class="text-muted mb-0">{{ $task->description ?? 'No description.' }}</p>

                <div class="mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
                    <table class="table table-borderless mb-0" style="font-size: 0.9rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2" style="width: 130px;">Assigned By</td>
                                <td class="py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $task->creator->profile_photo_url }}" alt="{{ $task->creator->name }}"
                                             class="rounded-circle shadow-sm object-fit-cover"
                                             style="width: 28px; height: 28px; border: 1px solid var(--navy); flex-shrink: 0;"
                                             title="{{ $task->creator->name }}">
                                        <span class="fw-semibold text-dark">{{ $task->creator->name }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Priority</td>
                                <td class="py-2">
                                    <span class="badge rounded-pill px-3 py-1" style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; font-weight: 600; font-size: 0.75rem;">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Assigned Date</td>
                                <td class="py-2">
                                    <span class="fw-semibold text-dark">
                                        <i class="bi bi-calendar-event me-1 text-secondary"></i>{{ $task->created_at->format('M d, Y h:i A') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Deadline</td>
                                <td class="py-2">
                                    <span class="fw-semibold {{ $task->is_overdue ? 'text-danger' : '' }}" style="{{ !$task->is_overdue ? 'color: var(--navy);' : '' }}">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $task->deadline->format('M d, Y h:i A') }}
                                        @if($task->is_overdue)
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue</span>
                                        @endif
                                    </span>
                                    <span class="badge bg-light text-muted border ms-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-hourglass-split me-1 text-primary"></i>{{ $task->duration_in_days }} Days Duration
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Update Card -->
    <div class="col-lg-6 mb-4">
        <div class="card bg-white shadow-sm border-0 h-100" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-pencil-square me-2" style="color: var(--gold);"></i>My Progress
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('faculty.tasks.updateProgress', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Automated Read-Only Progress Display -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center mb-1">
                            <span>Current Progress <span class="badge bg-light text-muted border fw-normal ms-1" style="font-size:0.72rem;">Calculated Automatically</span></span>
                            <span class="badge rounded-pill px-3 py-2" style="background-color: var(--navy); color: #fff; font-size: 0.9rem;">{{ $task->overall_progress }}%</span>
                        </label>

                        <div class="progress mt-2" style="height: 14px; background-color: #e9ecef; border-radius: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: {{ $task->overall_progress }}%; background-color: var(--navy); border-radius: 6px; transition: width 0.3s ease;">
                            </div>
                        </div>

                        <div class="text-muted mt-2 d-flex align-items-center gap-1.5" style="font-size: 0.78rem;" data-bs-toggle="tooltip" title="Progress is automatically calculated based on completed task activities.">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span>Progress is automatically calculated based on completed task activities.</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span>Workflow Stage</span>
                            @php $stBadge = $task->status_badge_details; @endphp
                            <span class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $stBadge['class'] ?? '' }}" style="{{ $stBadge['style'] ?? '' }} font-size: 0.78rem;">
                                {{ $stBadge['label'] }}
                            </span>
                        </label>
                        <select name="status" id="statusSelect" class="form-select border rounded-3 py-2 px-3" style="border-color: var(--border) !important; font-size: 0.88rem; font-weight: 500;">
                            <option value="not_started" {{ in_array($pivot->status, ['not_started', 'pending']) ? 'selected' : '' }}>Not Started</option>
                            <option value="collecting_resources" {{ $pivot->status === 'collecting_resources' ? 'selected' : '' }}>Collecting Resources</option>
                            <option value="working_on_task" {{ in_array($pivot->status, ['working_on_task', 'in_progress']) ? 'selected' : '' }}>Working on Task</option>
                            <option value="documents_uploaded" {{ $pivot->status === 'documents_uploaded' ? 'selected' : '' }}>Supporting Documents Uploaded</option>
                            <option value="checklist_completed" {{ $pivot->status === 'checklist_completed' ? 'selected' : '' }}>Checklist Completed</option>
                            <option value="submitted_for_review" {{ in_array($pivot->status, ['submitted_for_review', 'pending_review']) ? 'selected' : '' }}>Submitted for Review</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control border" style="border-color: var(--border) !important;" rows="3" placeholder="Any updates for the HOD?">{{ $pivot->remarks }}</textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                            <i class="bi bi-check2-circle me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TASK SUBTASKS / CHECKLIST SECTION                             -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mt-4 mb-4" style="border-radius: var(--radius, 8px);">
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
                </div>
            @empty
                <div class="text-center py-3 text-muted small">No subtasks assigned yet.</div>
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
                <textarea name="comment" class="form-control border" rows="3" placeholder="Write a comment or update for the HOD..." required style="font-size:0.88rem;"></textarea>
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
         data-bs-target="#facultyAuditHistoryCollapse" 
         aria-expanded="false" 
         aria-controls="facultyAuditHistoryCollapse"
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
    <div class="collapse" id="facultyAuditHistoryCollapse">
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
<!-- SHARED WORKSPACE DOCUMENTS MANAGEMENT SECTION                  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: var(--navy);">
                <i class="bi bi-folder2-open" style="color: var(--gold); font-size: 1.2rem;"></i>Shared Workspace Documents
            </h6>
            <span class="text-muted small" style="font-size:0.75rem;">Documents uploaded by all assigned faculty collaborators</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($latestDocuments) && $latestDocuments->where('user_id', auth()->id())->where('review_status', 'draft')->count() > 0)
                <form action="{{ route('faculty.tasks.documents.submit', $task) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm fw-medium text-white shadow-sm" style="background-color: var(--navy); font-size: 0.8rem;" onclick="return confirm('Submit your draft documents for HOD review?')">
                        <i class="bi bi-send me-1"></i>Submit My Drafts for Review
                    </button>
                </form>
            @endif
            <button class="btn btn-sm btn-outline-primary fw-medium" data-bs-toggle="collapse" data-bs-target="#uploadSection" style="border-color: var(--navy); color: var(--navy); font-size: 0.8rem;">
                <i class="bi bi-cloud-upload me-1"></i>Upload Document
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Upload Form (Collapsible) -->
        <div class="collapse mb-4" id="uploadSection">
            <div class="bg-light rounded p-3 border" style="border-color: var(--border) !important;">
                <form action="{{ route('faculty.tasks.documents.store', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Select Files</label>
                        <input type="file" name="documents[]" class="form-control border" style="border-color: var(--border) !important;" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.png,.jpg,.jpeg">
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Supported: PDF, DOCX, XLSX, PPTX, ZIP, PNG, JPG. Max 10MB each.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Remarks (optional)</label>
                        <textarea name="remarks" class="form-control border" style="border-color: var(--border) !important;" rows="2" placeholder="Add any notes about these documents..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: var(--navy);">
                        <i class="bi bi-upload me-1"></i>Upload as Draft
                    </button>
                </form>
            </div>
        </div>

        <!-- Documents Grouped By Faculty -->
        @if(isset($groupedDocuments) && $groupedDocuments->isNotEmpty())
            @foreach($groupedDocuments as $uploaderId => $facultyDocs)
                @php
                    $uploader = $facultyDocs->first()->user ?? \App\Models\User::find($uploaderId);
                    $isSelf = auth()->id() === $uploaderId;
                @endphp
                <div class="card border mb-4 shadow-none rounded-3" style="border-color: var(--border) !important;">
                    <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $uploader->profile_photo_url }}" alt="{{ $uploader->name }}" class="rounded-circle shadow-sm" style="width:28px;height:28px;object-fit:cover;border:1px solid var(--navy);">
                            <div>
                                <span class="fw-bold text-dark small">{{ $uploader->name }}</span>
                                <span class="text-muted small ms-1" style="font-size:0.72rem;">({{ $uploader->designation ?? 'Faculty' }})</span>
                                @if($isSelf)
                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.65rem;">You</span>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $facultyDocs->count() }} Document(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white text-uppercase text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="ps-3 py-2.5">Document Name</th>
                                        <th class="py-2.5">Uploaded By</th>
                                        <th class="py-2.5">Date & Time</th>
                                        <th class="py-2.5">Version</th>
                                        <th class="py-2.5">File Size</th>
                                        <th class="py-2.5">Status</th>
                                        <th class="pe-3 py-2.5 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facultyDocs as $doc)
                                        @php $badge = $doc->review_status_badge; @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi {{ $doc->file_icon }}" style="color: {{ $doc->file_icon_color }}; font-size: 1.2rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 200px; font-size: 0.85rem;" title="{{ $doc->file_name }}">{{ $doc->file_name }}</div>
                                                        @if($doc->remarks)
                                                            <div class="text-muted text-truncate" style="font-size: 0.7rem; max-width: 200px;" title="{{ $doc->remarks }}">Note: {{ $doc->remarks }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small text-dark">
                                                <span class="fw-medium">{{ $doc->user->name ?? 'Faculty' }}</span>
                                            </td>
                                            <td class="text-nowrap" style="font-size: 0.78rem;">
                                                <span class="text-dark d-block fw-medium">{{ $doc->created_at->format('M d, Y') }}</span>
                                                <span class="text-muted" style="font-size:0.7rem;">{{ $doc->created_at->format('g:i A') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">v{{ $doc->version }}</span>
                                            </td>
                                            <td class="small text-muted" style="font-size: 0.78rem;">
                                                {{ $doc->formatted_file_size }}
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-2 py-1" style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; font-weight: 600; font-size: 0.7rem;">
                                                    {{ $badge['label'] }}
                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="d-flex align-items-center gap-1 justify-content-end">
                                                    {{-- Preview --}}
                                                    @if($doc->is_previewable)
                                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 0.7rem;" title="Preview Document">
                                                            <i class="bi bi-eye"></i> Preview
                                                        </a>
                                                    @endif
                                                    {{-- Download --}}
                                                    <a href="{{ route('faculty.tasks.documents.download', [$task, $doc]) }}" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.7rem; border-color: var(--navy); color: var(--navy);" title="Download Document">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                    {{-- Version History --}}
                                                    <button class="btn btn-sm btn-outline-info px-2 py-1" style="font-size: 0.7rem;" title="Version History" onclick="loadVersionHistory({{ $doc->original_document_id ?? $doc->id }}, {{ $task->id }})">
                                                        <i class="bi bi-clock-history"></i> History
                                                    </button>
                                                    {{-- Replace (Only document owner) --}}
                                                    @if($isSelf && in_array($doc->review_status, ['draft', 'changes_requested']))
                                                        <button class="btn btn-sm btn-outline-warning px-2 py-1" style="font-size: 0.7rem;" title="Replace Document" data-bs-toggle="modal" data-bs-target="#replaceDocModal{{ $doc->id }}">
                                                            <i class="bi bi-arrow-repeat"></i> Replace
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        {{-- Review Comments Row --}}
                                        @if($doc->review_comments && in_array($doc->review_status, ['changes_requested', 'rejected']))
                                            <tr>
                                                <td colspan="7" class="ps-3 pe-3 py-2" style="background-color: {{ $doc->review_status === 'rejected' ? '#fce4ec' : '#fff8e1' }};">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-chat-left-text {{ $doc->review_status === 'rejected' ? 'text-danger' : 'text-warning' }}" style="font-size: 0.85rem; margin-top: 2px;"></i>
                                                        <div>
                                                            <div class="fw-semibold small" style="font-size: 0.78rem; color: {{ $doc->review_status === 'rejected' ? '#c62828' : '#f57f17' }};">
                                                                {{ $doc->review_status === 'rejected' ? 'Rejected' : 'Changes Requested' }} by {{ $doc->reviewer->name ?? 'HOD' }}
                                                                @if($doc->reviewed_at)
                                                                    <span class="fw-normal text-muted ms-1">{{ $doc->reviewed_at->diffForHumans() }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-dark" style="font-size: 0.8rem;">{{ $doc->review_comments }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-folder fs-2 d-block mb-2 text-secondary"></i>
                <p class="mb-0 small">No documents uploaded yet. Click "Upload Document" to get started.</p>
            </div>
        @endif
    </div>
</div>

<!-- Replace Document Modals -->
@if(isset($latestDocuments))
    @foreach($latestDocuments->whereIn('review_status', ['draft', 'changes_requested']) as $doc)
        <div class="modal fade" id="replaceDocModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('faculty.tasks.documents.replace', [$task, $doc]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                            <h6 class="modal-title text-white fw-bold">
                                <i class="bi bi-arrow-repeat me-2"></i>Replace Document
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="bi bi-info-circle me-1"></i>Replacing: <strong>{{ $doc->file_name }}</strong> (v{{ $doc->version }})
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">New File <span class="text-danger">*</span></label>
                                <input type="file" name="document" class="form-control border" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.png,.jpg,.jpeg">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Remarks (optional)</label>
                                <textarea name="remarks" class="form-control border" rows="2" placeholder="What changed in this version?"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm text-white fw-medium" style="background-color: var(--navy);">
                                <i class="bi bi-upload me-1"></i>Upload New Version
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Version History Modal -->
<div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="bi bi-clock-history me-2"></i>Version History
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="versionHistoryContent">
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
         data-bs-target="#facultyActivityLogCollapse" 
         aria-expanded="false" 
         aria-controls="facultyActivityLogCollapse"
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
    <div class="collapse" id="facultyActivityLogCollapse">
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
                        @if(!$loop->last)
                            <div style="position: absolute; left: -14px; top: 20px; bottom: -12px; width: 2px; background: #e9ecef;"></div>
                        @endif
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

@endsection

@section('scripts')
<style>
    /* Custom Range Slider Styling */
    .psg-range-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: linear-gradient(to right, var(--navy) 0%, var(--navy) var(--slider-fill, 0%), #e9ecef var(--slider-fill, 0%), #e9ecef 100%);
        outline: none;
        transition: background 0.15s ease;
    }
    .psg-range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--navy);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(18, 39, 90, 0.3);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .psg-range-slider::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 3px 10px rgba(18, 39, 90, 0.4);
    }
    .psg-range-slider::-moz-range-thumb {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--navy);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(18, 39, 90, 0.3);
    }
</style>
<script>
    const progressRange = document.getElementById('progressRange');
    const progressLabel = document.getElementById('progressLabel');
    const progressPreview = document.getElementById('progressPreview');
    const statusSelect = document.getElementById('statusSelect');

    function updateSlider(val) {
        if (!progressRange) return;
        progressLabel.textContent = val + '%';
        progressPreview.style.width = val + '%';
        progressRange.style.setProperty('--slider-fill', val + '%');

        if (val >= 100) {
            progressPreview.style.backgroundColor = '#2e7d32';
            progressLabel.style.backgroundColor = '#2e7d32';
        } else if (val >= 50) {
            progressPreview.style.backgroundColor = 'var(--navy)';
            progressLabel.style.backgroundColor = 'var(--navy)';
        } else {
            progressPreview.style.backgroundColor = 'var(--gold)';
            progressLabel.style.backgroundColor = 'var(--gold)';
        }

        if (val == 100) {
            statusSelect.value = 'completed';
        } else if (val > 0) {
            statusSelect.value = 'in_progress';
        } else {
            statusSelect.value = 'pending';
        }
    }

    if (progressRange) {
        progressRange.addEventListener('input', function() {
            updateSlider(this.value);
        });
        updateSlider(progressRange.value);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });

    function loadVersionHistory(docId, taskId) {
        const modal = new bootstrap.Modal(document.getElementById('versionHistoryModal'));
        const content = document.getElementById('versionHistoryContent');
        content.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>';
        modal.show();

        fetch(`/faculty/tasks/${taskId}/documents/${docId}/versions`)
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
