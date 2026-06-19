@extends('layouts.guest')

@section('title', 'Forgot Password - Admin')

@section('content')
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn w-100" style="background-color:#006198;color:white;">Send Reset Link</button>
    </form>
    <div class="text-center mt-3"><a href="{{ route('login') }}">Back to Login</a></div>
@endsection
