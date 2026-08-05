<x-guest-layout>
    <div class="mb-4">
        <h4 class="fw-bold mb-1" style="color: var(--navy);">Verify Email</h4>
        <p class="text-muted small mb-0">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small py-2 mb-4">
            <i class="bi bi-check-circle me-1"></i>
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn text-white fw-semibold px-4 shadow-sm" style="background-color: var(--navy);">
                <i class="bi bi-envelope me-1"></i> Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary fw-medium small">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
