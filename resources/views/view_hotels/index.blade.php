@extends('layouts.app')

@section('title', 'Hotels')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Hotels</h2>
        <a href="{{ route('view_participants', $event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back to Participants</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hotel Name</th>
                        <th>Qty</th>
                        <th>Available</th>
                        <th>Booked</th>
                        <th>Venue Type</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hotels as $i)
                    <tr>
                        <td>{{ $i->id }}</td>
                        <td class="fw-semibold">{{ $i->name }}</td>
                        <td>{{ $i->quantity ?? 0 }}</td>
                        <td><span class="badge bg-{{ $i->available_count > 0 ? 'success' : 'danger' }}">{{ $i->available_count ?? 0 }}</span></td>
                        <td>{{ $i->booked_count ?? 0 }}</td>
                        <td>{{ $i->venue_type ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ url('edit_hotel/' . $i->id) }}" class="btn btn-sm btn-outline-warning"><i class="bx bx-edit-alt"></i></a>
                            <a href="{{ url('delete_hotel/' . $i->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this hotel?')"><i class="bx bx-trash"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endSection
