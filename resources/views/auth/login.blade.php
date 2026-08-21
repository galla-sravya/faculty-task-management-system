<x-guest-layout>
    <div class="mb-3">
        <h5 class="fw-bold mb-1" style="color: var(--navy);">Sign In</h5>
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
        <div class="form-check mb-3">
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

    <div class="d-flex align-items-center my-3">
        <hr class="flex-grow-1 text-muted">
        <span class="mx-3 text-muted small fw-semibold">OR</span>
        <hr class="flex-grow-1 text-muted">
    </div>

    <div class="d-grid mb-2">
        <a href="{{ route('google.login') }}" class="btn btn-outline-secondary w-100 fw-semibold py-2 shadow-sm d-flex justify-content-center align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px" class="me-2">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg> Continue with Google
        </a>
    </div>

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
