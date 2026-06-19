@extends('layouts.web_app')

@section('title', 'Member Booking')

@push('styles')
<style>
.terms-checkbox {
    border: 2px solid #006198 !important;
    box-shadow: none !important;
}
.terms-checkbox:checked {
    background-color: #006198;
    border-color: #006198;
}
</style>
@endpush

@section('content')

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ========================= --}}
    {{-- TERMS MODAL --}}
    {{-- ========================= --}}
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {!! nl2br($terms) !!}
                </div>

            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">Invoice Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <iframe id="invoiceFrame"
                            src=""
                            width="100%"
                            height="600px"
                            style="border:none;">
                    </iframe>
                </div>

            </div>
        </div>
    </div>

    <!-- Proof of Payment Modal -->
    <div class="modal fade" id="popModal" tabindex="-1">
        <div class="modal-dialog">

            <form method="POST"
                  id="popForm"
                  enctype="multipart/form-data"
                  action="{{ route('upload.pop', $booking->bookingID) }}"
            >

                @csrf

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Upload Proof of Payment
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Proof of Payment
                            </label>

                            <input type="file"
                                   name="proof_of_payment"
                                   class="form-control"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   required>

                            <small class="text-muted">
                                Accepted formats: PDF, JPG, JPEG, PNG
                            </small>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="submit"
                                class="btn" style="background-color: #97D700; color:white;">
                            Upload
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- CANCEL BOOKING MODAL -->
    <div class="modal fade" id="cancelBookingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('web.cancel.booking') }}">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->bookingID }}">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Cancel Booking</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this booking?</p>
                        <p class="text-muted small">This action cannot be undone. Your booking will be cancelled.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Yes, Cancel Booking</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT BOOKING MODAL -->
    <div class="modal fade" id="editBookingModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form method="POST" id="editBookingForm" action="{{ route('updateBooking') }}">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="edit_booking_id" name="bookingID">
                        <input type="hidden" id="edit_member_type" value="{{ $memberType ?? 'Non-Member' }}">
                        <input type="hidden" id="edit_price_data" value='@json($eventPrices ?? [])'>

                        <div class="row">
                            <div class="col-lg-8">
                                {{-- ACCOMMODATION --}}
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5>1. Accommodation Required?</h5>
                                        <select name="accommodation" id="edit_accommodation" class="form-select">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>

                                    </div>
                                </div>

                                {{-- HOTEL SECTION --}}
                                <div id="edit_hotel_section" style="display:none;">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>2. Hotel Selection</h5>
                                            <select name="hotel" id="edit_hotel" class="form-select">
                                                <option value="">Select Hotel</option>
                                                @foreach($hotels as $hotel)
                                                    @php $unavail = $hotel->available_count <= 0; @endphp
                                                    <option value="{{ $hotel->id }}" data-price-code="{{ str_contains(strtolower($hotel->name), 'nkopola') ? 'nkopola' : 'sun_n_sand' }}" {{ $unavail ? 'disabled' : '' }}>
                                                        {{ $hotel->name }} {{ $unavail ? '(Full)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>3. Spouse Included?</h5>
                                            <select name="spouse_included" id="edit_spouse" class="form-select">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>

                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>4. Additional Guests</h5>
                                            <input type="number" name="extras" id="edit_extras" class="form-control" min="0" value="0">
                                        </div>
                                        <small class="text-danger d-block mt-2">
                                            <strong>Disclaimer:</strong> Please note that the accommodation is only suitable for two people per room. Any additional guests are booked at the owner's risk, as mattresses and sofa beds will be used to accommodate them.
                                        </small>
                                    </div>
                                </div>

                                {{-- ATTIRE SIZE --}}
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5>5. Attire Size</h5>
                                        <select name="attire_size_id" id="edit_attire_size" class="form-select">
                                            <option value="">Select</option>
                                            @foreach($attireSizes ?? [] as $size)
                                                <option value="{{ $size->id }}">{{ $size->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- SUMMARY --}}
                            <div class="col-lg-4">
                                <div class="card summary-box">
                                    <div class="card-body">
                                        <h4>Booking Summary</h4>
                                        <hr>
                                        <p>Event: <strong id="edit_summary_event">{{ $event->event_name }}</strong></p>
                                        <p>Accommodation: <strong id="edit_summary_acc">No</strong></p>
                                        <p>Hotel: <strong id="edit_summary_hotel">-</strong></p>
                                        <p>Spouse: <strong id="edit_summary_spouse">No</strong></p>
                                        <p>Extras: <strong id="edit_summary_extras">0</strong></p>
                                        <hr>
                                        <div class="price-text" id="edit_summary_price">MWK 0.00</div>
                                    </div>
                                </div>
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
                                <a href="{{ route('terms.show', ['event_id' => $event->event_id]) }}"
                                   target="_blank">
                                    Terms & Conditions
                                </a>
                            </label>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- EVENTS LOOP --}}
    {{-- ========================= --}}
    @php
        $activeStatuses = ['Pending Payment', 'Confirmed'];
    @endphp

    <div class="row justify-content-center mb-3">

        <div class="col-md-9">

            <div class="card shadow-sm dashboard-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    {{-- LEFT: EVENT INFO --}}
                    <div>

                        <h5 class="mb-1">
                            {{ $event->event_name }}
                        </h5>

                        <small class="text-muted d-block">
                            {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                            -
                            {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                        </small>

                        <small class="text-muted">
                            {{ $event->event_venue }}
                        </small>

                        {{-- STATUS --}}
                        <div class="mt-2">

                            @if(!$booking)

                                <span class="badge bg-secondary">Not Booked</span>

                            @elseif($booking->booking_status === 'Confirmed')

                                <span class="badge bg-success">Confirmed</span>

                            @elseif(in_array($booking->booking_status, $activeStatuses))

                                <span class="badge bg-warning text-dark">
                                {{ $booking->booking_status }}
                            </span>

                            @else

                                <span class="badge bg-light text-dark">
                                {{ $booking->booking_status }}
                            </span>

                            @endif

                        </div>

                    </div>

                    {{-- RIGHT: ACTION BUTTON --}}
                    <div class="">

                        @if(!$booking)
                            <button class="btn text-white" style="background:#006198"
                                    data-bs-toggle="modal" data-bs-target="#bookingModal"
                                    data-event-id="{{ $event->event_id }}">
                                Register
                            </button>

                        @elseif($booking->booking_status === 'Confirmed')
                            @php
                                $hasSibling = \App\Models\Bookers::where('booking_reference', $booking->booking_reference)
                                    ->where('bookingID', '!=', $booking->bookingID)
                                    ->exists();
                            @endphp
                            <div class="d-flex flex-column gap-2">
                                @if($booking->reference_code)
                                    <a href="{{ route('show-participant', ['reference_code' => $booking->reference_code]) }}"
                                       class="btn text-white" style="background:#006198;">
                                        Download Name Tag
                                    </a>
                                @endif
                                <button class="btn btn-outline-primary view-invoice"
                                        data-url="{{ $hasSibling ? url('/get-consolidated-invoice/' . $booking->bookingID) : url('/get-invoice-pdf/' . $booking->bookingID) }}">
                                    <i class="fas fa-file-invoice"></i> View Invoice
                                </button>
                            </div>

                        @elseif($booking->booking_status === 'Pending Payment')
                            @php
                                $hasSibling = \App\Models\Bookers::where('booking_reference', $booking->booking_reference)
                                    ->where('bookingID', '!=', $booking->bookingID)
                                    ->exists();
                            @endphp
                            <div class="d-flex flex-column gap-2">
                                <button class="btn text-white view-invoice"
                                        style="background:#006198;"
                                        data-url="{{ $hasSibling ? url('/get-consolidated-invoice/' . $booking->bookingID) : url('/get-invoice-pdf/' . $booking->bookingID) }}">
                                    <i class="fas fa-file-invoice"></i> View Invoice
                                </button>
                                <button class="btn btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editBookingModal"
                                        data-booking-id="{{ $booking->bookingID }}">
                                    <i class="fas fa-edit"></i> Edit Booking
                                </button>
                                @php $popUrl = $booking->proof_of_payment ? route('view.pop', $booking->bookingID) : null; @endphp
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary flex-grow-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#popModal"
                                            data-booking-id="{{ $booking->bookingID }}">
                                        <i class="fas fa-upload"></i> {{ $popUrl ? 'Re-upload' : 'Upload' }} POP
                                    </button>
                                    @if($popUrl)
                                        <a href="{{ $popUrl }}" target="_blank" class="btn btn-outline-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    @endif
                                </div>
                                <button class="btn btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelBookingModal"
                                        data-booking-id="{{ $booking->bookingID }}">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>

                        @else
                            <span class="text-muted">{{ $booking->booking_status }}</span>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('.view-invoice').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const url = this.getAttribute('data-url');
                    console.log(url);

                    document.getElementById('invoiceFrame').src = url;

                    const invoiceModal = new bootstrap.Modal(
                        document.getElementById('invoiceModal')
                    );

                    invoiceModal.show();
                });
            });

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const popModal = document.getElementById('popModal');

            popModal.addEventListener('show.bs.modal', event => {

                const button = event.relatedTarget;

                const bookingID = button.getAttribute('data-booking-id');

                const form = document.getElementById('popForm');

                form.action = `/upload-proof-of-payment/${bookingID}`;
            });

        });


        ======================================================

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const editModal = document.getElementById('editBookingModal');

            const form = document.getElementById('editBookingForm');

            const accommodation = document.getElementById('edit_accommodation');
            const hotelSection = document.getElementById('edit_hotel_section');
            const hotel = document.getElementById('edit_hotel');

            const spouseSection = document.getElementById('edit_spouse_section');
            const spouse = document.getElementById('edit_spouse');

            const extrasSection = document.getElementById('edit_extras_section');
            const extras = document.getElementById('edit_extras');

            let currentBookingID = null;

            // =========================
            // OPEN MODAL + LOAD DATA
            // =========================
            editModal.addEventListener('show.bs.modal', event => {

                const button = event.relatedTarget;
                currentBookingID = button.getAttribute('data-booking-id');

                document.getElementById('edit_booking_id').value = currentBookingID;

                fetch(`/booking-json/${currentBookingID}`)
                    .then(res => res.json())
                    .then(data => {

                        accommodation.value = data.accommodation ?? 0;
                        spouse.value = data.spouse_included ?? 0;
                        extras.value = data.extras ?? 0;

                        hotel.value = data.hotel_choice ?? "";

                        toggleSections(data.accommodation);

                    });

            });

            // =========================
            // TOGGLE SECTIONS
            // =========================
            function toggleSections(accommodationValue)
            {
                if (accommodationValue == 1) {

                    hotelSection.style.display = 'block';
                    spouseSection.style.display = 'block';
                    extrasSection.style.display = 'block';

                } else {

                    hotelSection.style.display = 'none';
                    spouseSection.style.display = 'none';
                    extrasSection.style.display = 'none';

                    hotel.value = "";
                    spouse.value = 0;
                    extras.value = 0;
                }
            }

            // =========================
            // EVENTS
            // =========================
            accommodation.addEventListener('change', function () {
                toggleSections(this.value);
            });
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit modal: pre-fill values when opening
            const editModal = document.getElementById('editBookingModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (e) {
                    const btn = e.relatedTarget;
                    const bookingId = btn.getAttribute('data-booking-id');

                    document.getElementById('edit_booking_id').value = bookingId;

                    // Reset and re-calculate
                    document.getElementById('edit_accommodation').value = '{{ $booking->accommodation ?? 0 }}';
                    document.getElementById('edit_summary_acc').textContent = '{{ $booking->accommodation ? "Yes" : "No" }}';

                    if ('{{ $booking->accommodation }}' == '1') {
                        document.getElementById('edit_hotel_section').style.display = 'block';
                    } else {
                        document.getElementById('edit_hotel_section').style.display = 'none';
                    }

                    editUpdatePrice();
                });
            }

            // Live price on edit modal
            const eAcc = document.getElementById('edit_accommodation');
            const eHotel = document.getElementById('edit_hotel');
            const eSpouse = document.getElementById('edit_spouse');
            const eExtras = document.getElementById('edit_extras');

            if (eAcc) eAcc.addEventListener('change', function () {
                document.getElementById('edit_hotel_section').style.display = this.value == '1' ? 'block' : 'none';
                document.getElementById('edit_summary_acc').textContent = this.value == '1' ? 'Yes' : 'No';
                if (this.value == '0') {
                    document.getElementById('edit_summary_hotel').textContent = '-';
                    document.getElementById('edit_summary_spouse').textContent = 'No';
                    document.getElementById('edit_summary_extras').textContent = '0';
                }
                editUpdatePrice();
            });

            if (eHotel) eHotel.addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                document.getElementById('edit_summary_hotel').textContent = opt ? opt.text : '-';
                editUpdatePrice();
            });

            if (eSpouse) eSpouse.addEventListener('change', function () {
                document.getElementById('edit_summary_spouse').textContent = this.value == '1' ? 'Yes' : 'No';
                editUpdatePrice();
            });

            if (eExtras) eExtras.addEventListener('input', function () {
                document.getElementById('edit_summary_extras').textContent = this.value;
                editUpdatePrice();
            });

            function editUpdatePrice() {
                var acc = parseInt(eAcc ? eAcc.value : 0);
                var selectedOpt = eHotel ? eHotel.options[eHotel.selectedIndex] : null;
                var hotelCode = selectedOpt ? (selectedOpt.dataset.priceCode || '') : '';
                var spouse = parseInt(eSpouse ? eSpouse.value : 0);
                var extras = parseInt(eExtras ? eExtras.value || 0 : 0);
                var memberType = (document.getElementById('edit_member_type') || {}).value || 'Non-Member';
                var eventId = '{{ $event->event_id }}';

                var priceData = [];
                try { priceData = JSON.parse(document.getElementById('edit_price_data').value || '[]'); } catch(e) {}

                var matched = null;
                priceData.forEach(function (p) {
                    if (p.event_id === eventId && p.member_type === memberType && parseInt(p.accommodation) === acc) {
                        if (acc === 0) { matched = p; }
                        else if (p.hotel === hotelCode && parseInt(p.spouse_included) === spouse) { matched = p; }
                    }
                });

                var total = matched ? parseFloat(matched.price) + (extras * parseFloat(matched.extra_person_price || 0)) : 0;
                var el = document.getElementById('edit_summary_price');
                if (el) el.textContent = 'MWK ' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
            }
        });
    </script>

@endsection
