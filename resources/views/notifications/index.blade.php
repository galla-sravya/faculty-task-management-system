@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom" style="border-color: var(--border) !important;">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--navy);">Notifications</h1>
        <p class="text-muted small mb-0">System updates and activity alerts</p>
    </div>
    @if(auth()->user()->notifications()->where('is_read', false)->count() > 0)
    <form action="{{ route('notifications.markAllRead') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary fw-medium">
            <i class="bi bi-check2-all me-1"></i> Mark all as read
        </button>
    </form>
    @endif
</div>

<div class="row">
    <div class="col-lg-9 col-xl-8">
        <div class="card bg-white shadow-sm border-0" style="border-radius: var(--radius, 8px);">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        <div class="list-group-item px-4 py-3 border-bottom {{ $notification->is_read ? 'bg-light text-muted' : 'bg-white' }}" style="border-color: var(--border) !important;">
                            <div class="d-flex w-100 justify-content-between align-items-center gap-3">
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 40px; height: 40px; background-color: rgba(18, 39, 90, 0.06);">
                                        <i class="bi {{ $notification->icon ?? 'bi-bell' }} fs-5" style="color: var(--navy);"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 {{ !$notification->is_read ? 'fw-bold text-dark' : 'text-secondary' }}" style="font-size: 0.92rem;">
                                            {{ $notification->message }}
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>{{ $notification->sent_at ? $notification->sent_at->diffForHumans() : 'Recently' }}
                                        </small>
                                    </div>
                                </div>
                                @if(!$notification->is_read)
                                <form action="{{ route('notifications.markRead', $notification) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm text-white fw-medium px-3" style="background-color: var(--navy); font-size: 0.75rem;">
                                        Mark as Read
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bell-slash fs-1 d-block mb-3 text-secondary"></i>
                            <p class="mb-0 fw-medium">No notifications found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($notifications->hasPages())
            <div class="px-4 py-3 border-top" style="border-color: var(--border) !important;">
                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
