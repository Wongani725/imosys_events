@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Notifications</h2>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-iia-blue"><i class="bx bx-plus"></i> New Notification</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Audience</th>
                        <th class="text-center">Sent</th>
                        <th class="text-center">Read</th>
                        <th>By</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $n)
                    <tr>
                        <td class="fw-semibold">
                            <a href="{{ route('admin.notifications.show', $n) }}" class="text-decoration-none">{{ $n->title }}</a>
                        </td>
                        <td class="text-muted small">{{ Str::limit($n->message, 60) }}</td>
                        <td><span class="badge bg-info">{{ str_replace('_', ' ', $n->audience_type) }}</span></td>
                        <td class="text-center">{{ $n->sentCount() }}</td>
                        <td class="text-center">{{ $n->readCount() }}</td>
                        <td>{{ $n->creator->name ?? 'N/A' }}</td>
                        <td>{{ $n->created_at->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.notifications.show', $n) }}" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                            <form action="{{ route('admin.notifications.destroy', $n) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this notification?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No notifications sent yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())<div class="card-footer">{{ $notifications->links() }}</div>@endif
    </div>
</div>
@endsection
