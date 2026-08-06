<section>
    <p class="text-muted small mb-4">
        Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <!-- Delete Account Button triggers Bootstrap Modal -->
    <button type="button" class="btn btn-danger fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        <i class="bi bi-trash me-1"></i> Delete Account
    </button>

    <!-- Bootstrap 5 Modal -->
    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: var(--radius, 8px);">
                <form method="post" action="<?php echo e(route('profile.destroy')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('delete'); ?>

                    <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                        <h5 class="modal-title fw-bold text-danger" id="confirmUserDeletionLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body py-4">
                        <p class="text-muted small mb-3">
                            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                        </p>

                        <div>
                            <label for="delete_password" class="form-label fw-semibold text-dark small">Password</label>
                            <input id="delete_password" name="password" type="password"
                                   class="form-control border <?php if($errors->userDeletion->has('password')): ?> is-invalid <?php endif; ?>"
                                   style="border-color: var(--border) !important;" placeholder="Enter your password">
                            <?php if($errors->userDeletion->has('password')): ?>
                                <div class="text-danger small mt-1"><?php echo e($errors->userDeletion->first('password')); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="modal-footer border-top" style="border-color: var(--border) !important;">
                        <button type="button" class="btn btn-outline-secondary fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-semibold">
                            <i class="bi bi-trash me-1"></i> Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/profile/partials/delete-user-form.blade.php ENDPATH**/ ?>