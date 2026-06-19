@extends('layouts.web_app')

@section('title', 'Participant Dashboard')

@push('styles')

    <style>

        body{
            background: url('https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?w=1920&q=80') center/cover no-repeat fixed;
        }

        .dashboard-card{
            border:none;
            border-radius:16px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
        }

        .event-image{
            height:220px;
            object-fit:cover;
        }

        .summary-box{
            position:sticky;
            top:20px;
        }

        .price-text{
            font-size:32px;
            font-weight:700;
            color:#97D700;
        }

        .step-circle{
            width:42px;
            height:42px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            color:white;
            font-weight:bold;
        }

        .sponsor-image{
            height:120px;
            object-fit:contain;
        }

        .event-badge{
            background:#fff3cd;
            color:#856404;
            padding:6px 12px;
            border-radius:30px;
            font-size:12px;
            font-weight:600;
        }

        .terms-checkbox {
            border: 2px solid #006198 !important;
            box-shadow: none !important;
        }
        .terms-checkbox:checked {
            background-color: #006198;
            border-color: #006198;
        }

        .btn-theme-blue {
            background-color: #006198;
            border-color: #006198;
            color: #fff;
        }
        .btn-theme-blue:hover {
            background-color: #004d7a;
            border-color: #004d7a;
            color: #fff;
        }
        .btn-theme-green {
            background-color: #97D700;
            border-color: #97D700;
            color: #fff;
        }
        .btn-theme-green:hover {
            background-color: #7ab800;
            border-color: #7ab800;
            color: #fff;
        }
        .badge-theme-green {
            background-color: #97D700;
            color: #fff;
        }

        .event-checkbox {
            border: 2px solid #006198 !important;
            box-shadow: none !important;
        }
        .event-checkbox:checked {
            background-color: #006198;
            border-color: #006198;
        }

    </style>

@endpush

@section('content')

    <div class="container py-4">

        {{-- PASSWORD SETUP --}}
        @if(!$user->password_set)

            <div class="alert alert-warning shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <strong>Complete Account Setup</strong><br>
                        Please set your password to secure your account.
                    </div>

                    <a href="{{ route('password.view') }}"
                       class="btn btn-dark">
                        Set Password
                    </a>

                </div>
            </div>

        @endif

        {{-- PROFILE --}}
        <div class="card dashboard-card mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        @php
                            $eventYear = $events->first() ? \Carbon\Carbon::parse($events->first()->start_date)->format('Y') : date('Y');
                        @endphp
                        <h3 class="mb-1">
                            Welcome {{ $user->name ?? $user->participant }} to the {{ $eventYear }} IIA Malawi registration platform
                        </h3>

                        <p class="mb-1 text-muted">
                            {{ $user->status }}
                        </p>

                        <small class="text-muted">
                            Member ID:
                            {{ $user->member_id ?? 'Non-Member' }}
                        </small>

                    </div>

                    <div class="col-md-4 text-center mt-3 mt-md-0">

                        <img src="{{ asset('images/alogo2.jpeg') }}"
                             alt="IIA Malawi"
                             style="height:50px;width:auto;"
                             class="mb-2">

                        @if($user->password_set)

                            <span class="badge badge-theme-green p-2 d-block">
                            Account Verified
                        </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- CURRENT BOOKING --}}
        @if($eventsWithBookings->count())

            <div class="card dashboard-card mb-4">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="mb-1">My Bookings</h5>

                        <small class="text-muted">
                            {{ $eventsWithBookings->sum(fn($e) => $e['bookings']->count()) }} Active Booking(s)
                        </small>

                    </div>

                    <div class="text-end">

                        @foreach($eventsWithBookings as $data)

                            @php
                                $event = $data['event'];
                                $bookings = $data['bookings'];
                                $latest = $bookings->sortByDesc('created_at')->first();
                            @endphp

                            <div class="mb-1">

                                <strong>
                                    {{ $event->event_name }}:
                                </strong>

                                <span class="badge
                            {{ $latest->booking_status === 'Confirmed' ? 'bg-success' : 'bg-warning text-dark' }}">

                            {{ $latest->booking_status }}

                        </span>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif

        {{-- SPONSORS --}}
        @if($sponsors->count())

            <div class="card dashboard-card mb-4">

                <div class="card-body">

                    <h5 class="mb-3">
                        Sponsors
                    </h5>

                    <div id="sponsorCarousel"
                         class="carousel slide"
                         data-bs-ride="carousel">

                        <div class="carousel-inner">

                            @foreach($sponsors as $index => $sponsor)

                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">

                                    <img src="{{ asset($sponsor->file_path) }}"
                                         class="d-block w-100 sponsor-image"
                                         alt="Sponsor">

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </div>

        @endif

        {{-- HOW TO REGISTER --}}
        <div class="card dashboard-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h5 class="mb-3"><i class="fas fa-info-circle" style="color:#006198;"></i> How to Register</h5>
                        <p class="mb-2">
                            <strong>Individual Registration:</strong> Check the box on <strong>one or both</strong> events below, then click
                            the <strong>"Register for Selected Events"</strong> button that appears at the bottom of the page.
                        </p>
                        <p class="mb-0">
                            <strong>Bulk Registration (for organizations):</strong> Download the template below,
                            fill in all required attendee details, then upload the completed file below.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('member.bulk-template', ['event_id' => $events->first()->event_id ?? '']) }}"
                           target="_blank"
                           class="btn btn-theme-green"
                           title="Bulk bookings are for organizations registering more than one (1) person for the Annual Conference and Governance Forum. Download the bulk booking template (Excel file). Fill in all required attendee details, then upload the completed file below.">
                            <i class="fas fa-download"></i> Download Bulk Booking Template
                        </a>
                        <button type="button" class="btn btn-theme-blue mt-2" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                            <i class="fas fa-upload"></i> Upload Completed Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Upload Modal --}}
        <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUploadModalLabel"><i class="fas fa-upload me-2"></i>Bulk Booking Upload</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('member.bulk-booking.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
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
                                <small class="text-muted">Use the template above to format your data.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-theme-blue"><i class="fas fa-eye"></i> Preview</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- EVENTS --}}
        <div class="row">

            @foreach($events as $event)

                @php
                    $userBooking = $bookingsByEvent[$event->event_id][0] ?? null;

                    $activeStatuses = ['Pending', 'Pending Payment', 'Approved'];
                    $bookingStart = $event->booking_start_time;
                    $bookingEnd = $event->booking_end_time;
                    $eventOver = now()->gte(\Carbon\Carbon::parse($event->end_date));

                    $totalSessions = 0;
                    $attendedSessions = 0;
                    $attendancePct = 0;
                    $canEvaluate = false;
                    $evaluationDone = false;
                    if ($userBooking && $userBooking->booking_status === 'Confirmed' && $userBooking->reference_code) {
                        $evaluationDone = DB::table('evaluation_submissions')
                            ->where('reference_code', $userBooking->reference_code)
                            ->where('event_id', $event->event_id)
                            ->exists();
                        if (!$evaluationDone) {
                            $totalSessions = DB::table('event_sessions')->where('event_id', $event->event_id)->count();
                            if ($totalSessions > 0) {
                                $attendedSessions = DB::table('attendance_registration')
                                    ->where('reference_code', $userBooking->reference_code)
                                    ->whereIn('session_id', function ($q) use ($event) {
                                        $q->select('session_id')->from('event_sessions')->where('event_id', $event->event_id);
                                    })
                                    ->count();
                                $attendancePct = ($attendedSessions / $totalSessions) * 100;
                                $canEvaluate = now()->gte(\Carbon\Carbon::parse($event->end_date)) && $attendancePct >= 70;
                            }
                        }
                    }
                @endphp

                <div class="col-lg-6 mb-4">

                    <div class="card dashboard-card h-100">

                        @if($event->image)

                            <img src="{{ asset($event->image) }}"
                                 class="card-img-top event-image">

                        @endif



                        <div class="card-body d-flex flex-column">

                            <div class="mb-2">

                            <span class="event-badge">
                                {{ ucfirst($event->event_type) }}
                            </span>

                            </div>

                            @if($userBooking)
                                <span class="badge bg-info mb-2">
                                Booked: {{ $userBooking->booking_status }}
                            </span>
                            @endif

                            <h4>
                                {{ $event->event_name }}
                            </h4>

                            <p class="text-muted">
                                {{ $event->theme }}
                            </p>

                            <p>
                                <strong>Date:</strong>

                                {{ \Carbon\Carbon::parse($event->start_date)->format('d M') }}
                                -
                                {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                            </p>

                            <p>
                                <strong>Venue:</strong>
                                {{ $event->event_venue }}
                            </p>

                            @if($userBooking)

                                @php
                                    $hasSibling = \App\Models\Bookers::where('booking_reference', $userBooking->booking_reference)
                                        ->where('bookingID', '!=', $userBooking->bookingID)
                                        ->exists();
                                @endphp

                                @if($userBooking->booking_status === 'Confirmed')
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <a href="{{ route('show-participant', ['reference_code' => $userBooking->reference_code]) }}"
                                           class="btn text-white flex-fill"
                                           style="background-color:#006198;">
                                            Download Name Tag
                                        </a>
                                        @if(!$hasSibling)
                                            <a href="{{ url('/get-invoice-pdf/' . $userBooking->bookingID) }}"
                                               class="btn flex-fill text-white"
                                               style="background-color:#6c757d;">
                                                <i class="fas fa-file-invoice"></i> Invoice
                                            </a>
                                        @else
                                            <a href="{{ url('/get-consolidated-invoice/' . $userBooking->bookingID) }}"
                                               class="btn flex-fill text-white"
                                               style="background-color:#6c757d;">
                                                <i class="fas fa-file-invoice"></i> Invoice
                                            </a>
                                        @endif
                                        @if($evaluationDone)
                                            <a href="{{ route('member.certificate', [$userBooking->reference_code, $userBooking->event_id]) }}"
                                               class="btn flex-fill text-white"
                                               style="background-color:#97D700;">
                                                <i class="fas fa-certificate"></i> View Certificate
                                            </a>
                                        @elseif($eventOver && $canEvaluate)
                                            <a href="{{ route('member.evaluation', $userBooking->event_id) }}"
                                               class="btn flex-fill text-white"
                                               style="background-color:#97D700;">
                                                <i class="fas fa-star"></i> Evaluate
                                            </a>
                                        @else
                                            <span title="@if($eventOver && $totalSessions > 0 && !$canEvaluate)Not eligible — {{ $attendedSessions }}/{{ $totalSessions }} sessions ({{ round($attendancePct) }}%). Need at least 70%.@elseif(!$eventOver)Evaluation opens after the event ends.Attend at least 70% of sessions to evaluate.@endif">
                                                <button disabled
                                                        class="btn flex-fill"
                                                        style="background:#ccc!important;color:#666!important;border:1px solid #bbb!important;cursor:not-allowed;opacity:0.7;pointer-events:none;">
                                                    <i class="fas fa-star"></i> Evaluate
                                                </button>
                                            </span>
                                            @if($eventOver && !$canEvaluate && $totalSessions > 0)
                                                <div class="text-danger small w-100 mt-1">
                                                    <i class="fas fa-times-circle"></i> {{ $attendedSessions }}/{{ $totalSessions }} sessions — {{ round($attendancePct) }}% attendance
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                @else
                                    <div class="d-flex gap-2">
                                        <a href="{{ url('/my-booking/' . $event->event_id) }}"
                                           class="btn flex-fill text-white"
                                           style="background:#97D700;">
                                            View Booking Details
                                        </a>
                                        @if($hasSibling)
                                            <a href="{{ url('/get-consolidated-invoice/' . $userBooking->bookingID) }}"
                                               class="btn text-white"
                                               style="background-color:#6c757d;">
                                                <i class="fas fa-file-invoice"></i> Invoice
                                            </a>
                                        @else
                                            <a href="{{ url('/get-invoice-pdf/' . $userBooking->bookingID) }}"
                                               class="btn text-white"
                                               style="background-color:#6c757d;">
                                                <i class="fas fa-file-invoice"></i> Invoice
                                            </a>
                                        @endif
                                    </div>
                                @endif

                            @else

                                @if(isset($bookingStart) && now()->lt($bookingStart))
                                    <button class="btn btn-secondary w-100" disabled>
                                        Booking Opens {{ $bookingStart->format('M d, Y H:i') }}
                                    </button>

                                @elseif(isset($bookingEnd) && now()->gt($bookingEnd))
                                    <button class="btn btn-secondary w-100" disabled>
                                        Booking Closed
                                    </button>

                                @else
                                    <div class="form-check mb-2">
                                        <input type="checkbox"
                                               class="form-check-input event-checkbox"
                                               id="event_cb_{{ $event->event_id }}"
                                               value="{{ $event->event_id }}"
                                               data-event-name="{{ $event->event_name }}">
                                        <label class="form-check-label fw-bold" for="event_cb_{{ $event->event_id }}">
                                            Include in my registration
                                        </label>
                                    </div>
                                @endif

                            @endif

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        {{-- FLOATING REGISTER BUTTON --}}
        <div id="floatingRegisterBar" class="fixed-bottom bg-white shadow p-3 border-top" style="display:none;z-index:1050;">
            <div class="container d-flex justify-content-between align-items-center">
                <span id="selectedCount" class="fw-bold">0 event(s) selected</span>
                <button class="btn text-white px-4"
                        style="background-color:#006198;"
                        data-bs-toggle="modal"
                        data-bs-target="#bookingModal"
                        id="openMultiBookingBtn">
                    Register for Selected Events
                </button>
            </div>
        </div>

    </div>

    {{-- BOOKING MODAL (Multi-Event) --}}
    <div class="modal fade"
         id="bookingModal"
         tabindex="-1">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <form method="POST"
                  action="{{ route('book') }}"
                  class="modal-content">

                @csrf

                <input type="hidden"
                       id="member_type"
                       value="{{ $user->status == 'Member' ? 'Member' : 'Non-Member' }}">

                <input type="hidden"
                       id="priceData"
                       value='@json($eventPrices)'>

                <input type="hidden"
                       id="memberCredit"
                       value="{{ $user->credit ?? 0 }}">

                <input type="hidden"
                       id="memberDebt"
                       value="{{ $user->debt ?? 0 }}">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Event Booking
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="row">

                        {{-- LEFT --}}
                        <div class="col-lg-8">

                            <div id="eventSectionsContainer">
                                {{-- Populated by JS with checked events --}}
                            </div>

                            {{-- ATTIRE SIZE (shared) --}}
                            <div class="card mb-3">

                                <div class="card-body">

                                    <h5>
                                        Attire Size
                                    </h5>

                                    <select
                                            name="attire_size_id"
                                            id="attire_size"
                                            class="form-select"
                                            required>

                                        <option value="">
                                            Select Attire Size
                                        </option>

                                        @foreach($attireSizes->unique('name') as $size)

                                            <option value="{{ $size->id }}">
                                                {{ $size->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                            </div>

                            {{-- TERMS --}}
                            <div class="form-check mt-3">

                                <input type="checkbox"
                                       name="terms_accepted"
                                       value="1"
                                       class="form-check-input terms-checkbox"
                                       required>

                                <label class="form-check-label">
                                    I agree to the
                                    <a href="{{ route('terms.show', ['event_id' => '_TERMS_']) }}"
                                       target="_blank"
                                       id="termsLink">
                                        Terms & Conditions
                                    </a>
                                </label>

                            </div>

                        </div>

                        {{-- RIGHT --}}
                        <div class="col-lg-4">

                            <div class="card summary-box">

                                <div class="card-body" id="summaryContent">

                                    <h4>
                                        Booking Summary
                                    </h4>

                                    <hr>

                                    <div id="summaryItems">
                                        <p class="text-muted">No events selected</p>
                                    </div>

                                    <hr>

                                    <div class="price-text"
                                         id="summaryPrice">

                                        MWK 0.00

                                    </div>

                                    <div id="summaryCredits" class="mt-2 small" style="display:none;">
                                        <p class="mb-0">Credit: <span id="summaryCreditAmt" style="color:#d32f2f;">MWK 0</span></p>
                                        <p class="mb-0">Debt: <span id="summaryDebtAmt" style="color:#d32f2f;">MWK 0</span></p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            class="btn text-white"
                            style="background:#006198">

                        Submit Booking

                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const memberType =
                document.getElementById('member_type').value;

            const priceData =
                JSON.parse(document.getElementById('priceData').value);

            const container =
                document.getElementById('eventSectionsContainer');

            const summaryItems =
                document.getElementById('summaryItems');

            const summaryPrice =
                document.getElementById('summaryPrice');

            const summaryCredits =
                document.getElementById('summaryCredits');

            const summaryCreditAmt =
                document.getElementById('summaryCreditAmt');

            const summaryDebtAmt =
                document.getElementById('summaryDebtAmt');

            const floatingBar =
                document.getElementById('floatingRegisterBar');

            const selectedCount =
                document.getElementById('selectedCount');

            const attireSelect =
                document.getElementById('attire_size');

            const memberCreditAmt = parseFloat(document.getElementById('memberCredit').value || 0);
            const memberDebtAmt = parseFloat(document.getElementById('memberDebt').value || 0);

            const hotelsData = @json($hotelsJson);

            const allSizes = @json($attireSizesJson);

            const events = @json($eventsJson);

            let activeEventIds = [];

            /*
            |--------------------------------------------------------------------------
            | CHECKBOX HANDLING
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.event-checkbox').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    const checked = document.querySelectorAll('.event-checkbox:checked');
                    selectedCount.textContent = checked.length + ' event(s) selected';
                    floatingBar.style.display = checked.length > 0 ? 'block' : 'none';
                });
            });

            /*
            |--------------------------------------------------------------------------
            | OPEN MODAL — Build sections for checked events
            |--------------------------------------------------------------------------
            */

            document.getElementById('openMultiBookingBtn').addEventListener('click', function () {

                const checked = document.querySelectorAll('.event-checkbox:checked');
                activeEventIds = [];

                container.innerHTML = '';
                summaryItems.innerHTML = '';
                summaryPrice.textContent = 'MWK 0.00';

                checked.forEach(function (cb) {
                    const eventId = cb.value;
                    const eventName = cb.dataset.eventName;
                    activeEventIds.push(eventId);
                    container.appendChild(buildEventSection(eventId, eventName));
                });

                // Update terms link to first event
                if (activeEventIds.length > 0) {
                    const termsLink = document.getElementById('termsLink');
                    if (termsLink) {
                        termsLink.href = '{{ route("terms.show", ["event_id" => "EVTID"]) }}'.replace('EVTID', encodeURIComponent(activeEventIds[0]));
                    }
                }

                // Reset attire
                attireSelect.value = '';

                updateSummary();
            });

            /*
            |--------------------------------------------------------------------------
            | BUILD EVENT SECTION
            |--------------------------------------------------------------------------
            */

            function buildEventSection(eventId, eventName)
            {
                const eventHotels = hotelsData.filter(function (h) { return h.event_id === eventId; });
                const sizeOptions = allSizes.filter(function (s) { return s.event_id === eventId; });

                const div = document.createElement('div');
                div.className = 'card mb-3';
                div.innerHTML = `
                    <div class="card-body">
                        <h5 class="d-flex justify-content-between">
                            <span>${eventName}</span>
                            <span class="event-badge" style="font-size:11px;">Subtotal: <strong class="event-subtotal" id="sub_${eventId}">MWK 0.00</strong></span>
                        </h5>

                        <input type="hidden" name="event_ids[]" value="${eventId}">

                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Accommodation</label>
                                <select name="accommodation[]" class="form-select form-select-sm acc-select" data-event="${eventId}">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="col-md-3 hotel-col" data-event="${eventId}" style="display:none;">
                                <label class="form-label small">Hotel</label>
                                <select name="hotel[]" class="form-select form-select-sm hotel-select" data-event="${eventId}">
                                    <option value="">Select Hotel</option>
                                    ${eventHotels.map(function (h) {
                                        const disabled = h.available_count <= 0 ? 'disabled' : '';
                                        const label = h.available_count <= 0 ? h.name + ' (Fully Booked)' : h.name;
                                        return `<option value="${h.id}" data-price-code="${h.price_code}" ${disabled}>${label}</option>`;
                                    }).join('')}
                                </select>
                            </div>

                            <div class="col-md-3 spouse-col" data-event="${eventId}" style="display:none;">
                                <label class="form-label small">Spouse</label>
                                <select name="spouse_included[]" class="form-select form-select-sm spouse-select" data-event="${eventId}">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="col-md-3 extras-col" data-event="${eventId}" style="display:none;">
                                <label class="form-label small">Extra Guests</label>
                                <input type="number" name="extras_count[]" class="form-control form-control-sm extras-input" min="0" value="0" data-event="${eventId}">
                                <small class="text-muted">MWK 600k each</small>
                            </div>
                        </div>

                        <div class="extras-disclaimer" data-event="${eventId}" style="display:none;margin-top:4px;">
                            <small class="text-danger"><strong>Disclaimer:</strong> Accommodation is suitable for two people per room. Additional guests are at owner's risk.</small>
                        </div>
                    </div>
                `;

                // Accordion toggle logic for this section
                const accSelect = div.querySelector('.acc-select');
                const hotelCol = div.querySelector('.hotel-col');
                const spouseCol = div.querySelector('.spouse-col');
                const extrasCol = div.querySelector('.extras-col');
                const disclaimer = div.querySelector('.extras-disclaimer');

                accSelect.addEventListener('change', function () {
                    const show = this.value === '1';
                    hotelCol.style.display = show ? '' : 'none';
                    spouseCol.style.display = show ? '' : 'none';
                    extrasCol.style.display = show ? '' : 'none';
                    disclaimer.style.display = show ? '' : 'none';
                    if (!show) {
                        hotelCol.querySelector('select').value = '';
                        spouseCol.querySelector('select').value = '0';
                        extrasCol.querySelector('input').value = '0';
                    }
                    updateSummary();
                });

                div.querySelectorAll('.hotel-select, .spouse-select, .extras-input').forEach(function (el) {
                    el.addEventListener('change', updateSummary);
                    if (el.tagName === 'INPUT') {
                        el.addEventListener('input', updateSummary);
                    }
                });

                return div;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE SUMMARY
            |--------------------------------------------------------------------------
            */

            function updateSummary()
            {
                let grandTotal = 0;
                let html = '';

                activeEventIds.forEach(function (eventId) {
                    const eventName = events.find(function (e) { return e.event_id === eventId; })?.event_name || eventId;

                    const accSelect = container.querySelector('.acc-select[data-event="' + eventId + '"]');
                    const hotelSelect = container.querySelector('.hotel-select[data-event="' + eventId + '"]');
                    const spouseSelect = container.querySelector('.spouse-select[data-event="' + eventId + '"]');
                    const extrasInput = container.querySelector('.extras-input[data-event="' + eventId + '"]');

                    const requiresAcc = parseInt(accSelect?.value || 0);
                    const priceCode = hotelSelect?.options[hotelSelect.selectedIndex]?.dataset.priceCode || null;
                    const spouseIncluded = parseInt(spouseSelect?.value || 0);
                    const extrasCount = parseInt(extrasInput?.value || 0);

                    let matched = null;

                    priceData.forEach(function (price) {
                        if (price.event_id !== eventId) return;
                        if (price.member_type !== memberType) return;
                        if (parseInt(price.accommodation) !== requiresAcc) return;
                        if (requiresAcc) {
                            if (price.hotel !== priceCode) return;
                            if (parseInt(price.spouse_included) !== spouseIncluded) return;
                        }
                        matched = price;
                    });

                    let total = 0;
                    if (matched) {
                        total = parseFloat(matched.price) + (extrasCount * parseFloat(matched.extra_person_price));
                    }

                    grandTotal += total;

                    // Update subtotal badge
                    const subEl = document.getElementById('sub_' + eventId);
                    if (subEl) {
                        subEl.textContent = 'MWK ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }

                    html += '<p><strong>' + eventName + ':</strong> MWK ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</p>';
                });

                if (html === '') {
                    html = '<p class="text-muted">No events selected</p>';
                }

                summaryItems.innerHTML = html;

                summaryPrice.textContent = 'MWK ' + grandTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });

                // Show credit/debt if applicable
                if (memberCreditAmt > 0 || memberDebtAmt > 0) {
                    let creditToShow = Math.min(memberCreditAmt, grandTotal);
                    summaryCreditAmt.textContent = '(MWK ' + creditToShow.toLocaleString(undefined, { minimumFractionDigits: 2 }) + ')';
                    summaryDebtAmt.textContent = 'MWK ' + memberDebtAmt.toLocaleString(undefined, { minimumFractionDigits: 2 });
                    summaryCredits.style.display = 'block';
                } else {
                    summaryCredits.style.display = 'none';
                }
            }

        });

    </script>

@endpush
