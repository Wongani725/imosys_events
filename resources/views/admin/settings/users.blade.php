@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Admin Users</h2>
        <a href="{{ route('admin.settings.create-user') }}" class="btn btn-iia-blue"><i class="bx bx-plus"></i> Add User</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td class="fw-semibold">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge bg-primary">{{ $u->roles->first()->name ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge bg-{{ $u->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td>{{ $u->created_at?->format('d M Y') ?? '—' }}</td>
                        <td class="text-center">
                            <form action="{{ route('admin.settings.toggle-status', $u) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $u->status === 'active' ? 'warning' : 'success' }}"
                                        title="{{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bx bx-{{ $u->status === 'active' ? 'lock' : 'lock-open' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.settings.reset-password', $u) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Reset password for {{ $u->name }}?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-info" title="Reset Password"><i class="bx bx-key"></i></button>
                            </form>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.settings.destroy-user', $u) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
    </div>
</div>
@endsection
