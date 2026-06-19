@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Walk-in Registration</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.walkin.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event <span class="text-danger">*</span></label>
                        <select name="event_id" class="form-select" required>
                            <option value="">Select Event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->event_id }}">{{ $event->event_name }} ({{ $event->start_date }} - {{ $event->end_date }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Participant Name <span class="text-danger">*</span></label>
                        <input type="text" name="participant" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email_address" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone_number" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" name="company_name" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Non-Member">Non-Member</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Accommodation</label>
                        <select name="accommodation" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hotel</label>
                        <select name="hotel_id" class="form-select">
                            <option value="">None</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }} ({{ $hotel->available_count }} avail)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Total Meals <span class="text-danger">*</span></label>
                        <input type="number" name="meals" class="form-control" min="0" value="2" required>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="generate_name_tag" value="1" class="form-check-input" id="genTag">
                            <label class="form-check-label" for="genTag">Generate & Download Name Tag</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Register Walk-in</button>
            </form>
        </div>
    </div>
</div>
@endsection
