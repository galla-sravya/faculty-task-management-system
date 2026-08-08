<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Sign In</h4>
        <p class="text-muted small mb-0">Enter your credentials to access your account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
            <input id="email" type="email" name="email" class="form-control border @error('email') is-invalid @enderror"
                   style="border-color: var(--border) !important;" required autofocus autocomplete="username"
                   placeholder="Enter your email address">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label fw-semibold text-dark small mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-decoration-none small fw-semibold" style="color: var(--navy);" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="position-relative">
                <input id="password" type="password" name="password" class="form-control border pe-5 @error('password') is-invalid @enderror"
                       style="border-color: var(--border) !important;" required autocomplete="current-password" placeholder="Enter your password">
                <button type="button" id="togglePassword" class="btn btn-link p-0 position-absolute end-0 top-50 translate-middle-y me-3 text-secondary text-decoration-none"
                        aria-label="Show password" style="border: none; background: transparent; z-index: 10; cursor: pointer;">
                    <i class="bi bi-eye" id="togglePasswordIcon" style="font-size: 1.1rem; transition: color 0.15s ease;"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small text-muted">
                Remember me on this device
            </label>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-psg-primary w-100 fw-semibold py-2 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            function clearLoginErrors() {
                const errorElements = document.querySelectorAll('.login-error-msg, .invalid-feedback');
                errorElements.forEach(function (el) {
                    el.style.display = 'none';
                });
                const invalidInputs = document.querySelectorAll('.is-invalid');
                invalidInputs.forEach(function (el) {
                    el.classList.remove('is-invalid');
                });
            }

            if (emailInput) {
                emailInput.addEventListener('input', clearLoginErrors);
            }
            if (passwordInput) {
                passwordInput.addEventListener('input', clearLoginErrors);
            }

            if (togglePassword && passwordInput && togglePasswordIcon) {
                togglePassword.addEventListener('click', function (e) {
                    e.preventDefault();
                    const start = passwordInput.selectionStart;
                    const end = passwordInput.selectionEnd;
                    const isPassword = passwordInput.getAttribute('type') === 'password';

                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                    if (isPassword) {
                        togglePasswordIcon.className = 'bi bi-eye-slash text-navy';
                        togglePassword.setAttribute('aria-label', 'Hide password');
                    } else {
                        togglePasswordIcon.className = 'bi bi-eye text-secondary';
                        togglePassword.setAttribute('aria-label', 'Show password');
                    }

                    passwordInput.focus();
                    if (start !== null && end !== null) {
                        passwordInput.setSelectionRange(start, end);
                    }
                });

                togglePassword.addEventListener('mouseenter', function() {
                    if (passwordInput.getAttribute('type') === 'password') {
                        togglePasswordIcon.classList.add('text-navy');
                        togglePasswordIcon.classList.remove('text-secondary');
                    }
                });

                togglePassword.addEventListener('mouseleave', function() {
                    if (passwordInput.getAttribute('type') === 'password') {
                        togglePasswordIcon.classList.remove('text-navy');
                        togglePasswordIcon.classList.add('text-secondary');
                    }
                });
            }

            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    clearLoginErrors();
                }
            });
        });
    </script>
</x-guest-layout>
