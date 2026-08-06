<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">My Profile</h1>
        <p class="text-muted small mb-0">Manage your account settings and security</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-person me-2" style="color: var(--gold);"></i>Profile Information
                </h6>
            </div>
            <div class="card-body p-4">
                <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold" style="color: var(--navy);">
                    <i class="bi bi-shield-lock me-2" style="color: var(--maroon);"></i>Update Password
                </h6>
            </div>
            <div class="card-body p-4">
                <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <div class="card bg-white shadow-sm border-0 mb-4" style="border-radius: var(--radius, 8px);">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                </h6>
            </div>
            <div class="card-body p-4">
                <?php echo $__env->make('profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Project\activity-monitor-main\resources\views/profile/edit.blade.php ENDPATH**/ ?>