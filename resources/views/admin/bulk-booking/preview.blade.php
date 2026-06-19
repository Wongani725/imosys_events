@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Preview: Bulk Booking Import</h2>
        <a href="{{ route('admin.bulk-booking.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ count($validRows) }}</h3>
                    <small>Valid Rows</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger mb-0">{{ count($errorRows) }}</h3>
                    <small>Errors</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="text-info mb-0">MWK {{ number_format($totalAmount, 2) }}</h3>
                    <small>Total Cost</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Organization:</strong> {{ $orgName }} &nbsp;|&nbsp; <strong>Contact Email:</strong> {{ $contactEmail }}</p>
            <p><strong>Participants with Accommodation:</strong> {{ $peopleWithAcc }}</p>
        </div>
    </div>

    {{-- Hotel Warnings --}}
    @if($hotelWarnings->count() > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Hotel Availability Warnings ({{ $hotelWarnings->count() }})</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">The following rows requested accommodation at a fully booked hotel. They will be created <strong>without accommodation</strong>.</p>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
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

    {{-- Valid Rows Table --}}
    @if(count($validRows) > 0)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-check-circle"></i> Valid Rows ({{ count($validRows) }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Errors ({{ count($errorRows) }}) — These rows will be skipped</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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

    {{-- Confirm / Cancel --}}
    <form method="POST" action="{{ route('admin.bulk-booking.confirm') }}" class="d-flex gap-2 justify-content-center">
        @csrf
        <a href="{{ route('admin.bulk-booking.index') }}" class="btn btn-lg btn-outline-danger px-5">
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