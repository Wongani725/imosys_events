@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Participants Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => 'participants', 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'participants', 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'participants', 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
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
        <div class="col-md-3">
            <select name="filter" class="form-select" onchange="this.form.submit()">
                <option value="">All Participants</option>
                <option value="with_accommodation" {{ $currentFilter === 'with_accommodation' ? 'selected' : '' }}>With Accommodation</option>
                <option value="without_accommodation" {{ $currentFilter === 'without_accommodation' ? 'selected' : '' }}>Without Accommodation</option>
            </select>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ref Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Accommodation</th>
                        <th>Walk-in</th>
                        <th class="text-end">Meals</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $p)
                    <tr>
                        <td><code>{{ $p->reference_code }}</code></td>
                        <td class="fw-semibold">{{ $p->participant }}</td>
                        <td>{{ $p->email_address }}</td>
                        <td>{{ $p->phone_number ?? '—' }}</td>
                        <td>{{ $p->company_name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $p->status === 'Member' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
                        <td>{!! $p->accommodation ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-light text-dark">No</span>' !!}</td>
                        <td>{!! $p->is_walkin ? '<span class="badge bg-warning text-dark">Walk-in</span>' : '—' !!}</td>
                        <td class="text-end">{{ $p->meals ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No participants found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($data->count() > 0)
        <div class="card-footer text-muted small">Showing {{ $data->count() }} participant(s)</div>
        @endif
    </div>
</div>
@endsection
