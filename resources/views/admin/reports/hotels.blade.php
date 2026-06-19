@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Hotel Occupancy Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => 'hotels', 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'hotels', 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'hotels', 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
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

    {{-- Hotel Summary Cards --}}
    <div class="row g-3 mb-4">
        @foreach($hotels as $h)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-iia-blue">{{ $h->name }}</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Rooms</span><span class="fw-bold">{{ $h->quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Booked</span><span class="fw-bold text-success">{{ $h->booked_count }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Available</span><span class="fw-bold text-{{ $h->available_count > 0 ? 'primary' : 'danger' }}">{{ $h->available_count }}</span>
                    </div>
                    @if($h->quantity > 0)
                    <div class="progress mt-2" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:{{ ($h->booked_count / $h->quantity) * 100 }}%"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Who Sleeps Where (Confirmed only) --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bx bx-bed"></i> Who Sleeps Where — Confirmed Bookings</span>
            <span class="badge bg-primary">{{ $sleepers->count() }} guests</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Hotel</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sleepers as $s)
                    <tr>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td>{{ $s->email }}</td>
                        <td>{{ $s->company ?? '—' }}</td>
                        <td><span class="badge bg-info">{{ $s->hotel->name ?? '—' }}</span></td>
                        <td><code>{{ $s->room_number ?? '—' }}</code></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No confirmed accommodation bookings.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
