@extends('layouts.app')

@section('title', env('APP_NAME').' | Bookings')

@section('vendor-css')

<link rel="stylesheet" href="{{ asset('cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('cms/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
<link rel="stylesheet" href="{{ asset('cms/vendor/libs/select2/select2.css') }}" />

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

@endsection

@section('page-css')

<style>

    body{
        background:#f4f7fb;
        font-family:'Poppins',sans-serif;
    }

    .card{
        border:none;
        border-radius:16px;
        box-shadow:0 2px 12px rgba(0,0,0,0.05);
    }

    .badge-status{
        font-size:12px;
        padding:6px 10px;
        border-radius:30px;
    }

    .table th{
        white-space:nowrap;
    }

    .action-btn{
        margin-bottom:4px;
    }

    .modal-content {
        background-color: #fff !important;
        border: none !important;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15) !important;
    }
    .modal-header, .modal-body, .modal-footer {
        background-color: #fff !important;
    }
    .modal-backdrop {
        opacity: 0.5 !important;
    }
    .modal-dialog {
        z-index: 1055 !important;
    }

    .tab-pane { display: none; }
    .tab-pane.active.show { display: block; }

</style>

@endsection

@section('content')

<div class="container-fluid py-4">

    <ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab">
                <i class="fas fa-user me-1"></i> Individual Bookings
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab 1: Individual Bookings --}}
        <div class="tab-pane active" id="individual" role="tabpanel">

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Booking Management</h4>
                    <p class="text-muted mb-0">Manage all event bookings</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back"></i> Dashboard
                </a>
            </div>

            {{-- FILTER --}}
            <form action="{{ route('get-bookers') }}" method="GET" class="row g-2 mb-4">

                <div class="col-md-3">
                    <select name="event_id" class="form-select" onchange="this.form.submit()">
                        @foreach($events as $event)
                            <option value="{{ $event->event_id }}"
                                {{ $selectedEventId == $event->event_id ? 'selected' : '' }}>
                                {{ $event->event_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Pending (no confirmed)</option>
                        <option value="all" {{ ($statusFilter ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Pending Payment" {{ $statusFilter === 'Pending Payment' ? 'selected' : '' }}>Pending Payment</option>
                        <option value="Confirmed" {{ $statusFilter === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Declined" {{ $statusFilter === 'Declined' ? 'selected' : '' }}>Declined</option>
                        <option value="Cancelled" {{ $statusFilter === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search name, email, company...">
                        <button class="btn btn-iia-blue" type="submit"><i class="bx bx-search"></i></button>
                    </div>
                </div>

            </form>

            {{-- ALERTS --}}
            @if(session('success'))

                <div class="alert alert-success">
                    {{ session('success') }}
                </div>

            @endif

            @if(session('error'))

                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>

            @endif

            {{-- TABLE --}}
            <div class="table-responsive">

                <table id="bookingsTable"
                       class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                    <tr>

                        <th>#</th>

                        <th>Event ID</th>

                        <th>Name</th>

                        <th>Booking ID</th>

                        <th>Email</th>

                        <th>Status</th>

                        <th width="280">
                            Actions
                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    @foreach($bookers as $booker)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $booker->event_id }}
                            </td>

                            <td>
                                {{ $booker->name }}
                            </td>

                            <td>
                                {{ $booker->bookingID }}
                            </td>

                            <td>
                                {{ $booker->email }}
                            </td>

                            <td>

                                <span class="badge bg-{{ $booker->status_color }} badge-status">

                                    {{ $booker->booking_status }}

                                </span>

                            </td>

                            <td>

                                <div class="d-flex flex-wrap gap-1">

                                    {{-- VIEW DETAILS --}}
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewDetailsModal{{ $booker->bookingID }}"
                                            title="View Details">

                                        <i class="fa-solid fa-eye"></i>

                                    </button>

                                    {{-- EDIT --}}
                                    <button class="btn btn-sm btn-warning text-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $booker->bookingID }}"
                                            title="Edit Booking">

                                        <i class="fa-solid fa-pen-to-square"></i>

                                    </button>

                                    {{-- INVOICE --}}
                                    <a href="{{ route('admin.invoice', $booker->bookingID) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Invoice">

                                        <i class="fa-solid fa-file-invoice"></i>

                                    </a>

                                    {{-- UPLOAD POP --}}
                                    <button class="btn btn-sm btn-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#popModal{{ $booker->bookingID }}"
                                            title="Upload Proof of Payment">

                                        <i class="fa-solid fa-upload"></i>

                                    </button>

                                    {{-- VIEW POP --}}
                                    @if($booker->proof_of_payment)
                                        <a href="{{ route('admin.viewPoP', $booker->bookingID) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-info text-white"
                                           title="View Proof of Payment">
                                            <i class="fa-solid fa-receipt"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary"
                                                disabled
                                                title="No Proof of Payment uploaded">
                                            <i class="fa-solid fa-receipt text-muted"></i>
                                        </button>
                                    @endif

                                    {{-- APPROVE --}}

                                     <form action="{{ url('/bookers/' . $booker->bookingID . '/approve') }}" method="POST" style="display:inline;">
                                        @csrf
                                        @php
                                            $approveTitle = 'Approve Booking';
                                            if ($booker->credit_applied > 0) $approveTitle .= ' (Credit: MWK '.number_format($booker->credit_applied, 0).')';
                                            if ($booker->debt_applied > 0) $approveTitle .= ' (Debt: MWK '.number_format($booker->debt_applied, 0).')';
                                        @endphp
                                        <button type="submit" 
                                            class="btn btn-sm btn-success"
                                            title="{{ $approveTitle }}">

                                        <i class="fa-solid fa-check"></i>

                                    </button>
                                    </form>

                                    {{-- DECLINE --}}
                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#declineModal{{ $booker->bookingID }}"
                                            title="Decline Booking">

                                        <i class="fa-solid fa-xmark"></i>

                                    </button>

                                    {{-- ENTER PAYMENT --}}
                                    <button class="btn btn-sm btn-info text-white"
                                            data-bs-toggle="modal"
                                            data-bs-target="#paymentModal{{ $booker->bookingID }}"
                                            title="Record Payment">

                                        <i class="fa-solid fa-money-bill"></i>

                                    </button>

                                    {{-- CANCEL --}}
                                    <button class="btn btn-sm btn-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelModal{{ $booker->bookingID }}"
                                            title="Cancel Booking">

                                        <i class="fa-solid fa-ban"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                {{ $bookers->links() }}

            </div>

        </div>

    </div>

    {{-- ======================== --}}
    {{-- ALL MODALS OUTSIDE TABLE --}}
    {{-- ======================== --}}
    @foreach($bookers as $booker)

        {{-- VIEW DETAILS MODAL --}}
        <div class="modal fade"
             id="viewDetailsModal{{ $booker->bookingID }}"
             tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Booking Details -  {{ $booker->bookingID }}
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <div class="row">

                             <div class="col-md-6 mb-3">

                                <strong>Member Type:</strong><br>

                                {{ $booker->member_type }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Name:</strong><br>

                                {{ $booker->name }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Email:</strong><br>

                                {{ $booker->email }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Phone:</strong><br>

                                {{ $booker->phone_number }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Company:</strong><br>

                                {{ $booker->company }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Status:</strong><br>

                                {{ $booker->booking_status }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Total Cost:</strong><br>

                                MWK {{ number_format($booker->total_cost,2) }}

                            </div>

                             <div class="col-md-6 mb-3">

                                <strong>Accommodation:</strong><br>

                                {{ $booker->accommodation ? 'Yes' : 'No' }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Hotel:</strong><br>

                                {{ $booker->hotel->name ?? 'N/A' }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Spouse Included:</strong><br>

                                {{ $booker->spouse_included ? 'Yes' : 'No' }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Extras:</strong><br>

                                {{ $booker->extras }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Attire Size:</strong><br>

                                {{ $booker->attireSize->name ?? 'N/A'}}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Amount Paid:</strong><br>

                                MWK {{ number_format($booker->amount_paid,2) }}

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Balance:</strong><br>

                                MWK {{ number_format($booker->balance,2) }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- INVOICE MODAL --}}
        <div class="modal fade"
             id="invoiceModal{{ $booker->bookingID }}"
             tabindex="-1">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Invoice
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body text-center">

                        @php
                            $hasSibling = $booker->booking_reference && \App\Models\Bookers::where('booking_reference', $booker->booking_reference)
                                ->where('bookingID', '!=', $booker->bookingID)
                                ->exists();
                        @endphp

                        <a href="{{ route('admin.invoice', $booker->bookingID) }}"
                           target="_blank"
                           class="btn btn-primary">

                            Open Invoice

                        </a>

                        @if($hasSibling)
                            <p class="text-muted small mt-2">
                                <i class="fa-solid fa-link"></i> Consolidated invoice for all events
                            </p>
                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- DECLINE MODAL --}}
        <div class="modal fade" id="declineModal{{ $booker->bookingID }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('bookings.decline', $booker->bookingID) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Decline Booking</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to decline <strong>{{ $booker->name }}</strong>'s booking?</p>
                        <div class="mb-3">
                            <label class="form-label">Reason for Declining <span class="text-danger">*</span></label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-x-circle"></i> Decline & Send Email</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- CANCEL MODAL --}}
        <div class="modal fade" id="cancelModal{{ $booker->bookingID }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('bookings.cancel', $booker->bookingID) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">Cancel Booking</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel <strong>{{ $booker->name }}</strong>'s booking?</p>
                        <div class="mb-3">
                            <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-dark"><i class="bx bx-x-circle"></i> Cancel & Send Email</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PAYMENT MODAL --}}
        <div class="modal fade" id="paymentModal{{ $booker->bookingID }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('bookings.enter-payment', $booker->bookingID) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Record Payment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Booking:</strong> {{ $booker->name }} ({{ $booker->bookingID }})</p>
                        <p><strong>Total Cost:</strong> MWK {{ number_format($booker->total_cost, 2) }}</p>
                        <p><strong>Amount Paid:</strong> MWK {{ number_format($booker->amount_paid, 2) }}</p>
                        <p><strong>Balance:</strong> MWK {{ number_format($booker->balance, 2) }}</p>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Amount to Record <span class="text-danger">*</span></label>
                            <input type="number" name="amount_paid" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt Number</label>
                            <input type="text" name="receipt_number" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-info text-white"><i class="bx bx-check"></i> Record Payment</button>
                    </div>
                </form>
            </div>
        </div>

    @endforeach

</div> {{-- end individual tab-pane --}}

</div> {{-- end tab-content --}}

</div> {{-- end container-fluid --}}

@endsection

@section('page-js')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    $(document).ready(function () {

        // Init modals with static backdrop so they don't close on click outside
        $('.modal').each(function() {
            var modal = new bootstrap.Modal(this, {
                backdrop: 'static',
                keyboard: false
            });
            // Wire close/dismiss buttons manually
            $(this).find('[data-bs-dismiss="modal"]').on('click', function() {
                modal.hide();
            });
        });

        // Manual tab switching
        $('#bookingTabs button').on('click', function() {
            var target = $(this).data('bs-target');
            $('#bookingTabs button').removeClass('active');
            $(this).addClass('active');
            $('.tab-pane').removeClass('active show');
            $(target).addClass('active show');
        });

    });

</script>

@endsection