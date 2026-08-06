<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-archive me-2 text-warning"></i>Archived NBA Tasks</h1>
        <p class="text-muted small mb-0">View and restore soft-deleted NBA Accreditation tasks</p>
    </div>
    <div>
        <a href="<?php echo e(route('nba.tasks.index')); ?>" class="btn btn-sm btn-outline-secondary fw-medium">&larr; Back to NBA Tasks</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if($archivedTasks->isEmpty()): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                No archived NBA tasks found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Archived At</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $archivedTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-navy"><?php echo e($task->title); ?></div>
                                <div class="text-muted" style="font-size: 0.78rem;">Created: <?php echo e($task->created_at->format('M d, Y')); ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($task->category ?? 'NBA'); ?></span></td>
                            <td><span class="badge bg-<?php echo e($task->priority_color); ?>-subtle text-<?php echo e($task->priority_color); ?>"><?php echo e(ucfirst($task->priority)); ?></span></td>
                            <td class="text-muted"><?php echo e($task->deleted_at->format('M d, Y H:i')); ?></td>
                            <td class="pe-4 text-end">
                                <form action="<?php echo e(route('nba.tasks.restore', $task->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-success fw-medium py-1 px-2" style="font-size:0.78rem;">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                <?php echo e($archivedTasks->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/nba/tasks/archived.blade.php ENDPATH**/ ?>