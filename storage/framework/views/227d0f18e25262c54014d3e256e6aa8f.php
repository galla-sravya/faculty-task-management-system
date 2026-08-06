<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Archived Tasks</h1>
        <p class="text-muted small mb-0">View archived department tasks and restore them when needed</p>
    </div>
    <div>
        <a href="<?php echo e(route('hod.tasks.index')); ?>" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Active Tasks
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo e(route('hod.tasks.archived')); ?>" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search archived task name, category, faculty..." value="<?php echo e(request('search')); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                    <option value="pending_review" <?php echo e(request('status') === 'pending_review' ? 'selected' : ''); ?>>Pending Review</option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>>Low</option>
                    <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                    <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>>High</option>
                    <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                </select>
            </div>
            <div class="col-md-1 d-flex justify-content-end">
                <?php if(request()->anyFilled(['search', 'status', 'priority'])): ?>
                    <a href="<?php echo e(route('hod.tasks.archived')); ?>" class="btn btn-sm btn-light border text-muted" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
                <?php else: ?>
                    <button type="submit" class="btn btn-sm btn-psg-primary w-100">Filter</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">Task Name</th>
                        <th class="py-3">Assigned Faculty</th>
                        <th class="py-3">Assigned Date</th>
                        <th class="py-3">Archived Date</th>
                        <th class="py-3">Archived By</th>
                        <th class="py-3">Status Before Archive</th>
                        <th class="pe-3 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $archivedTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusMap = [
                            'pending'        => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                            'in_progress'    => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                            'pending_review' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'label' => 'Pending Review'],
                            'completed'      => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                        ];
                        $st = $statusMap[$task->status] ?? $statusMap['pending'];
                    ?>
                    <tr>
                        <td class="ps-3">
                            <a href="<?php echo e(route('hod.tasks.show', $task->id)); ?>" class="fw-semibold text-navy text-decoration-none">
                                <?php echo e($task->title); ?>

                            </a>
                            <div class="text-muted small">ID: #TSK-<?php echo e($task->id); ?> · <?php echo e($task->category ?? 'General'); ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <?php $__currentLoopData = $task->assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <img src="<?php echo e($assignee->profile_photo_url); ?>" class="rounded-circle shadow-sm" style="width:26px;height:26px;object-fit:cover;border:1px solid var(--navy);" title="<?php echo e($assignee->name); ?>">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <span class="small text-muted ms-1">(<?php echo e($task->assignees->count()); ?>)</span>
                            </div>
                        </td>
                        <td class="text-nowrap small text-muted">
                            <i class="bi bi-calendar-event me-1"></i><?php echo e($task->created_at->format('M d, Y')); ?>

                        </td>
                        <td class="text-nowrap small text-muted">
                            <i class="bi bi-clock-history me-1"></i><?php echo e($task->deleted_at->format('M d, Y g:i A')); ?>

                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                                <i class="bi bi-person-fill me-1 text-secondary"></i><?php echo e($task->creator->name ?? 'HOD'); ?>

                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-2.5 py-1" style="background-color: <?php echo e($st['bg']); ?>; color: <?php echo e($st['text']); ?>; font-weight: 600; font-size: 0.72rem;">
                                <?php echo e($st['label']); ?> (<?php echo e($task->overall_progress); ?>%)
                            </span>
                        </td>
                        <td class="pe-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="<?php echo e(route('hod.tasks.show', $task->id)); ?>" class="btn btn-sm btn-outline-secondary fw-medium px-2 py-1" style="font-size: 0.75rem;" title="View Complete Record">
                                    <i class="bi bi-eye me-1"></i>Details
                                </a>
                                <form action="<?php echo e(route('hod.tasks.restore', $task->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Restore task <?php echo e($task->title); ?> to active status?');">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-medium px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-2 d-block mb-2 text-secondary"></i>
                            No archived tasks found matching your criteria.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($archivedTasks->hasPages()): ?>
    <div class="mt-4">
        <?php echo e($archivedTasks->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/hod/tasks/archived.blade.php ENDPATH**/ ?>