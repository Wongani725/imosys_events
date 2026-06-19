@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Bulk Booking</h2>
        <div>
            <a href="{{ route('admin.bulk-booking.template') }}" id="download_template_btn" class="btn btn-success">
                <i class="fas fa-download"></i> Download Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        {{-- Import Section --}}
        <div class="col-md-5 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import Bookings</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Upload an Excel file with columns: <code>event</code>, <code>member_status</code>, <code>member_id</code>,
                        <code>name</code>, <code>email</code>, <code>phone</code>, <code>accommodation</code>,
                        <code>hotel_name</code>, <code>spouse_included</code>, <code>extras</code>, <code>attire_size</code>.
                        Each row specifies its own event. <a href="{{ route('admin.bulk-booking.template') }}" target="_blank">Download template</a>
                        for an example.
                    </p>

                    <form method="POST" action="{{ route('admin.bulk-booking.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                            <input type="text" name="organization_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                            <input type="email" name="contact_email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Excel File <span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx,.csv" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Import Bookings</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Batches Section --}}
        <div class="col-md-7 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Bulk Booking Batches</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $batches = DB::table('bookers')
                            ->where('booking_reference', 'LIKE', 'BULK-%')
                            ->whereNotIn('booking_status', ['Cancelled', 'Declined'])
                            ->select('booking_reference', 'company', DB::raw('COUNT(*) as people_count'), DB::raw('SUM(total_cost) as total_amount'))
                            ->groupBy('booking_reference', 'company')
                            ->orderByDesc(DB::raw('MAX(created_at)'))
                            ->get();
                    @endphp

                    @if($batches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Batch Ref</th>
                                        <th>Organization</th>
                                        <th>Events</th>
                                        <th>People</th>
                                        <th>Total (MWK)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $batch)
                                        @php
                                            $batchBookings = DB::table('bookers')
                                                ->join('events', 'bookers.event_id', '=', 'events.event_id')
                                                ->where('bookers.booking_reference', $batch->booking_reference)
                                                ->select('events.event_name')
                                                ->distinct()
                                                ->pluck('event_name');
                                            $eventNames = $batchBookings->implode(', ');
                                            $statuses = DB::table('bookers')
                                                ->where('booking_reference', $batch->booking_reference)
                                                ->whereNotIn('booking_status', ['Cancelled', 'Declined'])
                                                ->pluck('booking_status');
                                            $allPending = $statuses->every(fn($s) => $s === 'Pending Payment');
                                            $allConfirmed = $statuses->every(fn($s) => $s === 'Confirmed');

                                            $popBooking = DB::table('bookers')
                                                ->where('booking_reference', $batch->booking_reference)
                                                ->whereNotNull('proof_of_payment')
                                                ->first(['bookingID']);
                                        @endphp
                                        <tr>
                                            <td><code>{{ $batch->booking_reference }}</code></td>
                                            <td>{{ $batch->company }}</td>
                                            <td>{{ $eventNames }}</td>
                                            <td>{{ $batch->people_count }}</td>
                                            <td>{{ number_format($batch->total_amount, 2) }}</td>
                                            <td>
                                                @if($allPending)
                                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                                        @if($popBooking)
                                                            <a href="{{ route('admin.viewPoP', $popBooking->bookingID) }}" target="_blank" class="btn btn-sm btn-info text-white" title="View Proof of Payment">
                                                                <i class="fas fa-receipt"></i> POP
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled title="No POP uploaded">
                                                                <i class="fas fa-receipt text-muted"></i>
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#notifyModal{{ \Illuminate\Support\Str::slug($batch->booking_reference) }}" title="Send Notification">
                                                            <i class="fas fa-bell"></i> Notify
                                                        </button>
                                                        <form action="{{ route('admin.bulk-booking.batches.approve', $batch->booking_reference) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve all {{ $batch->people_count }} bookings in this batch?')">
                                                                <i class="fas fa-check"></i> Approve All
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('admin.bulk-booking.batches.invoice', $batch->booking_reference) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-invoice"></i> Invoice
                                                        </a>
                                                    </div>
                                                @elseif($allConfirmed)
                                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                                        @if($popBooking)
                                                            <a href="{{ route('admin.viewPoP', $popBooking->bookingID) }}" target="_blank" class="btn btn-sm btn-info text-white" title="View Proof of Payment">
                                                                <i class="fas fa-receipt"></i> POP
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled title="No POP uploaded">
                                                                <i class="fas fa-receipt text-muted"></i>
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#notifyModal{{ \Illuminate\Support\Str::slug($batch->booking_reference) }}" title="Send Notification">
                                                            <i class="fas fa-bell"></i> Notify
                                                        </button>
                                                        <span class="badge bg-success">Approved</span>
                                                        <a href="{{ route('admin.bulk-booking.batches.invoice', $batch->booking_reference) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-invoice"></i> Invoice
                                                        </a>
                                                    </div>
                                                @else
                                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                                        @if($popBooking)
                                                            <a href="{{ route('admin.viewPoP', $popBooking->bookingID) }}" target="_blank" class="btn btn-sm btn-info text-white" title="View Proof of Payment">
                                                                <i class="fas fa-receipt"></i> POP
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled title="No POP uploaded">
                                                                <i class="fas fa-receipt text-muted"></i>
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#notifyModal{{ \Illuminate\Support\Str::slug($batch->booking_reference) }}" title="Send Notification">
                                                            <i class="fas fa-bell"></i> Notify
                                                        </button>
                                                        <span class="badge bg-warning">Mixed</span>
                                                        <a href="{{ route('admin.bulk-booking.batches.invoice', $batch->booking_reference) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-invoice"></i> Invoice
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-4 text-muted">No bulk booking batches found.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Notification Modals --}}
@foreach($batches as $batch)
    @php $modalId = \Illuminate\Support\Str::slug($batch->booking_reference); @endphp
    <div class="modal fade" id="notifyModal{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.bulk-booking.batches.notify', $batch->booking_reference) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notification — {{ $batch->booking_reference }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="title" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
