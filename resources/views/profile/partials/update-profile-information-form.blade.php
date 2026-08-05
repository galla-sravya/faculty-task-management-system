<section>
    <p class="text-muted small mb-4">
        Update your account's profile information and email address.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold text-dark small">Name</label>
            <input id="name" name="name" type="text" class="form-control border @error('name') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-dark small">Email</label>
            <input id="email" name="email" type="email" class="form-control border @error('email') is-invalid @enderror"
                   style="border-color: var(--border) !important;" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted small">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline small" style="color: var(--navy);">
                            Click here to re-send the verification email.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small fw-medium mt-1">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: var(--navy);">
                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-medium">Saved.</span>
            @endif
        </div>
    </form>
</section>
