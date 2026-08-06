<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Global Search Results</h1>
        <p class="text-muted small mb-0">Results for query: "<strong class="text-dark"><?php echo e($query); ?></strong>"</p>
    </div>
</div>

<!-- Search Input -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-body p-3">
        <form action="<?php echo e(route('global.search')); ?>" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control border" value="<?php echo e($query); ?>" placeholder="Search by faculty, task, category, priority, meeting, status..." required>
            <button type="submit" class="btn text-white fw-medium px-4 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-search me-1"></i>Search
            </button>
        </form>
    </div>
</div>

<!-- Tasks Results -->
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-clipboard-data me-2" style="color: var(--gold);"></i>Matching Tasks (<?php echo e($tasks->count()); ?>)
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary" style="font-size: 0.72rem;">
                    <tr>
                        <th class="ps-3 py-3">Task Title</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Deadline</th>
                        <th class="pe-3 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-3 fw-semibold text-dark"><?php echo e($t->title); ?></td>
                        <td><span class="badge rounded-pill bg-<?php echo e($t->priority_color); ?> px-2 py-1"><?php echo e(ucfirst($t->priority)); ?></span></td>
                        <td><span class="badge rounded-pill bg-<?php echo e($t->status_color); ?> px-2 py-1"><?php echo e(ucfirst(str_replace('_', ' ', $t->status))); ?></span></td>
                        <td class="small text-muted"><?php echo e($t->deadline->format('M d, Y')); ?></td>
                        <td class="pe-3 text-end">
                            <a href="<?php echo e(auth()->user()->isHod() ? route('hod.tasks.show', $t) : route('faculty.tasks.show', $t)); ?>" class="btn btn-sm btn-outline-primary fw-medium" style="font-size:0.75rem;">View</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center py-3 text-muted">No matching tasks found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Faculty Results -->
<?php if(auth()->user()->isHod() && $faculties->isNotEmpty()): ?>
<div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="m-0 fw-bold" style="color: var(--navy);">
            <i class="bi bi-people me-2" style="color: var(--maroon);"></i>Matching Faculty (<?php echo e($faculties->count()); ?>)
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                        <img src="<?php echo e($f->profile_photo_url); ?>" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;border:2px solid var(--navy);">
                        <div>
                            <div class="fw-semibold text-dark" style="font-size:0.9rem;"><?php echo e($f->name); ?></div>
                            <div class="text-muted small" style="font-size:0.75rem;"><?php echo e($f->designation ?? 'Faculty'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/search/index.blade.php ENDPATH**/ ?>