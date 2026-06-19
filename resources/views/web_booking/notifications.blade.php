@extends('layouts.web_app')

@section('title', 'Notifications')

@push('styles')
<style>
    .notification-item { border-left: 4px solid #006198; transition: background 0.2s; }
    .notification-item:hover { background: #f0f4ff; }
    .notification-item.unread { border-left-color: #dc3545; background: #fff5f5; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Notifications</h3>
        @if($notifications->whereNull('read_at')->count() > 0)
            <form method="POST" action="{{ route('member.notifications.read-all') }}">
                @csrf
                <button class="btn btn-sm btn-outline-primary">Mark All as Read</button>
            </form>
        @endif
    </div>

    @forelse($notifications as $notif)
        <div class="card dashboard-card mb-2 notification-item {{ $notif->read_at ? '' : 'unread' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-1">{{ $notif->notification->title }}</h6>
                        <p class="mb-1 text-muted small">{{ $notif->notification->message }}</p>
                        <small class="text-muted">{{ $notif->notification->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="text-end">
                        @if(!$notif->read_at)
                            <form method="POST" action="{{ route('member.notifications.read', $notif) }}">
                                @csrf
                                <button class="btn btn-sm btn-link text-decoration-none">Mark Read</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card dashboard-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">No notifications yet.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
