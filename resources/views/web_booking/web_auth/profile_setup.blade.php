@extends('layouts.guest')

@section('title', 'Complete Profile - ' . config('app.name'))

@section('content')
    <p class="text-muted text-center mb-4">Set your password and company details to complete your account setup.</p>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('member.profile.setup') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3">
            <label class="form-label">Company Name <span class="text-danger">*</span></label>
            <input type="text" name="company_name" class="form-control" value="{{ $company_name ?? '' }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Complete Setup</button>
    </form>
@endsection
