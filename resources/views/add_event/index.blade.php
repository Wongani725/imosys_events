@extends('layouts.app')

@section('title', 'Add Event')

@section('content')
<div class="container-fluid py-4">
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible">{{ session()->get('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Add Event</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('add_event2') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event ID <span class="text-danger">*</span></label>
                        <input type="text" name="event_id" class="form-control" placeholder="e.g. IIA-GF-2026" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="event_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="event_theme" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select">
                            <option value="">Select Type</option>
                            <option value="governance">Governance Forum</option>
                            <option value="main">Annual Conference</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="event_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="event_venue" class="form-control" placeholder="Venue name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location / Town</label>
                        <input type="text" name="venue" class="form-control" placeholder="e.g. Mangochi">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">GPS Coordinates</label>
                        <input type="text" name="event_gps_coordinates" class="form-control" placeholder="-14.0500,35.1500">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking Start <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="booking_start_time" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking End <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="booking_end_time" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name Tag Background Image</label>
                        <input type="file" name="background_image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Certificate Background Image</label>
                        <input type="file" name="certificate_background" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Program (PDF or Image)</label>
                        <input type="file" name="program_pdf" class="form-control" accept=".pdf,image/*">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Event</button>
            </form>
        </div>
    </div>
</div>
@endsection
