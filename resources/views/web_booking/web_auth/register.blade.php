@extends('layouts.guest')

@section('title', 'Register - ' . config('app.name'))

@section('content')
    <h4 class="text-center mb-3">Complete Registration</h4>
    <p class="text-muted text-center mb-4">Fill in your details to complete registration.</p>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.nonmember') }}">
        @csrf
        <input type="hidden" name="identifier" value="{{ session('identifier') }}">

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="{{ session('identifier') }}" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="text" name="phone_number" class="form-control" required placeholder="e.g. 0999123456">
        </div>
        <div class="mb-3">
            <label class="form-label">Organisation</label>
            <input type="text" name="organisation" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Country</label>
            <select class="form-select" name="country" required>
                <option value="" disabled selected>Select Country...</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->name }}">{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select" required>
                <option value="" disabled selected>Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Complete Registration</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('participant.login') }}">Back to Login</a>
    </div>
@endsection
