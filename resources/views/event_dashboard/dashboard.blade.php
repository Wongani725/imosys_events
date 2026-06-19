@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Event Filter + Title --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <div class="dropdown">
                <button class="btn dropdown-toggle text-white" style="background-color:#006198;" type="button" data-bs-toggle="dropdown">
                    {{ $eventName }}
                </button>
                <ul class="dropdown-menu">
                    @foreach($events as $ev)
                        <li><a class="dropdown-item" href="{{ route('dashboard-two', ['event_id' => $ev->event_id]) }}">{{ $ev->event_name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <h4 class="mb-0 text-primary fw-bold">{{ $eventName }}</h4>
        </div>
        <div class="col-md-4 text-end">
            <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #006198;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-1">{{ $totalBookers }}</h3>
                    <small class="text-muted">Total Bookings</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #28a745;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-1">{{ $confirmedBookers }}</h3>
                    <small class="text-muted">Confirmed</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ffc107;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-1">{{ $pendingPayment }}</h3>
                    <small class="text-muted">Pending Payment</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d;">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-secondary mb-1">{{ $declinedBookers + $cancelledBookers }}</h3>
                    <small class="text-muted">Declined / Cancelled</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue + Participants Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Revenue Summary</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="fw-bold text-primary">MWK {{ number_format($totalInvoiced, 0) }}</h5>
                            <small class="text-muted">Invoiced</small>
                        </div>
                        <div class="col-4">
                            <h5 class="fw-bold text-success">MWK {{ number_format($totalPaid, 0) }}</h5>
                            <small class="text-muted">Paid</small>
                        </div>
                        <div class="col-4">
                            <h5 class="fw-bold text-danger">MWK {{ number_format($outstandingBalance, 0) }}</h5>
                            <small class="text-muted">Balance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Participants (Initial Registration)</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h5 class="fw-bold">{{ $totalParticipants }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-3">
                            <h5 class="fw-bold text-primary">{{ $memberParticipants }}</h5>
                            <small class="text-muted">Members</small>
                        </div>
                        <div class="col-3">
                            <h5 class="fw-bold text-warning">{{ $nonMemberParticipants }}</h5>
                            <small class="text-muted">Non-Members</small>
                        </div>
                        <div class="col-3">
                            <h5 class="fw-bold text-info">{{ $walkinParticipants }}</h5>
                            <small class="text-muted">Walk-ins</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hotel Occupancy + Meals Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Hotel Occupancy</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Rooms</span><span class="fw-bold">{{ $totalRooms }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Booked</span><span class="fw-bold text-success">{{ $bookedRooms }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Available</span><span class="fw-bold text-{{ $availableRooms > 0 ? 'primary' : 'danger' }}">{{ $availableRooms }}</span>
                    </div>
                    @if($totalRooms > 0)
                    <div class="progress mt-2" style="height:8px;">
                        <div class="progress-bar bg-success" style="width:{{ ($bookedRooms/$totalRooms)*100 }}%"></div>
                    </div>
                    <small class="text-muted">{{ round(($bookedRooms/$totalRooms)*100) }}% occupancy</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Meals</div>
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary">{{ $totalMealCoupons }} @if($masterCoupons > 0)<small class="text-muted fw-normal"> ({{ $masterCoupons }} master)</small>@endif</h3>
                    <small class="text-muted">Coupons Issued</small>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Meals Offered</span><span class="fw-bold">{{ $totalMealsOffered }} @if($masterMeals > 0)<small class="text-muted fw-normal">({{ $masterMeals }} master)</small>@endif</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Meals Redeemed</span><span class="fw-bold text-success">{{ $totalMealsRedeemed }} @if($masterRedeemed > 0)<small class="text-muted fw-normal">({{ $masterRedeemed }} master)</small>@endif</span>
                    </div>
                    @if($totalMealsOffered > 0)
                    <div class="progress mt-2" style="height:8px;">
                        <div class="progress-bar bg-success" style="width:{{ ($totalMealsRedeemed/$totalMealsOffered)*100 }}%"></div>
                    </div>
                    <small class="text-muted">{{ round(($totalMealsRedeemed/$totalMealsOffered)*100) }}% redeemed</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Session Attendance</div>
                <div class="card-body text-center">
                    @php
                        $totalMorning = $sessionAttendance->where('description', 'Morning')->sum('attendee_count');
                        $totalAfternoon = $sessionAttendance->where('description', 'Afternoon')->sum('attendee_count');
                        $totalSessionsAll = $sessionAttendance->sum('attendee_count');
                        $uniqueDates = $sessionAttendance->pluck('session_date')->unique()->count();
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span>Morning Sessions</span><span class="fw-bold text-primary">{{ $totalMorning }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Afternoon Sessions</span><span class="fw-bold text-warning">{{ $totalAfternoon }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Attendance</span><span class="fw-bold">{{ $totalSessionsAll }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Conference Days</span><span class="fw-bold">{{ $uniqueDates }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hotel Meals Redeemed + Session Attendance Table --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Hotel Meals Redeemed</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th class="text-center">Premium</th>
                                <th class="text-center">Extras</th>
                                <th class="text-center fw-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotelMealsRedeemed as $meal)
                            <tr>
                                <td>{{ $meal->hotel_name }}</td>
                                <td class="text-center">{{ $meal->premium_scans }}</td>
                                <td class="text-center">{{ $meal->extras_scans }}</td>
                                <td class="text-center fw-bold">{{ $meal->total_scans }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-muted text-center">No meal scans recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Session Attendance by Day</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Period</th>
                                <th class="text-center">Attendees</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessionAttendance as $sa)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sa->session_date)->format('d M') }}</td>
                                <td>{{ $sa->description ?? 'N/A' }}</td>
                                <td class="text-center fw-bold">{{ $sa->attendee_count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center">No attendance data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Hotels List --}}
    <div class="row g-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Hotels — Room Allocation</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Name</th><th>Total Rooms</th><th>Booked</th><th>Available</th><th>Occupancy</th></tr></thead>
                        <tbody>
                            @forelse($hotels as $h)
                            <tr>
                                <td>{{ $h->name }}</td>
                                <td>{{ $h->quantity }}</td>
                                <td class="text-success fw-bold">{{ $h->booked_count }}</td>
                                <td class="text-{{ $h->available_count > 0 ? 'primary' : 'danger' }} fw-bold">{{ $h->available_count }}</td>
                                <td>
                                    @if($h->quantity > 0)
                                        <div class="progress" style="height:6px;width:80px;">
                                            <div class="progress-bar bg-success" style="width:{{ ($h->booked_count/$h->quantity)*100 }}%"></div>
                                        </div>
                                        <small>{{ round(($h->booked_count/$h->quantity)*100) }}%</small>
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-muted text-center">No hotels</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
