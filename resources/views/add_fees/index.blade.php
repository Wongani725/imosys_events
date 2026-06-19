@extends('layouts.app')

@section('title', 'Add Fee')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add Event Fee</h2>
        <a href="{{ route('view_participant_fees', $event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ url('add_fees2') }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event_id }}">

                <div class="col-md-4">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="status" class="form-control" placeholder="e.g. Sun N Sand Members" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Member Type</label>
                    <select name="member_type" class="form-select">
                        <option value="Member">Member</option>
                        <option value="Non-Member">Non-Member</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Accommodation</label>
                    <select name="accommodation" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Spouse Included</label>
                    <select name="spouse_included" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price (MWK) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Extra Person Price (MWK)</label>
                    <input type="number" name="extra_person_price" class="form-control" value="600000">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-iia-blue"><i class="bx bx-save"></i> Save Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
