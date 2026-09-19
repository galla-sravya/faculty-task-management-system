<section>
    <p class="text-muted small mb-4">
        Ensure your account is using a long, random password to stay secure.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold text-dark small">Current Password</label>
            <div class="input-group">
                <input id="update_password_current_password" name="current_password" type="password"
                       class="form-control border @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                       style="border-color: var(--border) !important;" autocomplete="current-password">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#update_password_current_password" style="border-color: var(--border) !important;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @if($errors->updatePassword->has('current_password'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold text-dark small">New Password</label>
            <div class="input-group">
                <input id="update_password_password" name="password" type="password"
                       class="form-control border @if($errors->updatePassword->has('password')) is-invalid @endif"
                       style="border-color: var(--border) !important;" autocomplete="new-password">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#update_password_password" style="border-color: var(--border) !important;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @if($errors->updatePassword->has('password'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label fw-semibold text-dark small">Confirm Password</label>
            <div class="input-group">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                       class="form-control border"
                       style="border-color: var(--border) !important;" autocomplete="new-password">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#update_password_password_confirmation" style="border-color: var(--border) !important;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: var(--navy);">
                Update Password
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small fw-medium">Saved.</span>
            @endif
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.querySelector(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
});
</script>
