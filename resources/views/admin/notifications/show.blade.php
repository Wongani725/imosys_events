@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Notification Details</h2>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ $notification->title }}</h4>
            <p class="text-muted">{{ $notification->message }}</p>
            <hr>
            <p><strong>Audience:</strong> {{ str_replace('_', ' ', $notification->audience_type) }}</p>
            <p><strong>Sent:</strong> {{ $notification->created_at->format('d M Y H:i') }} by {{ $notification->creator->name ?? 'System' }}</p>
            <p><strong>Recipients:</strong> {{ $notification->sentCount() }} &nbsp;|&nbsp; <strong>Read:</strong> {{ $readCount }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-bold">Recipients</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead><tr><th>Member</th><th>Email</th><th>Status</th><th>Read At</th></tr></thead>
                <tbody>
                    @forelse($notification->recipients as $r)
                    <tr>
                        <td>{{ $r->member->participant ?? 'N/A' }}</td>
                        <td>{{ $r->member->email_address ?? 'N/A' }}</td>
                        <td>{{ $r->member->status ?? 'N/A' }}</td>
                        <td>{{ $r->read_at ? $r->read_at->format('d M Y H:i') : 'Unread' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center">No recipients</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
