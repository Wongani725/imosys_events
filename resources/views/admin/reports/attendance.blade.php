@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Attendance Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => 'attendance', 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'attendance', 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'attendance', 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
            <a href="{{ route('admin.reports.index', ['event_id' => $selectedEvent->event_id]) }}" class="btn btn-sm btn-outline-secondary ms-2"><i class="bx bx-arrow-back"></i> Back</a>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="event_id" class="form-select" onchange="this.form.submit()">
                @foreach($events as $ev)
                    <option value="{{ $ev->event_id }}" {{ $selectedEvent->event_id === $ev->event_id ? 'selected' : '' }}>{{ $ev->event_name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span>Per-Session Breakdown</span>
            <span class="badge bg-primary">{{ $data->count() }} sessions</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Session</th>
                        <th class="text-end">Total</th>
                        <th class="text-end text-primary">Members</th>
                        <th class="text-end text-warning">Non-Members</th>
                        <th class="text-end">% Members</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $a)
                    <tr>
                        <td>{{ $a->session_date }}</td>
                        <td>{{ $a->description ?? 'N/A' }}</td>
                        <td class="text-end fw-bold">{{ $a->total }}</td>
                        <td class="text-end text-primary fw-bold">{{ $a->members }}</td>
                        <td class="text-end text-warning fw-bold">{{ $a->non_members }}</td>
                        <td class="text-end">{{ $a->total > 0 ? round(($a->members / $a->total) * 100, 1) . '%' : '0%' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No attendance data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
