<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Confirm Password</h4>
        <p class="text-muted small mb-0">
            This is a secure area of the application. Please confirm your password before continuing.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold text-dark small">Password</label>
            <input id="password" type="password" name="password" class="form-control border @error('password') is-invalid @enderror"
                   style="border-color: var(--border) !important;" required autocomplete="current-password" placeholder="Enter your password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="d-grid">
            <button type="submit" class="btn text-white fw-semibold py-2 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-shield-lock me-1"></i> Confirm
            </button>
        </div>
    </form>
</x-guest-layout>
