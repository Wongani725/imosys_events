@extends('layouts.web_app')

@section('title', 'Update Profile')

@section('content')
<div class="container py-4" style="max-width: 700px;">
    @php $user = Auth::guard('member')->user(); @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title text-center mb-1">Update Profile</h4>
            <p class="text-muted text-center mb-4">View and update your profile details below.</p>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.change') }}">
                @csrf

                <h5 class="border-bottom pb-2 mb-3">Profile Details</h5>

                <div class="mb-3">
                    <label class="form-label">Member ID</label>
                    <input type="text" class="form-control" value="{{ $user->member_id ?? 'N/A' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="participant" class="form-control" value="{{ old('participant', $user->participant) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email_address" class="form-control" value="{{ old('email_address', $user->email_address) }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $user->company_name) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                </div>

                <hr class="my-4">

                <h5 class="border-bottom pb-2 mb-3">Change Password</h5>
                <p class="text-muted small mb-3">Leave these fields blank if you don't want to change your password.</p>

                @if(!empty($user->password))
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                @else
                    <p class="text-warning small">You haven't set a password yet. Enter a new password below to set one.</p>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn w-100 py-2" style="background-color: #006198; color: white; font-size: 16px;">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection