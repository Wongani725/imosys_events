@extends('layouts.app')

@section('title', 'Edit Session')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Edit Session</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('update_session') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $data[0]->session_id }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Session ID</label>
                        <input type="text" class="form-control" value="{{ $data[0]->session_id }}" disabled>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Session Date <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" class="form-control" value="{{ $data[0]->session_date }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="{{ $data[0]->start_time }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="{{ $data[0]->end_time }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Description</label>
                        <select name="description" class="form-select">
                            <option value="Morning" {{ $data[0]->description == 'Morning' ? 'selected' : '' }}>Morning</option>
                            <option value="Afternoon" {{ $data[0]->description == 'Afternoon' ? 'selected' : '' }}>Afternoon</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Session</button>
            </form>
        </div>
    </div>
</div>
@endSection
