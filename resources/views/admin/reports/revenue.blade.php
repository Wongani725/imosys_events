@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Revenue Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => 'revenue', 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'revenue', 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'revenue', 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #006198;">
                <div class="card-body">
                    <h5 class="fw-bold text-primary">MWK {{ number_format($totalInvoiced, 0) }}</h5>
                    <small class="text-muted">Total Invoiced</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #28a745;">
                <div class="card-body">
                    <h5 class="fw-bold text-success">MWK {{ number_format($totalPaid, 0) }}</h5>
                    <small class="text-muted">Total Paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #dc3545;">
                <div class="card-body">
                    <h5 class="fw-bold text-danger">MWK {{ number_format($totalBalance, 0) }}</h5>
                    <small class="text-muted">Outstanding Balance</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Per-booking breakdown --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Per-Booking Breakdown</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Name</th>
                        <th class="text-end">Invoiced</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td><code>{{ $b->booking_reference ?? $b->bookingID }}</code></td>
                        <td>{{ $b->name }}</td>
                        <td class="text-end">{{ number_format($b->total_cost, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($b->amount_paid, 2) }}</td>
                        <td class="text-end text-{{ $b->balance > 0 ? 'danger' : 'success' }}">{{ number_format($b->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
