@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Meals Report</h2>
        <div>
            <a href="{{ route('admin.reports.export.index', ['type' => 'meals', 'event_id' => $selectedEvent->event_id, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success"><i class="bx bxs-file"></i> Excel</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'meals', 'event_id' => $selectedEvent->event_id, 'format' => 'csv']) }}" class="btn btn-sm btn-info text-white"><i class="bx bxs-file"></i> CSV</a>
            <a href="{{ route('admin.reports.export.index', ['type' => 'meals', 'event_id' => $selectedEvent->event_id, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf"></i> PDF</a>
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

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #006198;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-1">{{ $totalCoupons }} @if($masterCoupons > 0)<small class="text-muted fw-normal"> ({{ $masterCoupons }} master)</small>@endif</h3>
                    <small class="text-muted">Coupons Issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #17a2b8;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-1">{{ $totalOffered }} @if($masterMeals > 0)<small class="text-muted fw-normal"> ({{ $masterMeals }} master)</small>@endif</h3>
                    <small class="text-muted">Meals Offered</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #28a745;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-1">{{ $totalRedeemed }} @if($masterRedeemed > 0)<small class="text-muted fw-normal"> ({{ $masterRedeemed }} master)</small>@endif</h3>
                    <small class="text-muted">Meals Redeemed</small>
                </div>
            </div>
        </div>
    </div>

    @if($totalOffered > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Overall Redemption Rate</span>
                <span class="fw-bold text-success">{{ round(($totalRedeemed / $totalOffered) * 100, 1) }}%</span>
            </div>
            <div class="progress mt-2" style="height:10px;">
                <div class="progress-bar bg-success" style="width: {{ ($totalRedeemed / $totalOffered) * 100 }}%"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Detailed Scan Breakdown --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span>Meal Scans by Day, Hotel & Period</span>
            <span class="badge bg-primary">{{ $mealScans->count() }} entries</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Period</th>
                        <th>Hotel</th>
                        <th class="text-end">Scans</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mealScans as $scan)
                    <tr>
                        <td>{{ $scan->date ?? '—' }}</td>
                        <td><span class="badge bg-{{ $scan->meal_period === 'Lunch' ? 'warning' : 'dark' }}">{{ $scan->meal_period }}</span></td>
                        <td>{{ $scan->hotel_name ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ $scan->scan_count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No meal scan data available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mealScans->count() > 0)
        <div class="card-footer text-muted small">Showing {{ $mealScans->count() }} scan records</div>
        @endif
    </div>
</div>
@endsection
