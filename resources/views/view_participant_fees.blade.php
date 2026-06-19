@extends('layouts.app')

@section('title', 'Event Fees')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Participant Fees</h2>
        <div>
            <a href="{{ url('add_fees/' . $event_id) }}" class="btn btn-iia-green me-2"><i class="bx bx-plus"></i> Add Fee</a>
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
                        <th>ID</th>
                        <th>Participant Category</th>
                        <th>Member Type</th>
                        <th>Accommodation</th>
                        <th>Price (MWK)</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fees as $i)
                    <tr>
                        <td>{{ $i->id }}</td>
                        <td class="fw-semibold">{{ $i->status }}</td>
                        <td>{{ $i->member_type ?? '—' }}</td>
                        <td>{{ $i->accommodation ? 'Yes' : 'No' }}</td>
                        <td>{{ number_format($i->price, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ url('edit_fees/' . $i->id) }}" class="btn btn-sm btn-outline-warning"><i class="bx bx-edit-alt"></i></a>
                            <a href="{{ url('delete_fees/' . $i->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this fee?')"><i class="bx bx-trash"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
