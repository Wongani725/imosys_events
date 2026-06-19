@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Members</h2>
        <div>
            <a href="{{ route('admin.members.import.form') }}" class="btn btn-outline-primary me-2">
                <i class="bx bx-upload"></i> Import
            </a>
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add Member
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Stats --}}
    <div class="row g-2 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #006198;">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-primary mb-0">{{ $totalMembers }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #28a745;">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-success mb-0">{{ $memberCount }}</h4>
                    <small class="text-muted">IIA Members</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #ffc107;">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-warning mb-0">{{ $nonMemberCount }}</h4>
                    <small class="text-muted">Non-Members</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #17a2b8;">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-info mb-0">{{ $executiveCount }}</h4>
                    <small class="text-muted">Executives</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, member ID, phone, company..."
                               value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Search</button>
                        @if(request('search') || request('filter'))
                            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">All Members</option>
                        <option value="member" {{ request('filter') === 'member' ? 'selected' : '' }}>IIA Members</option>
                        <option value="non_member" {{ request('filter') === 'non_member' ? 'selected' : '' }}>Non-Members</option>
                        <option value="executive" {{ request('filter') === 'executive' ? 'selected' : '' }}>Executives</option>
                        <option value="no_password" {{ request('filter') === 'no_password' ? 'selected' : '' }}>No Password Set</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Executive</th>
                        <th>Password</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                    <tr>
                        <td><code>{{ $member->member_id ?? '—' }}</code></td>
                        <td class="fw-semibold">{{ $member->participant }}</td>
                        <td>{{ $member->email_address }}</td>
                        <td>{{ $member->phone_number ?? '—' }}</td>
                        <td>{{ $member->company_name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $member->status === 'Member' ? 'success' : 'secondary' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td>
                            @if($member->is_executive)
                                <span class="badge bg-info">Yes</span>
                            @else
                                <span class="badge bg-light text-dark">No</span>
                            @endif
                        </td>
                        <td>
                            @if($member->password_set)
                                <span class="badge bg-success">Set</span>
                            @else
                                <span class="badge bg-warning text-dark">Not Set</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <form action="{{ route('admin.members.destroy', $member) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $member->participant }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
            <div class="card-footer">{{ $members->links() }}</div>
        @endif
    </div>
</div>
@endsection
