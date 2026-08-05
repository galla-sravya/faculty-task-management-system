<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Create Account</h4>
        <p class="text-muted small mb-0">Register a new account to get started</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold text-dark small">Full Name</label>
            <input id="name" type="text" name="name" class="form-control border @error('name') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your full name">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
            <input id="email" type="email" name="email" class="form-control border @error('email') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('email') }}" required autocomplete="username" placeholder="name@psgitech.ac.in">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold text-dark small">Password</label>
            <input id="password" type="password" name="password" class="form-control border @error('password') is-invalid @enderror"
                   style="border-color: var(--border) !important;" required autocomplete="new-password" placeholder="Minimum 8 characters">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold text-dark small">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control border"
                   style="border-color: var(--border) !important;" required autocomplete="new-password" placeholder="Re-enter password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-person-plus me-1"></i> Register
            </button>
        </div>

        <div class="text-center">
            <a class="text-decoration-none small fw-semibold" style="color: var(--navy);" href="{{ route('login') }}">
                Already registered? Sign In
            </a>
        </div>
    </form>
</x-guest-layout>
