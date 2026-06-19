
@extends('layouts.guest')

@section('title', env('APP_NAME')." Login")

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-info">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="login-form" name="login-form" class="mb-3">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="form-alignment-username">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="Email" id="email" name="email" required="required" autofocus="autofocus" value="{{ old('email', session('email')) }}"/>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                <a href="{{ route('password.request') }}">
                    <small>Forgot Password?</small>
                </a>
            </div>

            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                   aria-describedby="form-alignment-password2" id="password" name="password" required="required" autocomplete="current-password"/>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3" hidden>
            <label class="form-check m-0">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember"/>
                <span class="form-check-label">Remember me</span>
            </label>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn" style="color: white; background-color: #006198;">Login</button>
        </div>
    </form>
@endsection


@section('script')
    <script src="{{asset('')}}cms//js/pages-auth.js"></script>
@endsection
