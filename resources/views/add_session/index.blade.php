@extends('layouts.app')

@section('title', 'Add Session')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Add Event Session</h2>
        </div>
        <div class="card-body">
            <form action="{{ url('add_session2') }}" method="POST">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event_id }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Name</label>
                        <input type="text" class="form-control" value="{{ $event_name }}" disabled>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Session Date <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Description</label>
                        <select name="description" class="form-select">
                            <option value="">Select...</option>
                            <option value="Morning">Morning</option>
                            <option value="Afternoon">Afternoon</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Session</button>
                <a href="{{ url('view_sessions', $event_id) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endSection
