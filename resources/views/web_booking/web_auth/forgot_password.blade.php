@extends('layouts.guest')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('content')
    <p class="text-muted text-center mb-4">Enter your registered email to reset your password.</p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('member.forgot.password.send') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Send Reset OTP</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('participant.login') }}">Back to Login</a>
    </div>
@endsection
