@extends('layouts.web_app')

@section('title', 'Preview Bulk Booking')

@push('styles')
<style>
    .summary-card { border-radius: 12px; border: none; }
    .summary-card .card-body { padding: 1.25rem; }
    .summary-number { font-size: 2rem; font-weight: 700; }
    .table th { background: #006198; color: #fff; font-size: 0.85rem; }
    .table td { font-size: 0.85rem; vertical-align: middle; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-file-import me-2" style="color:#006198;"></i>Preview: Bulk Booking Import</h4>
        <a href="{{ route('member-dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card summary-card border-success h-100">
                <div class="card-body text-center">
                    <div class="summary-number text-success">{{ count($validRows) }}</div>
                    <small class="text-muted">Valid Rows</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card border-danger h-100">
                <div class="card-body text-center">
                    <div class="summary-number text-danger">{{ count($errorRows) }}</div>
                    <small class="text-muted">Errors</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card border-info h-100">
                <div class="card-body text-center">
                    <div class="summary-number text-info">MWK {{ number_format($totalAmount, 2) }}</div>
                    <small class="text-muted">Total Cost</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Org Details --}}
    <div class="card mb-4" style="border-radius:12px;border:none;">
        <div class="card-body">
            <p class="mb-1"><strong>Organization:</strong> {{ $orgName }} &nbsp;|&nbsp; <strong>Contact Email:</strong> {{ $contactEmail }}</p>
            <p class="mb-0"><strong>Participants with Accommodation:</strong> {{ $peopleWithAcc }}</p>
        </div>
    </div>

    {{-- Hotel Warnings --}}
    @if($hotelWarnings->count() > 0)
        <div class="card mb-4 border-warning" style="border-radius:12px;">
            <div class="card-header bg-warning text-dark" style="border-radius:12px 12px 0 0;">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Hotel Availability Warnings ({{ $hotelWarnings->count() }})</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">The following rows requested accommodation at a fully booked hotel. They will be created <strong>without accommodation</strong>.</p>
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Requested Hotel</th>
                            <th>Issue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hotelWarnings as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r['name'] }}</td>
                                <td>{{ $r['hotel_name'] }}</td>
                                <td class="text-danger fw-semibold">{{ $r['warning'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Valid Rows --}}
    @if(count($validRows) > 0)
        <div class="card mb-4" style="border-radius:12px;border:none;">
            <div class="card-header bg-success text-white" style="border-radius:12px 12px 0 0;">
                <h5 class="mb-0"><i class="fas fa-check-circle me-1"></i> Valid Rows ({{ count($validRows) }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Acc</th>
                                <th>Hotel</th>
                                <th>Spouse</th>
                                <th>Extras</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($validRows as $i => $r)
                                <tr class="{{ $r['warning'] ? 'table-warning' : '' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $r['event_name'] }}</td>
                                    <td>{{ $r['name'] }}</td>
                                    <td>{{ $r['email'] }}</td>
                                    <td>{{ $r['member_status'] }}</td>
                                    <td>
                                        @if($r['warning'])
                                            <span class="text-danger fw-bold">Removed</span>
                                        @else
                                            {{ $r['accommodation'] ? 'Yes' : 'No' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($r['accommodation'])
                                            {{ $r['hotel_name'] }}
                                        @elseif($r['warning'])
                                            <span class="text-danger"><s>{{ $r['hotel_name'] }}</s></span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $r['spouse_included'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $r['extras'] }}</td>
                                    <td>MWK {{ number_format($r['total_cost'], 2) }}</td>
                                    <td>
                                        @if($r['warning'])
                                            <span class="badge bg-danger" title="{{ $r['warning'] }}">Hotel Full</span>
                                        @else
                                            <span class="badge bg-success">Ready</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="9" class="text-end">Total</td>
                                <td>MWK {{ number_format($totalAmount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Errors --}}
    @if(count($errorRows) > 0)
        <div class="card mb-4" style="border-radius:12px;border:none;">
            <div class="card-header bg-danger text-white" style="border-radius:12px 12px 0 0;">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle me-1"></i> Errors ({{ count($errorRows) }}) &mdash; These rows will be skipped</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Issue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errorRows as $err)
                            <tr>
                                <td>{{ $err['row'] }}</td>
                                <td>{{ $err['name'] ?: '-' }}</td>
                                <td>{{ $err['email'] ?: '-' }}</td>
                                <td class="text-danger">{{ $err['issue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <form method="POST" action="{{ route('member.bulk-booking.confirm') }}" class="d-flex gap-2 justify-content-center">
        @csrf
        <a href="{{ route('member-dashboard') }}" class="btn btn-lg btn-outline-danger px-5">
            <i class="fas fa-times"></i> Cancel
        </a>
        @if(count($validRows) > 0)
            <button type="submit" class="btn btn-lg btn-success px-5">
                <i class="fas fa-check"></i> Confirm Import ({{ count($validRows) }} rows)
            </button>
        @else
            <button type="submit" class="btn btn-lg btn-success px-5" disabled>
                <i class="fas fa-check"></i> Nothing to Import
            </button>
        @endif
    </form>
</div>
@endsection