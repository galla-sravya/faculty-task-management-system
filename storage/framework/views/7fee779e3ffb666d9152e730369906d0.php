<section>
    <p class="text-muted small mb-4">
        Ensure your account is using a long, random password to stay secure.
    </p>

    <form method="post" action="<?php echo e(route('password.update')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('put'); ?>

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold text-dark small">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="form-control border <?php if($errors->updatePassword->has('current_password')): ?> is-invalid <?php endif; ?>"
                   style="border-color: var(--border) !important;" autocomplete="current-password">
            <?php if($errors->updatePassword->has('current_password')): ?>
                <div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('current_password')); ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold text-dark small">New Password</label>
            <input id="update_password_password" name="password" type="password"
                   class="form-control border <?php if($errors->updatePassword->has('password')): ?> is-invalid <?php endif; ?>"
                   style="border-color: var(--border) !important;" autocomplete="new-password">
            <?php if($errors->updatePassword->has('password')): ?>
                <div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('password')); ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label fw-semibold text-dark small">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                   class="form-control border"
                   style="border-color: var(--border) !important;" autocomplete="new-password">
            <?php if($errors->updatePassword->has('password_confirmation')): ?>
                <div class="text-danger small mt-1"><?php echo e($errors->updatePassword->first('password_confirmation')); ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: var(--navy);">
                Update Password
            </button>

            <?php if(session('status') === 'password-updated'): ?>
                <span class="text-success small fw-medium">Saved.</span>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/profile/partials/update-password-form.blade.php ENDPATH**/ ?>