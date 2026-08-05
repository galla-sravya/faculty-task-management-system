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
                   style="border-color: var(--border) !important;" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="name@psgitech.ac.in">
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
            <input id="password" type="password" name="password" class="form-control border @error('password') is-invalid @enderror"
                   style="border-color: var(--border) !important;" required autocomplete="current-password" placeholder="••••••••">
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
            <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </div>
    </form>
</x-guest-layout>
