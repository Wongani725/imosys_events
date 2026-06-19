@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Update Event</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('update_event') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="event_id" value="{{ $data[0]->event_id }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event ID</label>
                        <input type="text" class="form-control" value="{{ $data[0]->event_id }}" disabled>
                        <small class="text-muted">Event ID cannot be changed.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="event_name" class="form-control" value="{{ $data[0]->event_name }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="event_theme" class="form-control" value="{{ $data[0]->theme }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select">
                            <option value="">Select Type</option>
                            <option value="governance" {{ ($data[0]->event_type ?? '') == 'governance' ? 'selected' : '' }}>Governance Forum</option>
                            <option value="main" {{ ($data[0]->event_type ?? '') == 'main' ? 'selected' : '' }}>Annual Conference</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="event_status" class="form-select" required>
                            <option value="active" {{ $data[0]->event_status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $data[0]->event_status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ $data[0]->start_date }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ $data[0]->end_date }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="event_venue" class="form-control" value="{{ $data[0]->event_venue }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location / Town</label>
                        <input type="text" name="venue" class="form-control" value="{{ $data[0]->venue }}" placeholder="e.g. Mangochi">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">GPS Coordinates</label>
                        <input type="text" name="event_gps_coordinates" class="form-control" value="{{ $data[0]->event_gps_coordinates }}" placeholder="-14.0500,35.1500">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking Start <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="booking_start_time" class="form-control" value="{{ $data[0]->booking_start_time ? date('Y-m-d\TH:i', strtotime($data[0]->booking_start_time)) : '' }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking End <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="booking_end_time" class="form-control" value="{{ $data[0]->booking_end_time ? date('Y-m-d\TH:i', strtotime($data[0]->booking_end_time)) : '' }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name Tag Background Image</label>
                        <input type="file" name="background_image" class="form-control" accept="image/*">
                        @if($data[0]->background_image)
                            <small class="text-muted">Current: {{ basename($data[0]->background_image) }}</small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Certificate Background Image</label>
                        <input type="file" name="certificate_background" class="form-control" accept="image/*">
                        @if($data[0]->certificate_background)
                            <small class="text-muted">Current: {{ basename($data[0]->certificate_background) }}</small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Program (PDF or Image)</label>
                        <input type="file" name="program_pdf" class="form-control" accept=".pdf,image/*">
                        @if($data[0]->program_pdf)
                            <small class="text-muted">Current: {{ basename($data[0]->program_pdf) }}</small>
                        @endif
                    </div>
                </div>

                <hr class="my-4">
                <h5>Name Tag Positioning</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Content Padding Top (px)</label>
                        <input type="number" name="name_tag_padding_top" class="form-control" value="{{ $data[0]->name_tag_padding_top ?? 283 }}" min="0" max="600">
                        <small class="text-muted">How far down content starts (default 283)</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">QR Margin Top (px)</label>
                        <input type="number" name="name_tag_qr_top" class="form-control" value="{{ $data[0]->name_tag_qr_top ?? 120 }}" min="0" max="600">
                        <small class="text-muted">Extra spacing for QR within content (default 120)</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Event</button>
            </form>
        </div>
    </div>
</div>
@endSection
