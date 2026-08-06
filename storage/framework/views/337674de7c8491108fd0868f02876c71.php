<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Assigned Tasks</h1>
        <p class="text-muted small mb-0">Track and update your assigned tasks</p>
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
                        <th class="py-3">My Status</th>
                        <th class="py-3">My Progress</th>
                        <th class="py-3">Assigned Date</th>
                        <th class="py-3">Deadline</th>
                        <th class="pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $myPivot = $task->pivot; ?>
                    <tr>
                        <td class="ps-4">
                            <a href="<?php echo e(route('faculty.tasks.show', $task)); ?>" class="text-decoration-none fw-semibold" style="color: var(--navy);">
                                <?php echo e($task->title); ?>

                            </a>
                        </td>
                        <td>
                            <?php
                                $priorityMap = [
                                    'low'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Low'],
                                    'medium' => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Medium'],
                                    'high'   => ['bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'High'],
                                    'urgent' => ['bg' => '#fce4ec', 'text' => '#c62828', 'label' => 'Urgent'],
                                ];
                                $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
                            ?>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: <?php echo e($p['bg']); ?>; color: <?php echo e($p['text']); ?>; font-weight: 600; font-size: 0.75rem;">
                                <?php echo e($p['label']); ?>

                            </span>
                        </td>
                        <td>
                            <?php
                                $statusMap = [
                                    'pending'     => ['bg' => '#fff8e1', 'text' => '#f57f17', 'label' => 'Pending'],
                                    'in_progress' => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'In Progress'],
                                    'completed'   => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => 'Completed'],
                                ];
                                $ms = $statusMap[$myPivot->status] ?? $statusMap['pending'];
                            ?>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: <?php echo e($ms['bg']); ?>; color: <?php echo e($ms['text']); ?>; font-weight: 600; font-size: 0.75rem;">
                                <?php echo e($ms['label']); ?>

                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; min-width: 80px; background-color: #e9ecef; border-radius: 4px;">
                                    <div class="progress-bar" style="width: <?php echo e($myPivot->progress_percentage); ?>%; background-color: <?php echo e($myPivot->progress_percentage >= 100 ? '#2e7d32' : ($myPivot->progress_percentage >= 50 ? 'var(--navy)' : 'var(--gold)')); ?>; border-radius: 4px;"></div>
                                </div>
                                <span class="fw-semibold small text-dark" style="min-width: 35px;"><?php echo e($myPivot->progress_percentage); ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar-event me-1"></i><?php echo e($task->created_at->format('M d, Y')); ?>

                            </span>
                        </td>
                        <td>
                            <span class="<?php echo e($task->is_overdue ? 'text-danger fw-semibold' : 'text-muted'); ?>" style="font-size: 0.85rem;">
                                <i class="bi bi-calendar3 me-1"></i><?php echo e($task->deadline->format('M d, Y')); ?>

                                <?php if($task->is_overdue): ?>
                                    <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">Overdue</span>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="pe-4">
                            <a href="<?php echo e(route('faculty.tasks.show', $task)); ?>" class="btn btn-sm btn-outline-primary fw-medium px-3" style="font-size: 0.8rem; border-color: var(--navy); color: var(--navy);">
                                Update
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-check fs-2 d-block mb-2 text-secondary"></i>
                            No tasks assigned to you right now.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($tasks->hasPages()): ?>
        <div class="px-4 py-3 border-top" style="border-color: var(--border) !important;">
            <?php echo e($tasks->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/faculty/tasks/index.blade.php ENDPATH**/ ?>