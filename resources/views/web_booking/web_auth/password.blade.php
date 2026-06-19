@extends('layouts.guest')

@section('title', 'Enter Password - ' . config('app.name'))

@section('content')
    <h4 class="mb-4">Enter your password</h4>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.password.submit') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Login</button>
    </form>
@endsection
