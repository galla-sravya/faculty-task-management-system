<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Create New Task</h1>
        <p class="text-muted small mb-0">Assign a new task to faculty members</p>
    </div>
    <a href="<?php echo e(route('hod.tasks.index')); ?>" class="btn btn-sm btn-outline-secondary fw-medium d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Tasks
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-pencil-square me-2" style="color: var(--gold);"></i>Task Details
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo e(route('hod.tasks.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control border" style="border-color: var(--border) !important;" required value="<?php echo e(old('title')); ?>" placeholder="Enter task title...">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Description <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="description" class="form-control border" style="border-color: var(--border) !important;" rows="4" placeholder="Describe the task..."><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-dark">Priority</label>
                            <select name="priority" class="form-select border" style="border-color: var(--border) !important;">
                                <option value="low" <?php echo e(old('priority') === 'low' ? 'selected' : ''); ?>>🟢 Low</option>
                                <option value="medium" <?php echo e(old('priority', 'medium') === 'medium' ? 'selected' : ''); ?>>🟡 Medium</option>
                                <option value="high" <?php echo e(old('priority') === 'high' ? 'selected' : ''); ?>>🟠 High</option>
                                <option value="urgent" <?php echo e(old('priority') === 'urgent' ? 'selected' : ''); ?>>🔴 Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline" class="form-control border" style="border-color: var(--border) !important;" required value="<?php echo e(old('deadline')); ?>">
                            <?php $__errorArgs = ['deadline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Assign To Faculty <span class="text-danger">*</span></label>
                        <select name="assignees[]" class="form-select border" style="border-color: var(--border) !important;" multiple size="5" required>
                            <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($faculty->id); ?>"><?php echo e($faculty->name); ?> (<?php echo e($faculty->designation); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Hold CTRL/CMD to select multiple faculty members.</small>
                        <?php $__errorArgs = ['assignees'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                            <i class="bi bi-send me-1"></i> Create Task & Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/hod/tasks/create.blade.php ENDPATH**/ ?>