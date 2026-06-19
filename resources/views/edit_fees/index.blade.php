@extends('layouts.app')

@section('title', 'Edit Fee')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Event Fee</h2>
        <a href="{{ route('view_participant_fees', $data[0]->event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_fees') }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="id" value="{{ $data[0]->id }}">
                <input type="hidden" name="event_id" value="{{ $data[0]->event_id }}">

                <div class="col-md-4">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="status" class="form-control" value="{{ $data[0]->status }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Member Type</label>
                    <select name="member_type" class="form-select">
                        <option value="Member" {{ ($data[0]->member_type ?? 'Member') == 'Member' ? 'selected' : '' }}>Member</option>
                        <option value="Non-Member" {{ ($data[0]->member_type ?? '') == 'Non-Member' ? 'selected' : '' }}>Non-Member</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Accommodation</label>
                    <select name="accommodation" class="form-select">
                        <option value="0" {{ !($data[0]->accommodation ?? false) ? 'selected' : '' }}>No</option>
                        <option value="1" {{ ($data[0]->accommodation ?? false) ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Spouse Included</label>
                    <select name="spouse_included" class="form-select">
                        <option value="0" {{ !($data[0]->spouse_included ?? false) ? 'selected' : '' }}>No</option>
                        <option value="1" {{ ($data[0]->spouse_included ?? false) ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price (MWK) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" value="{{ $data[0]->price }}" required step="0.01">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Extra Person Price (MWK)</label>
                    <input type="number" name="extra_person_price" class="form-control" value="{{ $data[0]->extra_person_price ?? 600000 }}" step="0.01">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-iia-blue"><i class="bx bx-save"></i> Update Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
