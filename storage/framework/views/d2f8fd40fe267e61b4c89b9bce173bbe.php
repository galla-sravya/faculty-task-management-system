<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-award me-2 text-warning"></i>NBA Tasks</h1>
        <p class="text-muted small mb-0">Manage tasks created by or assigned to you for NBA Accreditation</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('nba.tasks.archived')); ?>" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-archive"></i> Archived Tasks
        </a>
        <a href="<?php echo e(route('nba.tasks.create')); ?>" class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create NBA Task
        </a>
    </div>
</div>

<!-- Filters Row -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="<?php echo e(route('nba.tasks.index')); ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                    <option value="pending_review" <?php echo e(request('status') === 'pending_review' ? 'selected' : ''); ?>>Pending Review</option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                    <option value="overdue" <?php echo e(request('status') === 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                    <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>>High</option>
                    <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                    <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>>Low</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-navy px-3 fw-medium">Filter</button>
                <a href="<?php echo e(route('nba.tasks.index')); ?>" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Task Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if($tasks->isEmpty()): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                No NBA tasks matching the criteria.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Deadline</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr style="cursor: pointer;" onclick="window.location='<?php echo e(route('nba.tasks.show', $task)); ?>'">
                            <td class="ps-4">
                                <div class="fw-semibold text-navy"><?php echo e($task->title); ?></div>
                                <div class="text-muted" style="font-size: 0.78rem;">Owner: <?php echo e($task->creator->name ?? 'Self'); ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($task->category ?? 'NBA'); ?></span></td>
                            <td><span class="badge bg-<?php echo e($task->priority_color); ?>-subtle text-<?php echo e($task->priority_color); ?> text-capitalize"><?php echo e($task->priority); ?></span></td>
                            <td><span class="badge bg-<?php echo e($task->status_color); ?>-subtle text-<?php echo e($task->status_color); ?> text-capitalize"><?php echo e(str_replace('_', ' ', $task->status)); ?></span></td>
                            <td style="width: 130px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo e($task->overall_progress); ?>%"></div>
                                    </div>
                                    <span class="text-muted fw-medium" style="font-size:0.75rem;"><?php echo e($task->overall_progress); ?>%</span>
                                </div>
                            </td>
                            <td><span class="<?php echo e($task->is_overdue ? 'text-danger fw-bold' : ''); ?>"><?php echo e($task->deadline->format('M d, Y')); ?></span></td>
                            <td class="pe-4 text-end" onclick="event.stopPropagation();">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?php echo e(route('nba.tasks.show', $task)); ?>" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size: 0.8rem;">View</a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $task)): ?>
                                    <a href="<?php echo e(route('nba.tasks.edit', $task)); ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.8rem;">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                <?php echo e($tasks->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/nba/tasks/index.blade.php ENDPATH**/ ?>