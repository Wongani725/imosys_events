@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create Master Meal Tag</h2>
        <a href="{{ route('admin.master-meal-tags.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.master-meal-tags.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Event</label>
                    <select name="event_id" class="form-select" required>
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->event_id }}">{{ $event->event_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Member</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">Select Member</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->participant }} ({{ $member->member_id ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Meals</label>
                    <input type="number" name="total_meals" class="form-control" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Tag</button>
            </form>
        </div>
    </div>
</div>
@endsection
