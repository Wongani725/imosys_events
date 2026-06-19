@extends('layouts.app')

@section('title', 'Edit Hotel')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Hotel</h2>
        <a href="{{ route('view_hotels', $data[0]->event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_hotel') }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="id" value="{{ $data[0]->id }}">

                <div class="col-md-6">
                    <label class="form-label">Hotel Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ $data[0]->name }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Room Quantity</label>
                    <input type="number" class="form-control" name="quantity" value="{{ $data[0]->quantity ?? 0 }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Extra Person Cost (MWK)</label>
                    <input type="number" class="form-control" name="extra_price" value="{{ $data[0]->extra_price ?? 0 }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Venue Type</label>
                    <select name="venue_type" class="form-select">
                        <option value="governance" {{ ($data[0]->venue_type ?? '') == 'governance' ? 'selected' : '' }}>Governance</option>
                        <option value="main" {{ ($data[0]->venue_type ?? '') == 'main' ? 'selected' : '' }}>Main</option>
                        <option value="both" {{ ($data[0]->venue_type ?? '') == 'both' ? 'selected' : '' }}>Both</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-iia-blue"><i class="bx bx-save"></i> Update Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endSection
