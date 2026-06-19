@extends('layouts.app')

@section('title', 'Add Hotel')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add Hotel</h2>
        <a href="{{ route('view_hotels', $event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ url('add_hotel2') }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event_id }}">

                <div class="col-md-6">
                    <label class="form-label">Hotel Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Room Quantity</label>
                    <input type="number" class="form-control" name="quantity" value="0" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Extra Person Cost (MWK)</label>
                    <input type="number" class="form-control" name="extra_price" value="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event</label>
                    <input type="text" class="form-control" value="{{ $event->event_name ?? $event_id }}" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Venue Type</label>
                    <input type="text" class="form-control" value="{{ $event->event_type ?? 'both' }}" disabled>
                    <input type="hidden" name="venue_type" value="{{ $event->event_type ?? 'both' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Latitudes</label>
                    <input type="text" class="form-control" name="latitudes" placeholder="-14.0500">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Longitudes</label>
                    <input type="text" class="form-control" name="longitudes" placeholder="35.1500">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-iia-blue"><i class="bx bx-save"></i> Save Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
