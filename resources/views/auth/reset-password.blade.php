<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Reset Password</h4>
        <p class="text-muted small mb-0">Enter your new password below.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
            <input id="email" type="email" name="email" class="form-control border @error('email') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold text-dark small">New Password</label>
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

        <div class="d-grid">
            <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-key me-1"></i> Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
