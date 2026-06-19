@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Send Notification</h2>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.notifications.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Audience <span class="text-danger">*</span></label>
                    <select name="audience_type" class="form-select" required>
                        <option value="">Select Audience</option>
                        <option value="all">All Members ({{ $memberCount }})</option>
                        <option value="members">IIA Members Only</option>
                        <option value="non_members">Non-Members ({{ $nonMemberCount }})</option>
                        <option value="pending_payment">Pending Payment</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="governance">Governance Attendants</option>
                        <option value="main">Main Attendants</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" maxlength="255" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Notification</button>
            </form>
        </div>
    </div>
</div>
@endsection
