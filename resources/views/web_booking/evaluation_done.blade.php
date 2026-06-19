@extends('layouts.web_app')

@section('title', 'Evaluation Submitted')

@section('content')
<div class="container py-5 text-center">
    <div class="card eval-card p-5" style="border:none;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <h3>Evaluation Submitted!</h3>
        <p class="text-muted mb-4">Thank you for your feedback on <strong>{{ $event->event_name }}</strong>.</p>
        <a href="{{ $certUrl }}" class="btn btn-lg text-white px-5" style="background-color:#006198;">
            <i class="fas fa-download me-2"></i>Download Certificate
        </a>
        <div class="mt-3">
            <a href="{{ route('member-dashboard') }}" class="text-muted">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
