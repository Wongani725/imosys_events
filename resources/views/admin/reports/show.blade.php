@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">{{ ucfirst($type) }} Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => $type, 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => $type, 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => $type, 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @forelse($data as $row)
                                @foreach($row as $key => $val)
                                    <th>{{ is_string($key) ? ucwords(str_replace('_', ' ', $key)) : '#' }}</th>
                                @endforeach
                                @break
                            @empty
                                <th>No data</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            @foreach($row as $val)
                                <td>{{ $val ?? '—' }}</td>
                            @endforeach
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-4 text-muted">No data available for this report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(count($data) > 0)
            <div class="card-footer text-muted small text-end">Showing {{ count($data) }} record(s)</div>
            @endif
        </div>
    </div>
</div>
@endsection
