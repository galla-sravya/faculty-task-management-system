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

@if(session('success'))
<div id="successToastPopup" class="position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg rounded-3 p-3 bg-white border border-success d-flex align-items-center gap-3"
     style="z-index: 1090; min-width: 320px; max-width: 520px; box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;">
    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
        <i class="bi bi-check-lg fs-4"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-bold text-dark small">Update Saved</div>
        <div class="text-secondary small" style="font-size: 0.83rem; line-height: 1.35;">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close ms-2 small" onclick="document.getElementById('successToastPopup').remove()"></button>
</div>
<script>
    setTimeout(function() {
        const toast = document.getElementById('successToastPopup');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }
    }, 4000);
</script>
@endif

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Update Task Progress</h1>
        <p class="text-muted small mb-0">Report your progress, add remarks, and manage task collaborators</p>
    </div>
    <div class="d-flex gap-2">
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
                <h4 class="fw-bold mb-2 d-flex align-items-center gap-2" style="color: var(--navy);">
                    {{ $task->title }}
                    @php $myPivot = $task->assignees->where('id', auth()->id())->first()?->pivot; @endphp
                    @if($myPivot && $myPivot->is_reassigned)
                        <span class="badge rounded-pill bg-warning text-dark border border-warning" style="font-size: 0.7rem; padding: 0.3rem 0.6rem;">
                            <i class="bi bi-arrow-repeat me-1"></i>Reassigned
                        </span>
                    @endif
                </h4>
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
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue by {{ $task->days_overdue }} {{ $task->days_overdue === 1 ? 'day' : 'days' }}</span>
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

                    <!-- Interactive Progress Slider -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="progressRange" class="form-label fw-semibold text-dark mb-0">
                                <span>My Progress</span>
                            </label>
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" id="progressValBadge" style="background-color: var(--navy); color: #fff; font-size: 0.95rem;">
                                {{ $pivot->progress_percentage ?? 0 }}%
                            </span>
                        </div>

                        <div class="position-relative pt-4 pb-2 px-1">
                            <!-- Floating Tooltip above slider thumb -->
                            <div id="sliderTooltip" class="position-absolute bg-dark text-white rounded-pill px-2.5 py-1 text-nowrap shadow-sm fw-medium"
                                 style="top: -6px; transform: translateX(-50%); font-size: 0.72rem; transition: left 0.05s ease, opacity 0.15s ease; opacity: 0; pointer-events: none; z-index: 10;">
                                <span id="tooltipText">0% - Not Started</span>
                            </div>

                            <input type="range" class="psg-navy-slider" id="progressRange" min="0" max="100" step="1"
                                   value="{{ $pivot->progress_percentage ?? 0 }}" style="cursor: pointer;">
                        </div>
                    </div>

                    <!-- Read-Only Workflow Stage Display -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span>Workflow Stage</span>
                            <span id="workflowBadge" class="badge rounded-pill px-3 py-1.5 fw-semibold bg-secondary text-white" style="font-size: 0.78rem;">
                                Not Started
                            </span>
                        </label>
                        <input type="hidden" name="progress_percentage" id="progressInput" value="{{ $pivot->progress_percentage ?? 0 }}">
                        <input type="hidden" name="status" id="statusInput" value="{{ $pivot->status ?? 'not_started' }}">
                        
                        <div class="form-control bg-light border-0 py-2 px-3 text-dark d-flex align-items-center justify-content-between" style="font-size: 0.88rem; font-weight: 500;">
                            <span id="workflowStageLabel">Not Started</span>
                        </div>
                    </div>

                    <!-- Remarks / Notes -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Remarks / Notes</label>
                        <textarea name="remarks" id="remarksTextarea" class="form-control border" style="border-color: var(--border) !important;" rows="3" placeholder="Any updates for the HOD?">{{ $pivot->remarks }}</textarea>
                    </div>

                    <!-- Supporting Document Upload / Replacement -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center mb-1">
                            <span>Supporting Document</span>
                            @if(isset($myLatestDoc) && $myLatestDoc)
                                <span class="badge bg-light text-primary border fw-normal" style="font-size:0.72rem;" title="{{ $myLatestDoc->file_name }}">
                                    <i class="bi bi-file-earmark-check me-1"></i>Current: {{ Str::limit($myLatestDoc->file_name, 20) }} (v{{ $myLatestDoc->version }})
                                </span>
                            @endif
                        </label>

                        <div class="input-group">
                            <input type="file" name="document" class="form-control border" id="supportingDocInput"
                                   style="border-color: var(--border) !important; font-size: 0.85rem;"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.png,.jpg,.jpeg">
                            @if(isset($myLatestDoc) && $myLatestDoc)
                                <span class="input-group-text bg-light text-secondary small border" style="border-color: var(--border) !important; font-size: 0.78rem;">
                                    Replace v{{ $myLatestDoc->version }}
                                </span>
                            @endif
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i>{{ isset($myLatestDoc) && $myLatestDoc ? 'Uploading a new file will update the document to v' . ($myLatestDoc->version + 1) . '.' : 'Optional: Upload a supporting document (PDF, DOCX, XLSX, PNG, Max 10MB).' }}
                        </small>
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
<!-- SHARED WORKSPACE DOCUMENTS MANAGEMENT SECTION                  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mt-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: var(--navy);">
                <i class="bi bi-folder2-open" style="color: var(--gold); font-size: 1.2rem;"></i>Shared Workspace Documents
            </h6>
            <span class="text-muted small" style="font-size:0.75rem;">Document repository for all assigned task collaborators</span>
        </div>
        @if(isset($latestDocuments) && $latestDocuments->where('user_id', auth()->id())->where('review_status', 'draft')->count() > 0)
            <form action="{{ route('faculty.tasks.documents.submit', $task) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm fw-medium text-white shadow-sm" style="background-color: var(--navy); font-size: 0.8rem;" onclick="return confirm('Submit your draft documents for HOD review?')">
                    <i class="bi bi-send me-1"></i>Submit My Drafts for Review
                </button>
            </form>
        @endif
    </div>
    <div class="card-body p-4">
        <!-- Documents Repository List -->
        @if(isset($groupedDocuments) && $groupedDocuments->isNotEmpty())
            @foreach($groupedDocuments as $uploaderId => $facultyDocs)
                @php
                    $uploader = $facultyDocs->first()->user ?? \App\Models\User::find($uploaderId);
                    $isSelf = auth()->id() === $uploaderId;
                @endphp
                <div class="card border mb-3 shadow-none rounded-3" style="border-color: var(--border) !important;">
                    <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $uploader->profile_photo_url }}" alt="{{ $uploader->name }}" class="rounded-circle shadow-sm" style="width:26px;height:26px;object-fit:cover;border:1px solid var(--navy);">
                            <div>
                                <span class="fw-bold text-dark small">{{ $uploader->name }}</span>
                                <span class="text-muted small ms-1" style="font-size:0.72rem;">({{ $uploader->designation ?? 'Faculty' }})</span>
                                @if($isSelf)
                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.65rem;">You</span>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border" style="font-size:0.7rem;">{{ $facultyDocs->count() }} Latest File(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white text-uppercase text-secondary" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="ps-3 py-2.5">Document</th>
                                        <th class="py-2.5">Uploaded By</th>
                                        <th class="py-2.5">Upload Date & Time</th>
                                        <th class="py-2.5">Version</th>
                                        <th class="py-2.5">Status</th>
                                        <th class="pe-3 py-2.5 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facultyDocs as $doc)
                                        @php $badge = $doc->review_status_badge; @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi {{ $doc->file_icon }}" style="color: {{ $doc->file_icon_color }}; font-size: 1.25rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 220px; font-size: 0.85rem;" title="{{ $doc->file_name }}">
                                                            {{ $doc->file_name }}
                                                        </div>
                                                        @if($doc->remarks)
                                                            <div class="text-muted text-truncate" style="font-size: 0.7rem; max-width: 220px;" title="{{ $doc->remarks }}">Note: {{ $doc->remarks }}</div>
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
                                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">Version {{ $doc->version }}</span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; font-weight: 600; font-size: 0.7rem;">
                                                    {{ $badge['label'] }}
                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="d-flex align-items-center gap-1 justify-content-end">
                                                    {{-- View document in new tab --}}
                                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary px-2.5 py-1 fw-medium" style="font-size: 0.75rem; border-color: var(--navy); color: var(--navy);" title="View Document in New Tab">
                                                        <i class="bi bi-eye me-1"></i>View
                                                    </a>
                                                    {{-- Version History modal --}}
                                                    <button class="btn btn-sm btn-outline-info px-2 py-1" style="font-size: 0.72rem;" title="Version History" onclick="loadVersionHistory({{ $doc->original_document_id ?? $doc->id }}, {{ $task->id }})">
                                                        <i class="bi bi-clock-history me-1"></i>History
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-folder-x fs-3 d-block mb-1 text-secondary"></i>
                <span class="small">No documents uploaded yet. Faculty can upload supporting documents in the Update Progress section above.</span>
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

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- DISCUSSION & COMMENTS SECTION                                 -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mt-4 mb-4" style="border-radius: var(--radius, 8px);">
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

@if(session('error'))
<div id="errorToastPopup" class="position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg rounded-3 p-3 bg-white border border-danger d-flex align-items-center gap-3"
     style="z-index: 1090; min-width: 320px; max-width: 520px; box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;">
    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-bold text-dark small">Action Failed</div>
        <div class="text-secondary small" style="font-size: 0.83rem; line-height: 1.35;">{{ session('error') }}</div>
    </div>
    <button type="button" class="btn-close ms-2 small" onclick="document.getElementById('errorToastPopup').remove()"></button>
</div>
<script>
    setTimeout(function() {
        const toast = document.getElementById('errorToastPopup');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }
    }, 5000);
</script>
@endif

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TASK SUBTASKS / CHECKLIST SECTION (FOLDED ACCORDION VIEW)     -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="card bg-white shadow-sm border-0 mt-4 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: var(--navy);">
                <i class="bi bi-card-checklist" style="color: var(--gold); font-size: 1.2rem;"></i>Subtasks & Checklist
            </h6>
            <span class="text-muted small" style="font-size:0.75rem;">Shared work division items and HOD requirements</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php $items = $task->checklistItems; @endphp
            <span class="badge bg-light text-dark border px-2.5 py-1.5" style="font-size:0.75rem;">
                <i class="bi bi-list-task me-1 text-primary"></i>{{ $items->count() }} {{ Str::plural('Item', $items->count()) }}
                @if($items->isNotEmpty())
                    · {{ $items->where('status', 'completed')->count() }} Completed
                @endif
            </span>
            @can('manageChecklist', $task)
            <button class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center gap-1" style="background-color: var(--navy); font-size:0.78rem;" data-bs-toggle="collapse" data-bs-target="#addChecklistItemCollapse">
                <i class="bi bi-plus-lg"></i> Add Work Item
            </button>
            @endcan
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Add Subtask / Work Item Collapsible Form -->
        @can('manageChecklist', $task)
        <div class="collapse mb-4" id="addChecklistItemCollapse">
            <div class="card card-body bg-light border-0 shadow-none rounded-3 p-3">
                <h6 class="fw-bold text-dark mb-2" style="font-size:0.88rem;"><i class="bi bi-plus-circle me-1 text-primary"></i>Create New Work Item / Subtask</h6>
                <form action="{{ route('tasks.checklist.store', $task) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Subtask Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm border" placeholder="e.g. Collect syllabus requirements" required style="font-size:0.85rem;">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Assign To</label>
                            <select name="assigned_to" class="form-select form-select-sm border" style="font-size:0.85rem;">
                                <option value="all">Both / All Collaborators (Shared)</option>
                                @foreach($task->assignees as $assignee)
                                    <option value="{{ $assignee->id }}" {{ auth()->id() === $assignee->id ? 'selected' : '' }}>
                                        {{ $assignee->name }} ({{ $assignee->pivot->role === 'owner' ? 'Owner' : 'Collaborator' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Optional Description / Details</label>
                            <textarea name="description" class="form-control form-control-sm border" rows="2" placeholder="Provide extra details or instructions..." style="font-size:0.83rem;"></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addChecklistItemCollapse">Cancel</button>
                            <button type="submit" class="btn btn-sm text-white fw-medium px-3" style="background-color: var(--navy);">
                                <i class="bi bi-check-lg me-1"></i>Save Work Item
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <!-- Folded Work Items Accordion List -->
        <div class="d-flex flex-column gap-2" id="checklistAccordionFaculty">
            @forelse($items as $item)
                @php
                    $isHodReq = $item->isHodRequirement();
                    $badgeDetails = $item->status_badge;
                @endphp
                <div class="card border rounded-3 shadow-none overflow-hidden" style="border-color: var(--border) !important;">
                    <!-- Collapsed Header Row (Clickable) -->
                    <div class="card-header bg-white py-2.5 px-3 border-0 d-flex align-items-center justify-content-between cursor-pointer checklist-collapse-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#checklistItemCollapse{{ $item->id }}"
                         aria-expanded="false"
                         aria-controls="checklistItemCollapse{{ $item->id }}"
                         style="cursor: pointer; border-left: 4px solid {{ $isHodReq ? 'var(--gold, #d4a017)' : 'var(--navy, #12275a)' }} !important;">
                        
                        <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden me-2">
                            <i class="bi bi-chevron-right collapse-chevron text-muted flex-shrink-0" style="transition: transform 0.2s ease; font-size: 0.8rem;"></i>
                            <span class="fw-semibold text-dark text-truncate {{ $item->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}" style="font-size: 0.88rem;">
                                {{ $item->title }}
                            </span>
                        </div>

                        <!-- Header Right Metadata -->
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if($isHodReq)
                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.65rem;" title="Mandatory requirement defined by HOD">
                                    HOD Requirement
                                </span>
                            @else
                                <span class="badge bg-light text-primary border" style="font-size: 0.65rem;">
                                    Work Item
                                </span>
                            @endif

                            <span class="text-muted small d-none d-md-inline" style="font-size: 0.72rem;">
                                Assigned to {{ $item->assignee ? $item->assignee->name : 'Both' }}
                            </span>

                            <span class="badge rounded-pill px-2 py-0.8" style="background-color: {{ $badgeDetails['bg'] }}; color: {{ $badgeDetails['text'] }}; font-weight: 600; font-size: 0.68rem;">
                                {{ $badgeDetails['label'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Expanded Content Area -->
                    <div class="collapse" id="checklistItemCollapse{{ $item->id }}">
                        <div class="card-body p-3 bg-light border-top" style="font-size: 0.83rem;">
                            @if($item->description)
                                <div class="mb-3 p-2 bg-white rounded border text-secondary" style="font-size: 0.82rem; line-height: 1.45;">
                                    <strong class="text-dark d-block mb-0.5">Description:</strong>
                                    {{ $item->description }}
                                </div>
                            @endif

                            <div class="row g-2 mb-3 text-muted">
                                <div class="col-sm-6">
                                    <i class="bi bi-person-circle me-1 text-primary"></i><strong>Created by:</strong> {{ $item->creator->name ?? 'System' }} {{ $isHodReq ? '(HOD)' : '' }}
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-calendar-event me-1 text-secondary"></i><strong>Created at:</strong> {{ $item->created_at->format('M d, Y g:i A') }}
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-person-check me-1 text-success"></i><strong>Assigned to:</strong> {{ $item->assignee->name ?? 'Both / All Collaborators' }}
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-clock-history me-1 text-info"></i><strong>Status:</strong> {{ $badgeDetails['label'] }}
                                </div>
                                @if($item->status === 'completed' && $item->completedByUser)
                                    <div class="col-12 text-success">
                                        <i class="bi bi-check-all me-1"></i><strong>Completed by:</strong> {{ $item->completedByUser->name }} · {{ $item->completed_at ? $item->completed_at->format('M d, Y g:i A') : '' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Expanded Action Controls -->
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                                    @can('updateChecklistItemStatus', [$task, $item])
                                        <form action="{{ route('tasks.checklist.updateStatus', [$task, $item]) }}" method="POST" class="d-inline" onclick="event.stopPropagation();">
                                            @csrf
                                            @method('PATCH')
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="fw-semibold text-dark mb-0 small">Status:</label>
                                                <select name="status" class="form-select form-select-sm border fw-semibold" onchange="this.form.submit()" style="font-size: 0.75rem; width: auto; background-color: #fff;">
                                                    <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ $item->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="completed" {{ $item->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                            </div>
                                        </form>
                                    @endcan
                                </div>

                                <div onclick="event.stopPropagation();">
                                    @can('deleteChecklistItem', [$task, $item])
                                        <form action="{{ route('tasks.checklist.destroy', [$task, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this work item?');" onclick="event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 py-1 px-2.5" style="font-size: 0.75rem;" onclick="event.stopPropagation();">
                                                <i class="bi bi-trash"></i> Delete Work Item
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-card-checklist fs-3 d-block mb-1 text-secondary"></i>
                    <span class="small">No subtasks or work items added yet. Collaborators or HOD can add work items above.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .checklist-collapse-header[aria-expanded="true"] .collapse-chevron {
        transform: rotate(90deg) !important;
    }
</style>

<script>
function confirmDeleteWorkItem(event, title) {
    if (event) {
        event.stopPropagation();
    }
    const confirmed = confirm('Delete work item "' + title + '" permanently from the database?');
    if (!confirmed && event) {
        event.preventDefault();
    }
    return confirmed;
}
</script>

<x-task-timeline :task="$task" />

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<!-- ACTIVITY LOG & AUDIT HISTORY (Side-by-Side Collapsible)        -->
<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<div class="row g-4 mt-4">
<div class="col-lg-6">
<!-- Activity Log Timeline (Collapsible) -->
@if($task->activities->count() > 0)
<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
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
</div>
<div class="col-lg-6">
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
</div>
</div>

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
                <input type="hidden" name="old_assignee_id" value="{{ auth()->id() }}">
                <div class="modal-header border-bottom py-3" style="background: linear-gradient(135deg, var(--navy) 0%, #1a3a7a 100%);">
                    <h5 class="modal-title text-white fw-bold" id="reassignTaskModalLabel">
                        <i class="bi bi-arrow-repeat me-2"></i>Reassign My Task
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning mt-1"></i>
                        <span>This will <strong>completely transfer</strong> your assignment to another faculty member. You will be removed from this task and the new person will take over.</span>
                    </div>

                    <!-- Current Assignee Info (read-only) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Reassigning From</label>
                        <div class="form-control bg-light" style="pointer-events: none;">
                            <i class="bi bi-person me-1"></i>{{ auth()->user()->name }} (You)
                        </div>
                    </div>

                    <!-- Select New Assignee -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Transfer To <span class="text-danger">*</span></label>
                        <select name="new_assignee_id" class="form-select" required>
                            <option value="">— Select new assignee —</option>
                            @php
                                $availableForReassign = \App\Models\User::where('department_id', $task->department_id)
                                    ->where('role', 'faculty')
                                    ->where('id', '!=', auth()->id())
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
                        <textarea name="reason" class="form-control" rows="3" placeholder="Why are you transferring this task?"></textarea>
                    </div>

                    <!-- HOD Notification Note -->
                    <div class="alert alert-info py-2 px-3 mb-2 small d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-info"></i>
                        <span>The HOD will be automatically notified about this reassignment.</span>
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
<style>
    /* Modern Range Slider – thin track, smooth circular thumb */
    .psg-navy-slider {
        -webkit-appearance: none !important;
        appearance: none !important;
        width: 100%;
        height: 6px !important;
        border-radius: 3px;
        background: #e9ecef;
        outline: none;
        padding: 0 !important;
        transition: background 0.1s ease;
    }
    .psg-navy-slider::-webkit-slider-runnable-track {
        height: 6px;
        border-radius: 3px;
        background: transparent;
    }
    .psg-navy-slider::-webkit-slider-thumb {
        -webkit-appearance: none !important;
        appearance: none !important;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--navy, #12275a);
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.22);
        margin-top: -6px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .psg-navy-slider::-webkit-slider-thumb:hover {
        transform: scale(1.18);
        box-shadow: 0 2px 8px rgba(18,39,90,0.35);
    }
    .psg-navy-slider::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--navy, #12275a);
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.22);
    }
    .psg-navy-slider::-moz-range-track {
        height: 6px;
        border-radius: 3px;
        background: transparent;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const progressRange = document.getElementById('progressRange');
    const progressValBadge = document.getElementById('progressValBadge');
    const sliderTooltip = document.getElementById('sliderTooltip');
    const tooltipText = document.getElementById('tooltipText');

    const workflowBadge = document.getElementById('workflowBadge');
    const workflowStageLabel = document.getElementById('workflowStageLabel');
    const progressInput = document.getElementById('progressInput');
    const statusInput = document.getElementById('statusInput');

    const stageMap = [
        { min: 0, max: 0, stage: 'Not Started', statusKey: 'not_started', badgeClass: 'bg-secondary text-white', desc: 'Task has not been started.' },
        { min: 1, max: 20, stage: 'Collecting Resources', statusKey: 'collecting_resources', badgeClass: 'bg-info text-dark', desc: 'Faculty is collecting resources and preparing work.' },
        { min: 21, max: 40, stage: 'Working on Task', statusKey: 'working_on_task', badgeClass: 'bg-primary text-white', desc: 'Faculty is actively working on the assigned task.' },
        { min: 41, max: 60, stage: 'Supporting Documents Uploaded', statusKey: 'documents_uploaded', badgeClass: 'bg-purple text-white', desc: 'Supporting documents have been uploaded.' },
        { min: 61, max: 80, stage: 'Checklist Completed', statusKey: 'checklist_completed', badgeClass: 'bg-warning text-dark', desc: 'Checklist and required activities have been completed.' },
        { min: 81, max: 99, stage: 'Submitted for Review', statusKey: 'submitted_for_review', badgeClass: 'bg-warning text-dark fw-bold', desc: 'Task has been submitted for HOD review.' },
        { min: 100, max: 100, stage: 'Completed', statusKey: 'completed', badgeClass: 'bg-success text-white fw-bold', desc: 'Task has been completed successfully.' }
    ];

    function getStageInfo(val) {
        val = parseInt(val, 10) || 0;
        for (const item of stageMap) {
            if (val >= item.min && val <= item.max) return item;
        }
        return stageMap[0];
    }

    function updateSliderUI(val) {
        val = parseInt(val, 10) || 0;
        const info = getStageInfo(val);

        if (progressValBadge) progressValBadge.textContent = val + '%';
        if (progressInput) progressInput.value = val;
        if (statusInput) statusInput.value = info.statusKey;

        if (workflowStageLabel) workflowStageLabel.textContent = info.stage;

        if (workflowBadge) {
            workflowBadge.className = 'badge rounded-pill px-3 py-1.5 fw-semibold ' + info.badgeClass;
            workflowBadge.textContent = info.stage;
        }

        // Dynamic Navy Track Filling from 0% up to current position
        if (progressRange) {
            const navyColor = getComputedStyle(document.documentElement).getPropertyValue('--navy').trim() || '#12275a';
            progressRange.style.background = `linear-gradient(to right, ${navyColor} 0%, ${navyColor} ${val}%, #e9ecef ${val}%, #e9ecef 100%)`;
        }

        // Floating Tooltip Positioning
        if (sliderTooltip && progressRange) {
            const min = parseInt(progressRange.min || 0, 10);
            const max = parseInt(progressRange.max || 100, 10);
            const pct = (val - min) / (max - min);
            
            sliderTooltip.style.left = `calc(${pct * 100}% + (${8 - pct * 16}px))`;
            if (tooltipText) tooltipText.textContent = `${val}% - ${info.stage}`;
        }
    }

    if (progressRange) {
        updateSliderUI(progressRange.value);

        progressRange.addEventListener('input', function () {
            updateSliderUI(this.value);
            if (sliderTooltip) sliderTooltip.style.opacity = '1';
        });

        progressRange.addEventListener('mousedown', function () {
            if (sliderTooltip) sliderTooltip.style.opacity = '1';
        });

        progressRange.addEventListener('mouseup', function () {
            if (sliderTooltip) sliderTooltip.style.opacity = '0';
        });

        progressRange.addEventListener('mouseleave', function () {
            if (sliderTooltip) sliderTooltip.style.opacity = '0';
        });

        progressRange.addEventListener('touchstart', function () {
            if (sliderTooltip) sliderTooltip.style.opacity = '1';
        });

        progressRange.addEventListener('touchend', function () {
            if (sliderTooltip) sliderTooltip.style.opacity = '0';
        });
    }

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
