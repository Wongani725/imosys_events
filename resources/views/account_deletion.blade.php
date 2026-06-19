<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Account Deletion Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('cms/vendor/fonts/boxicons.css') }}">
    <style>
        body {
            background-color: #f4f5f7;
            font-family: 'Public Sans', sans-serif;
        }
        .deletion-card {
            max-width: 500px;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        .brand-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo img {
            height: 80px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="deletion-card">
        <div class="brand-logo">
            <img src="{{ asset('MEI_LOGO.png') }}" alt="MEI Logo">
        </div>

        <h4 class="text-center mb-3">Account Deletion Request</h4>
        <p class="text-muted text-center mb-4">Please enter your registered email address to request deletion of your account.</p>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ url('/account-deletion') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        required>
            </div>
            <button type="submit" class="btn w-100" style="background-color: #37a739; color: white;">Request Deletion</button>
        </form>

        <div class="text-center mt-4">
            {{--            <a href="{{ url('/') }}" class="text-decoration-none">&larr; Back to Home</a>--}}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
