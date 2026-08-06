<section>
    <p class="text-muted small mb-4">
        Update your account's profile information and email address.
    </p>

    <form id="send-verification" method="post" action="<?php echo e(route('verification.send')); ?>">
        <?php echo csrf_field(); ?>
    </form>

    <form method="post" action="<?php echo e(route('profile.update')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('patch'); ?>

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold text-dark small">Name</label>
            <input id="name" name="name" type="text" class="form-control border <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   style="border-color: var(--border) !important;" value="<?php echo e(old('name', $user->name)); ?>" required autofocus autocomplete="name">
            <?php $__errorArgs = ['name'];
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

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-dark small">Email</label>
            <input id="email" name="email" type="email" class="form-control border <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   style="border-color: var(--border) !important;" value="<?php echo e(old('email', $user->email)); ?>" required autocomplete="username">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="text-danger small mt-1"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <?php if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()): ?>
                <div class="mt-2">
                    <p class="text-muted small">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline small" style="color: var(--navy);">
                            Click here to re-send the verification email.
                        </button>
                    </p>

                    <?php if(session('status') === 'verification-link-sent'): ?>
                        <p class="text-success small fw-medium mt-1">
                            A new verification link has been sent to your email address.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: var(--navy);">
                Save Changes
            </button>

            <?php if(session('status') === 'profile-updated'): ?>
                <span class="text-success small fw-medium">Saved.</span>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php /**PATH C:\Project\activity-monitor-main\resources\views/profile/partials/update-profile-information-form.blade.php ENDPATH**/ ?>