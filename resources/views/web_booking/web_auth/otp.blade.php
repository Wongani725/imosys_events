<!-- resources/views/auth/verify-otp.blade.php -->
@extends('layouts.guest')

@section('title', 'Verify OTP - ' . config('app.name'))

@section('content')
    <h4 class="text-center mb-3">Verify OTP</h4>
    <p class="text-muted text-center mb-4">Enter the 6-digit code sent to your email.</p>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <input type="hidden" name="identifier" value="{{ session('identifier') }}">
        <input type="hidden" name="token" id="firebase_token">

        <div class="mb-3">
            <label class="form-label">OTP Code</label>
            <input type="text" name="otp" class="form-control" maxlength="6" required placeholder="123456">
        </div>

        <button type="submit" class="btn w-100" style="background-color: #006198; color: white;">Verify</button>
    </form>

    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging.js"></script>
    <script>
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            messagingSenderId: "YOUR_SENDER_ID",
            appId: "YOUR_APP_ID",
            projectId: "YOUR_PROJECT_ID",
        };
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
        messaging.requestPermission().then(() => messaging.getToken()).then(token => {
            document.getElementById('firebase_token').value = token;
        });
    </script>
@endsection
