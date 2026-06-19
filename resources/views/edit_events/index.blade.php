@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Event</h2>
        <a href="{{ route('events') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back to Events</a>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_event') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="event_id" value="{{ $data[0]->event_id }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event ID</label>
                        <input type="text" class="form-control" value="{{ $data[0]->event_id }}" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Name</label>
                        <input type="text" name="event_name" class="form-control" value="{{ $data[0]->event_name }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="event_theme" class="form-control" value="{{ $data[0]->theme }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Type</label>
                        <select name="event_type" class="form-select">
                            <option value="governance" {{ $data[0]->event_type == 'governance' ? 'selected' : '' }}>Governance Forum</option>
                            <option value="main" {{ $data[0]->event_type == 'main' ? 'selected' : '' }}>Annual Conference</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="event_status" class="form-select">
                            <option value="active" {{ $data[0]->event_status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $data[0]->event_status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $data[0]->start_date }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $data[0]->end_date }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="event_venue" class="form-control" value="{{ $data[0]->event_venue }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Location / Town</label>
                        <input type="text" name="venue" class="form-control" value="{{ $data[0]->venue ?? '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">GPS Coordinates</label>
                        <input type="text" name="event_gps_coordinates" class="form-control" value="{{ $data[0]->event_gps_coordinates }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking Start</label>
                        <input type="datetime-local" name="booking_start_time" class="form-control" value="{{ $data[0]->booking_start_time }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Booking End</label>
                        <input type="datetime-local" name="booking_end_time" class="form-control" value="{{ $data[0]->booking_end_time }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name Tag Background</label>
                        <input type="file" name="background_image" class="form-control" accept="image/*">
                        @if($data[0]->background_image)
                            <small class="text-muted">Current: <a href="{{ asset($data[0]->background_image) }}" target="_blank">view image</a></small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Certificate Background</label>
                        <input type="file" name="certificate_background" class="form-control" accept="image/*">
                        @if($data[0]->certificate_background)
                            <small class="text-muted">Current: <a href="{{ asset($data[0]->certificate_background) }}" target="_blank">view image</a></small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Program PDF</label>
                        <input type="file" name="program_pdf" class="form-control" accept=".pdf">
                        @if($data[0]->program_pdf)
                            <small class="text-muted">Current: <a href="{{ asset($data[0]->program_pdf) }}" target="_blank">view PDF</a></small>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-iia-blue mt-3"><i class="bx bx-save"></i> Update Event</button>
            </form>
        </div>
    </div>
</div>
@endsection
