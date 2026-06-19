@extends('layouts.guest')

@section('title', 'Login - ' . config('app.name'))

@section('content')
    <p class="text-muted text-center mb-4">Enter your email and member details to receive a login code.</p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('otp.request') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Member Status</label>
            <select name="member_status" id="member_status" class="form-select" required>
                <option value="" disabled selected>Select status</option>
                <option value="member">Paid Up Member</option>
                <option value="non-member">Non-Member</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>

        <div class="mb-3" id="member_id_field" style="display: none;">
            <label class="form-label">Member ID <small class="text-muted">(if member)</small></label>
            <input type="text" name="member_id" class="form-control">
        </div>

        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Continue</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('member.forgot.password.form') }}" class="text-muted small">Forgot Password?</a>
    </div>

    <!-- <small class="d-block text-center mt-2 text-muted">
        <a href="{{ route('register.nonmember') }}">Register as Non-Member</a>
    </small> -->

    <script>
        const statusSelect = document.getElementById('member_status');
        const memberIdField = document.getElementById('member_id_field');

        statusSelect.addEventListener('change', function () {
            if (this.value === 'member') {
                memberIdField.style.display = 'block';
            } else {
                memberIdField.style.display = 'none';
            }
        });
    </script>
@endsection
