<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold text-navy mb-1"><i class="bi bi-calendar-event me-2 text-primary"></i>NBA Meetings</h1>
        <p class="text-muted small mb-0">Meetings organized by or attended by you for NBA Accreditation</p>
    </div>
    <div>
        <a href="<?php echo e(route('nba.meetings.create')); ?>" class="btn btn-sm btn-psg-primary fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Schedule NBA Meeting
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if($meetings->isEmpty()): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                No NBA meetings found. <a href="<?php echo e(route('nba.meetings.create')); ?>" class="text-navy fw-semibold">Schedule a Meeting</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Organizer</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr style="cursor: pointer;" onclick="window.location='<?php echo e(route('nba.meetings.show', $m)); ?>'">
                            <td class="ps-4">
                                <div class="fw-semibold text-navy"><?php echo e($m->title); ?></div>
                                <div class="text-muted" style="font-size: 0.78rem;"><?php echo e(Str::limit($m->agenda ?? 'No agenda', 40)); ?></div>
                            </td>
                            <td><?php echo e($m->scheduled_at->format('M d, Y g:i A')); ?></td>
                            <td><?php echo e($m->venue ?? 'Online / Main Block'); ?></td>
                            <td><?php echo e($m->organizer->name ?? 'Self'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($m->status === 'completed' ? 'success' : 'primary'); ?>-subtle text-<?php echo e($m->status === 'completed' ? 'success' : 'primary'); ?>">
                                    <?php echo e(ucfirst($m->status)); ?>

                                </span>
                            </td>
                            <td class="pe-4 text-end" onclick="event.stopPropagation();">
                                <a href="<?php echo e(route('nba.meetings.show', $m)); ?>" class="btn btn-sm btn-outline-navy py-1 px-2" style="font-size: 0.8rem;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                <?php echo e($meetings->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/nba/meetings/index.blade.php ENDPATH**/ ?>