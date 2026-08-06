<?php $__env->startSection('content'); ?>
<?php
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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Update Task Progress</h1>
        <p class="text-muted small mb-0">Report your progress, add remarks, and manage task collaborators</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('addCollaborator', $task)): ?>
        <button class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal">
            <i class="bi bi-person-plus"></i> Reassign / Add Collaborator
        </button>
        <?php endif; ?>
        <a href="<?php echo e(route('faculty.tasks.index')); ?>" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to Tasks
        </a>
    </div>
</div>

<!-- Assigned Faculty Photo Avatars Row -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small fw-semibold me-2"><i class="bi bi-people-fill me-1"></i>Assigned Faculty (<?php echo e($task->assignees->count()); ?>):</span>
                <?php $__currentLoopData = $task->assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $aRole = $assignee->pivot->role ?? 'owner';
                        $r = $roleMap[$aRole] ?? $roleMap['collaborator'];
                    ?>
                    <div class="position-relative d-inline-block" data-bs-toggle="tooltip" data-bs-html="true" title="<strong><?php echo e($assignee->name); ?></strong><br><?php echo e($assignee->designation ?? 'Faculty'); ?><br><em><?php echo e($r['label']); ?></em>">
                        <img src="<?php echo e($assignee->profile_photo_url); ?>" alt="<?php echo e($assignee->name); ?>" class="rounded-circle shadow-sm object-fit-cover" style="width: 40px; height: 40px; border: 2px solid <?php echo e($r['text']); ?>; cursor: pointer;">
                        <span class="position-absolute bottom-0 end-0 badge rounded-pill" style="font-size: 0.5rem; background-color: <?php echo e($r['bg']); ?>; color: <?php echo e($r['text']); ?>; border: 1px solid <?php echo e($r['text']); ?>; padding: 1px 4px; transform: translateY(2px);">
                            <?php echo e($aRole === 'owner' ? 'O' : ($aRole === 'secondary_owner' ? 'SO' : 'C')); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('addCollaborator', $task)): ?>
            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCollaboratorModal" style="border-color: var(--navy); color: var(--navy);">
                <i class="bi bi-plus-lg"></i> Add
            </button>
            <?php endif; ?>
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
                <h4 class="fw-bold mb-2" style="color: var(--navy);"><?php echo e($task->title); ?></h4>
                <p class="text-muted mb-0"><?php echo e($task->description ?? 'No description.'); ?></p>

                <div class="mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
                    <table class="table table-borderless mb-0" style="font-size: 0.9rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2" style="width: 130px;">Assigned By</td>
                                <td class="py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e($task->creator->profile_photo_url); ?>" alt="<?php echo e($task->creator->name); ?>"
                                             class="rounded-circle shadow-sm object-fit-cover"
                                             style="width: 28px; height: 28px; border: 1px solid var(--navy); flex-shrink: 0;"
                                             title="<?php echo e($task->creator->name); ?>">
                                        <span class="fw-semibold text-dark"><?php echo e($task->creator->name); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Priority</td>
                                <td class="py-2">
                                    <span class="badge rounded-pill px-3 py-1" style="background-color: <?php echo e($p['bg']); ?>; color: <?php echo e($p['text']); ?>; font-weight: 600; font-size: 0.75rem;">
                                        <?php echo e(ucfirst($task->priority)); ?>

                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Assigned Date</td>
                                <td class="py-2">
                                    <span class="fw-semibold text-dark">
                                        <i class="bi bi-calendar-event me-1 text-secondary"></i><?php echo e($task->created_at->format('M d, Y h:i A')); ?>

                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium ps-0 py-2">Deadline</td>
                                <td class="py-2">
                                    <span class="fw-semibold <?php echo e($task->is_overdue ? 'text-danger' : ''); ?>" style="<?php echo e(!$task->is_overdue ? 'color: var(--navy);' : ''); ?>">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo e($task->deadline->format('M d, Y h:i A')); ?>

                                        <?php if($task->is_overdue): ?>
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="badge bg-light text-muted border ms-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-hourglass-split me-1 text-primary"></i><?php echo e($task->duration_in_days); ?> Days Duration
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
                <form action="<?php echo e(route('faculty.tasks.updateProgress', $task)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <!-- Automated Read-Only Progress Display -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center mb-1">
                            <span>Current Progress <span class="badge bg-light text-muted border fw-normal ms-1" style="font-size:0.72rem;">Calculated Automatically</span></span>
                            <span class="badge rounded-pill px-3 py-2" style="background-color: var(--navy); color: #fff; font-size: 0.9rem;"><?php echo e($task->overall_progress); ?>%</span>
                        </label>

                        <div class="progress mt-2" style="height: 14px; background-color: #e9ecef; border-radius: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: <?php echo e($task->overall_progress); ?>%; background-color: var(--navy); border-radius: 6px; transition: width 0.3s ease;">
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
                            <?php $stBadge = $task->status_badge_details; ?>
                            <span class="badge rounded-pill px-3 py-1.5 fw-semibold <?php echo e($stBadge['class'] ?? ''); ?>" style="<?php echo e($stBadge['style'] ?? ''); ?> font-size: 0.78rem;">
                                <?php echo e($stBadge['label']); ?>

                            </span>
                        </label>
                        <select name="status" id="statusSelect" class="form-select border rounded-3 py-2 px-3" style="border-color: var(--border) !important; font-size: 0.88rem; font-weight: 500;">
                            <option value="not_started" <?php echo e(in_array($pivot->status, ['not_started', 'pending']) ? 'selected' : ''); ?>>Not Started</option>
                            <option value="collecting_resources" <?php echo e($pivot->status === 'collecting_resources' ? 'selected' : ''); ?>>Collecting Resources</option>
                            <option value="working_on_task" <?php echo e(in_array($pivot->status, ['working_on_task', 'in_progress']) ? 'selected' : ''); ?>>Working on Task</option>
                            <option value="documents_uploaded" <?php echo e($pivot->status === 'documents_uploaded' ? 'selected' : ''); ?>>Supporting Documents Uploaded</option>
                            <option value="checklist_completed" <?php echo e($pivot->status === 'checklist_completed' ? 'selected' : ''); ?>>Checklist Completed</option>
                            <option value="submitted_for_review" <?php echo e(in_array($pivot->status, ['submitted_for_review', 'pending_review']) ? 'selected' : ''); ?>>Submitted for Review</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control border" style="border-color: var(--border) !important;" rows="3" placeholder="Any updates for the HOD?"><?php echo e($pivot->remarks); ?></textarea>
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
        <?php $items = $task->checklistItems; ?>
        <?php if($items->isNotEmpty()): ?>
            <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                <?php echo e($items->where('is_completed', true)->count()); ?> / <?php echo e($items->count()); ?> Completed
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <!-- Checklist Items List -->
        <div class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item px-2 py-2.5 border-bottom d-flex align-items-center justify-content-between">
                    <form action="<?php echo e(route('tasks.checklist.toggle', [$task, $item])); ?>" method="POST" class="d-flex align-items-center gap-2 flex-grow-1">
                        <?php echo csrf_field(); ?>
                        <input type="checkbox" class="form-check-input mt-0" onchange="this.form.submit()" <?php echo e($item->is_completed ? 'checked' : ''); ?> style="cursor:pointer;width:18px;height:18px;">
                        <span class="<?php echo e($item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark fw-medium'); ?>" style="font-size:0.88rem;">
                            <?php echo e($item->title); ?>

                        </span>
                        <?php if($item->is_completed && $item->completedByUser): ?>
                            <span class="badge bg-light text-muted border ms-2" style="font-size:0.65rem;">Done by <?php echo e($item->completedByUser->name); ?></span>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-3 text-muted small">No subtasks assigned yet.</div>
            <?php endif; ?>
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
        <form action="<?php echo e(route('tasks.comments.store', $task)); ?>" method="POST" enctype="multipart/form-data" class="mb-4">
            <?php echo csrf_field(); ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $task->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item px-0 py-3 border-bottom">
                    <div class="d-flex align-items-start gap-2.5">
                        <img src="<?php echo e($comment->user->profile_photo_url); ?>" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;border:1px solid var(--navy);">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark" style="font-size:0.85rem;"><?php echo e($comment->user->name); ?></span>
                                <span class="text-muted small" style="font-size:0.72rem;"><?php echo e($comment->created_at->diffForHumans()); ?></span>
                            </div>
                            <div class="text-dark" style="font-size:0.85rem;"><?php echo e($comment->comment); ?></div>
                            <?php if($comment->attachment_path): ?>
                                <div class="mt-2">
                                    <a href="<?php echo e(Storage::url($comment->attachment_path)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="font-size:0.75rem;">
                                        <i class="bi bi-paperclip text-primary"></i> <?php echo e($comment->attachment_name ?? 'Attachment'); ?>

                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-3 text-muted small">No comments posted yet.</div>
            <?php endif; ?>
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
                <span class="text-muted fw-normal" style="font-size: 0.85rem;">(<?php echo e($task->auditLogs->count()); ?> <?php echo e(Str::plural('Change', $task->auditLogs->count())); ?>)</span>
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
                        <?php $__empty_1 = true; $__currentLoopData = $task->auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3 text-nowrap small text-muted"><?php echo e($log->created_at->format('M d, Y g:i A')); ?></td>
                            <td class="fw-semibold text-dark" style="font-size:0.82rem;"><?php echo e($log->user->name ?? 'System'); ?></td>
                            <td><span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?php echo e($log->formatted_field_name); ?></span></td>
                            <td class="small text-muted" style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($log->old_value ?? '—'); ?></td>
                            <td class="pe-3 small text-dark fw-medium" style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($log->new_value ?? '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center py-3 text-muted small">No audit modifications recorded yet.</td></tr>
                        <?php endif; ?>
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
            <?php if(isset($latestDocuments) && $latestDocuments->where('user_id', auth()->id())->where('review_status', 'draft')->count() > 0): ?>
                <form action="<?php echo e(route('faculty.tasks.documents.submit', $task)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-sm fw-medium text-white shadow-sm" style="background-color: var(--navy); font-size: 0.8rem;" onclick="return confirm('Submit your draft documents for HOD review?')">
                        <i class="bi bi-send me-1"></i>Submit My Drafts for Review
                    </button>
                </form>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-primary fw-medium" data-bs-toggle="collapse" data-bs-target="#uploadSection" style="border-color: var(--navy); color: var(--navy); font-size: 0.8rem;">
                <i class="bi bi-cloud-upload me-1"></i>Upload Document
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Upload Form (Collapsible) -->
        <div class="collapse mb-4" id="uploadSection">
            <div class="bg-light rounded p-3 border" style="border-color: var(--border) !important;">
                <form action="<?php echo e(route('faculty.tasks.documents.store', $task)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
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
        <?php if(isset($groupedDocuments) && $groupedDocuments->isNotEmpty()): ?>
            <?php $__currentLoopData = $groupedDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uploaderId => $facultyDocs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $uploader = $facultyDocs->first()->user ?? \App\Models\User::find($uploaderId);
                    $isSelf = auth()->id() === $uploaderId;
                ?>
                <div class="card border mb-4 shadow-none rounded-3" style="border-color: var(--border) !important;">
                    <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?php echo e($uploader->profile_photo_url); ?>" alt="<?php echo e($uploader->name); ?>" class="rounded-circle shadow-sm" style="width:28px;height:28px;object-fit:cover;border:1px solid var(--navy);">
                            <div>
                                <span class="fw-bold text-dark small"><?php echo e($uploader->name); ?></span>
                                <span class="text-muted small ms-1" style="font-size:0.72rem;">(<?php echo e($uploader->designation ?? 'Faculty'); ?>)</span>
                                <?php if($isSelf): ?>
                                    <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.65rem;">You</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?php echo e($facultyDocs->count()); ?> Document(s)</span>
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
                                    <?php $__currentLoopData = $facultyDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $badge = $doc->review_status_badge; ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi <?php echo e($doc->file_icon); ?>" style="color: <?php echo e($doc->file_icon_color); ?>; font-size: 1.2rem;"></i>
                                                    <div>
                                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 200px; font-size: 0.85rem;" title="<?php echo e($doc->file_name); ?>"><?php echo e($doc->file_name); ?></div>
                                                        <?php if($doc->remarks): ?>
                                                            <div class="text-muted text-truncate" style="font-size: 0.7rem; max-width: 200px;" title="<?php echo e($doc->remarks); ?>">Note: <?php echo e($doc->remarks); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small text-dark">
                                                <span class="fw-medium"><?php echo e($doc->user->name ?? 'Faculty'); ?></span>
                                            </td>
                                            <td class="text-nowrap" style="font-size: 0.78rem;">
                                                <span class="text-dark d-block fw-medium"><?php echo e($doc->created_at->format('M d, Y')); ?></span>
                                                <span class="text-muted" style="font-size:0.7rem;"><?php echo e($doc->created_at->format('g:i A')); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">v<?php echo e($doc->version); ?></span>
                                            </td>
                                            <td class="small text-muted" style="font-size: 0.78rem;">
                                                <?php echo e($doc->formatted_file_size); ?>

                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-2 py-1" style="background-color: <?php echo e($badge['bg']); ?>; color: <?php echo e($badge['text']); ?>; font-weight: 600; font-size: 0.7rem;">
                                                    <?php echo e($badge['label']); ?>

                                                </span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="d-flex align-items-center gap-1 justify-content-end">
                                                    
                                                    <?php if($doc->is_previewable): ?>
                                                        <a href="<?php echo e(Storage::url($doc->file_path)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 0.7rem;" title="Preview Document">
                                                            <i class="bi bi-eye"></i> Preview
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="<?php echo e(route('faculty.tasks.documents.download', [$task, $doc])); ?>" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size: 0.7rem; border-color: var(--navy); color: var(--navy);" title="Download Document">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                    
                                                    <button class="btn btn-sm btn-outline-info px-2 py-1" style="font-size: 0.7rem;" title="Version History" onclick="loadVersionHistory(<?php echo e($doc->original_document_id ?? $doc->id); ?>, <?php echo e($task->id); ?>)">
                                                        <i class="bi bi-clock-history"></i> History
                                                    </button>
                                                    
                                                    <?php if($isSelf && in_array($doc->review_status, ['draft', 'changes_requested'])): ?>
                                                        <button class="btn btn-sm btn-outline-warning px-2 py-1" style="font-size: 0.7rem;" title="Replace Document" data-bs-toggle="modal" data-bs-target="#replaceDocModal<?php echo e($doc->id); ?>">
                                                            <i class="bi bi-arrow-repeat"></i> Replace
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <?php if($doc->review_comments && in_array($doc->review_status, ['changes_requested', 'rejected'])): ?>
                                            <tr>
                                                <td colspan="7" class="ps-3 pe-3 py-2" style="background-color: <?php echo e($doc->review_status === 'rejected' ? '#fce4ec' : '#fff8e1'); ?>;">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-chat-left-text <?php echo e($doc->review_status === 'rejected' ? 'text-danger' : 'text-warning'); ?>" style="font-size: 0.85rem; margin-top: 2px;"></i>
                                                        <div>
                                                            <div class="fw-semibold small" style="font-size: 0.78rem; color: <?php echo e($doc->review_status === 'rejected' ? '#c62828' : '#f57f17'); ?>;">
                                                                <?php echo e($doc->review_status === 'rejected' ? 'Rejected' : 'Changes Requested'); ?> by <?php echo e($doc->reviewer->name ?? 'HOD'); ?>

                                                                <?php if($doc->reviewed_at): ?>
                                                                    <span class="fw-normal text-muted ms-1"><?php echo e($doc->reviewed_at->diffForHumans()); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-dark" style="font-size: 0.8rem;"><?php echo e($doc->review_comments); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-folder fs-2 d-block mb-2 text-secondary"></i>
                <p class="mb-0 small">No documents uploaded yet. Click "Upload Document" to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Replace Document Modals -->
<?php if(isset($latestDocuments)): ?>
    <?php $__currentLoopData = $latestDocuments->whereIn('review_status', ['draft', 'changes_requested']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="replaceDocModal<?php echo e($doc->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="<?php echo e(route('faculty.tasks.documents.replace', [$task, $doc])); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header border-bottom py-3" style="background: var(--navy);">
                            <h6 class="modal-title text-white fw-bold">
                                <i class="bi bi-arrow-repeat me-2"></i>Replace Document
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="bi bi-info-circle me-1"></i>Replacing: <strong><?php echo e($doc->file_name); ?></strong> (v<?php echo e($doc->version); ?>)
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

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

<?php if (isset($component)) { $__componentOriginal6243a699ec44fb6eae713055e6a13a94 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6243a699ec44fb6eae713055e6a13a94 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.task-timeline','data' => ['task' => $task]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('task-timeline'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['task' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($task)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6243a699ec44fb6eae713055e6a13a94)): ?>
<?php $attributes = $__attributesOriginal6243a699ec44fb6eae713055e6a13a94; ?>
<?php unset($__attributesOriginal6243a699ec44fb6eae713055e6a13a94); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6243a699ec44fb6eae713055e6a13a94)): ?>
<?php $component = $__componentOriginal6243a699ec44fb6eae713055e6a13a94; ?>
<?php unset($__componentOriginal6243a699ec44fb6eae713055e6a13a94); ?>
<?php endif; ?>

<!-- Activity Log Timeline (Collapsible) -->
<?php if($task->activities->count() > 0): ?>
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
                <span class="text-muted fw-normal" style="font-size: 0.85rem;">(<?php echo e($task->activities->count()); ?> <?php echo e(Str::plural('Event', $task->activities->count())); ?>)</span>
            </h6>
            <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">Click to expand</span>
        </div>
    </div>
    <div class="collapse" id="facultyActivityLogCollapse">
        <div class="card-body p-4">
            <div class="position-relative" style="padding-left: 24px;">
                <?php $__currentLoopData = $task->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>
                    <div class="d-flex align-items-start mb-3 position-relative">
                        <?php if(!$loop->last): ?>
                            <div style="position: absolute; left: -14px; top: 20px; bottom: -12px; width: 2px; background: #e9ecef;"></div>
                        <?php endif; ?>
                        <div style="position: absolute; left: -20px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #fff; border: 2px solid <?php echo e($ai['color']); ?>; display: flex; align-items: center; justify-content: center; z-index: 2;">
                            <i class="bi <?php echo e($ai['icon']); ?>" style="font-size: 0.5rem; color: <?php echo e($ai['color']); ?>;"></i>
                        </div>
                        <div class="ms-2">
                            <div class="fw-medium text-dark" style="font-size: 0.85rem;"><?php echo e($activity->description); ?></div>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-clock me-1"></i><?php echo e($activity->created_at->diffForHumans()); ?> · <?php echo e($activity->created_at->format('M d, Y g:i A')); ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add Collaborator Modal -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('addCollaborator', $task)): ?>
<div class="modal fade" id="addCollaboratorModal" tabindex="-1" aria-labelledby="addCollaboratorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?php echo e(route('tasks.collaborators.store', $task)); ?>">
                <?php echo csrf_field(); ?>
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
                            <?php
                                $existingIds = $task->assignees->pluck('id')->toArray();
                                $availableFaculty = \App\Models\User::where('department_id', $task->department_id)
                                    ->where('role', 'faculty')
                                    ->whereNotIn('id', $existingIds)
                                    ->get();
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $availableFaculty; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <option value="<?php echo e($f->id); ?>"><?php echo e($f->name); ?> — <?php echo e($f->designation ?? 'Faculty'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <option disabled>No available faculty to add</option>
                            <?php endif; ?>
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
                    <?php if(!auth()->user()->isHod()): ?>
                    <div class="alert alert-info py-2 px-3 mb-2 small d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-info"></i>
                        <span>The task HOD will be automatically notified about this collaborator assignment.</span>
                    </div>
                    <?php endif; ?>
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
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/faculty/tasks/show.blade.php ENDPATH**/ ?>