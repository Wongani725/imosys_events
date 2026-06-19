@extends('layouts.guest')

@section('title', 'Set Password - ' . config('app.name'))

@section('content')
    <div class="text-center mb-4">
        <h4 class="text-iia-blue fw-bold">Set Your Password</h4>
        <p class="text-muted">Create a secure password for your IIA account.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('set-password') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
            <div class="form-text">Minimum 8 characters</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-iia-green w-100 py-2 fw-semibold">Set Password</button>
    </form>
@endsection
