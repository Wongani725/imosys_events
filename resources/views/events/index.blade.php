@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Events</h2>
        <a href="{{ route('add_event') }}" class="btn btn-iia-blue"><i class="bx bx-plus"></i> Create Event</a>
    </div>

    @if(session('message'))
        <div class="alert alert-info alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Event ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th style="min-width:280px;">Configuration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $i)
                    <tr>
                        <td><code>{{ $i->event_id }}</code></td>
                        <td class="fw-semibold">{{ $i->event_name }}</td>
                        <td><span class="badge bg-info">{{ $i->event_type ?? '—' }}</span></td>
                        <td>{{ $i->start_date }} — {{ $i->end_date }}</td>
                        <td>{{ $i->event_venue }}</td>
                        <td>
                            <span class="badge bg-{{ $i->event_status === 'active' ? 'success' : 'secondary' }}">
                                {{ $i->event_status }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ url('edit_event/' . $i->event_id) }}" class="btn btn-sm btn-outline-warning" title="Edit Event"><i class="bx bx-edit-alt"></i></a>
                                <a href="{{ route('add_hotel', $i->event_id) }}" class="btn btn-sm btn-outline-primary" title="Hotels"><i class="bx bx-building"></i></a>
                                <a href="{{ route('view_participant_fees', $i->event_id) }}" class="btn btn-sm btn-outline-success" title="Fees"><i class="bx bx-money"></i></a>
                                <a href="{{ route('view_sessions', $i->event_id) }}" class="btn btn-sm btn-outline-info" title="Sessions"><i class="bx bx-time"></i></a>
                                <!-- <a href="{{ route('get-sponsors', $i->event_id) }}" class="btn btn-sm btn-outline-secondary" title="Sponsors"><i class="bx bx-star"></i></a> -->
                                <a href="{{ route('admin.attire-sizes.index') }}?event_id={{ $i->event_id }}" class="btn btn-sm btn-outline-dark" title="Attire Sizes"><i class="bx bx-ruler"></i></a>
                                <a href="{{ route('admin.documents.index', $i->event_id) }}" class="btn btn-sm btn-outline-info" title="Documents"><i class="bx bx-folder"></i></a>
                                <a href="{{ route('admin.master-meal-tags.index') }}?event_id={{ $i->event_id }}" class="btn btn-sm btn-outline-warning" title="Master Meal Tags"><i class="bx bx-dish"></i></a>
                                <!-- <a href="{{ route('get-terms') }}?event_id={{ $i->event_id }}" class="btn btn-sm btn-outline-secondary" title="Terms"><i class="bx bx-file"></i></a> -->
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('view_participants', $i->event_id) }}" class="btn btn-sm btn-iia-blue" title="View Participants"><i class="bx bx-user"></i> View</a>
                                <a href="{{ url('delete_event/' . $i->event_id) }}" class="btn btn-sm btn-outline-danger" title="Delete"
                                   onclick="return confirm('Delete event {{ $i->event_name }}?')"><i class="bx bx-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
