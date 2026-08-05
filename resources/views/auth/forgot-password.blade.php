<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Forgot Password</h4>
        <p class="text-muted small mb-0">
            Enter your email address and we'll send you a password reset link.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
            <input id="email" type="email" name="email" class="form-control border @error('email') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('email') }}" required autofocus placeholder="name@psgitech.ac.in">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-envelope me-1"></i> Email Password Reset Link
            </button>
        </div>

        <div class="text-center">
            <a class="text-decoration-none small fw-semibold" style="color: var(--navy);" href="{{ route('login') }}">
                Back to Sign In
            </a>
        </div>
    </form>
</x-guest-layout>
