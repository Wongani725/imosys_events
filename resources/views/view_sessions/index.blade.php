@extends('layouts.app')

@section('title', 'Event Sessions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Event Sessions</h2>
        <div>
            <a href="{{ url('add_session/' . $event_id) }}" class="btn btn-iia-green me-2"><i class="bx bx-plus"></i> Add Session</a>
            <a href="{{ route('view_participants', $event_id) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back to Participants</a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Session ID</th>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $i)
                    <tr>
                        <td>{{ $i->session_id }}</td>
                        <td>{{ $i->session_date }}</td>
                        <td>{{ $i->start_time }}</td>
                        <td>{{ $i->end_time }}</td>
                        <td>{{ $i->description }}</td>
                        <td class="text-center">
                            <a href="{{ url('edit_session/' . $i->session_id) }}" class="btn btn-sm btn-outline-warning"><i class="bx bx-edit-alt"></i></a>
                            <a href="{{ url('delete_session/' . $i->session_id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this session?')"><i class="bx bx-trash"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
