@extends('layouts.app')

@section('title', 'Participants list')

@section('vendor-css')
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" />
@endsection

@section('page-css')
    <style>
        .action-buttons > * {
            margin: 2px;
        }
    </style>
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('events') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Back to Events</a>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <a href="{{ route('view_participant_fees', $event_id) }}" class="btn btn-iia-blue w-100"><i class="bx bx-money"></i> Fees</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('view_hotels', $event_id) }}" class="btn btn-iia-blue w-100"><i class="bx bx-building"></i> Hotels</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('get-sponsors', $event_id) }}" class="btn btn-iia-blue w-100"><i class="bx bx-star"></i> Sponsors</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('evaluate-here', $event_id) }}" class="btn btn-iia-blue w-100"><i class="bx bx-star"></i> Questions</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('get-terms') }}?event_id={{ $event_id }}" class="btn btn-iia-blue w-100"><i class="bx bx-file"></i> Terms & Conditions</a>

                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.walkin.create') }}" class="btn btn-iia-green w-100"><i class="bx bx-walk"></i> Walk-in</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.name-tags.index', ['event_id' => $event_id]) }}" class="btn btn-iia-blue w-100"><i class="bx bx-tag"></i> Name Tags</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ url('download_certificates/'.$event_id) }}" class="btn btn-iia-blue w-100"><i class="bx bx-certification"></i> Certificates</a>
                </div>
               
            </div>

            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Participants details</span>
            </h4>

            {{-- Search --}}
            <form action="{{ route('view_participants') }}" method="GET" class="d-flex mt-4">
                <input type="hidden" name="id" value="{{ $event_id }}">
                <input type="text" name="search" class="form-control" placeholder="Search participants..." value="{{ request('search') }}">
                <button type="submit" style="color: white; background-color: #006198;" class="btn ms-2">Search</button>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="participantsTable" class="table table-striped table-bordered nowrap" style="width:100%;">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reference Code</th>
                        <th>Participant</th>
                        <th>Phone Number</th>
                        <th>Email Address</th>
                        <th>Company Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($participants as $i)
                        <tr>
                            <td>{{ $i->id }}</td>
                            <td>{{ $i->reference_code }}</td>
                            <td>{{ $i->participant }}</td>
                            <td>{{ $i->phone_number }}</td>
                            <td>{{ $i->email_address }}</td>
                            <td>{{ $i->company_name }}</td>
                            <td>{{ $i->status }}</td>
                            <td>
                                <div class="btn-group flex-wrap action-buttons">
                                    @if (auth()->user()->user_type == '2' || auth()->user()->user_type == '3')
                                        <a href="{{ url("edit_participant/{$i->reference_code}") }}" style="color: white; background-color: #006198;" class="btn btn-sm">Edit</a>
                                    @endif

                                    <a href="{{ route('show_participant', ['id1' => $i->reference_code, 'id2' => $event_id]) }}" style="color: white; background-color: #006198;" class="btn btn-sm">View Name Tag</a>

                                    @if (auth()->user()->user_type == '2' || auth()->user()->user_type == '3')
                                        <a href="{{ url("delete_participant/{$i->reference_code}") }}" class="btn btn-danger btn-sm">Delete</a>
                                    @endif

                                    <button data-reference="{{$i->reference_code}}" class="btn btn-warning btn-sm sendMail" style="text-align: center;">
                                        <div data-i18n="Send Name Tag Email">Send Name Tag Email</div>
                                    </button>

                                    <button data-reference="{{$i->reference_code}}" class="btn btn-secondary btn-sm sendEvaluation" style="text-align: center;">
                                        <div data-i18n="Send Evaluation Email">Send Evaluation email</div>
                                    </button>

                                    <a href="{{ route('view_certificate', ['id1' => $i->reference_code, 'id2' => $event_id]) }}" style="color: white; background-color: #006198;" class="btn btn-sm">View Certificate</a>
                                    <a href="{{ route('download_certificate_pdf', ['reference_code' => $i->reference_code, 'event_id' => $event_id]) }}" style="color: white; background-color: #28a745;" class="btn btn-sm">Download PDF</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $participants->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>


@endsection

@section('page-js')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).on("click", ".sendMail", function (e) {
            e.preventDefault();
            let reference = $(this).data("reference"), email = "";

            Swal.fire({
                title: 'Enter Participant Email',
                input: 'email',
                inputPlaceholder: 'example@example.com',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Send',
                showLoaderOnConfirm: true,
                preConfirm: (input) => {
                    email = input;

                    return fetch(`{{ route('participant_send_email') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            email: email,
                            reference: reference
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: `Name Tag Email Sent`,
                        text: `Sent to ${email} successfully.`,
                        icon: 'success'
                    });
                }
            });
        });
    </script>


    <script>
        $(document).on("click", ".sendEvaluation", function (e) {
            e.preventDefault();
            let reference = $(this).data("reference"), email = "";
            Swal.fire({
                title: 'Enter Participant Email',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Send',
                showLoaderOnConfirm: true,
                preConfirm: (input) => {
                    email = input;
                    return fetch(`{{route('participant_send_email_evaluation')}}`, {

                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: `${email}`,
                            reference: `${reference}`
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: `Evaluation Email`,
                        text: `Sent to ${email} successfully.`,
                        // imageUrl: result.value.avatar_url
                    })
                }
            })
        });
    </script>

    <script>
        $(document).ready(function () {
            const table = $('#participantsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                searching: false,
                paging: false,
                ordering: false,
                info: false
            });

            // Force DataTable to recalc responsive layout
            setTimeout(function () {
                table.responsive.recalc();
            }, 200);


        });
    </script>
@endsection
