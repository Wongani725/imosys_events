@extends('layouts.app')

@section('title', 'Pending Authorization Logs')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
    <style>

        .card {
            width: 100%; /* Adjust the width as per your requirement */
            margin: 0 auto; /* Center the container horizontally */
        }

        table {
            width: 80%; /* Set the table width to 100% of its container */
            /* Additional table styles go here */
        }

        /*#datatables-table_wrapper .dataTables_length label,*/
        /*#datatables-table_wrapper .dataTables_filter label {*/
        /*    display: flex;*/
        /*    align-items: center;*/
        /*    margin-left: 30px;*/
        /*}*/

        #datatables-table_filter input {
            width: auto;
            flex-grow: 1;
            margin-left: 30px;
        }

        #datatables-table{
            border-collapse: collapse;
            width: 90%;
        }
        /*dataTables_filter input[type="search"] {*/
        /*    width: 90000px; !* Adjust the width as needed *!*/
        /*}*/
    </style>
@endsection

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">List of </span> Actions seeking approval
    </h4>

    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <div class="row">
            <div class="col-sm-6 col-md-3 mb-2">
                <a href="{{route("view-progress")}}" class="btn text-white btn-block"; style="background-color: #166590">View progress</a>
            </div>
        </div>
        <div class="card-body text-nowrap">
            <div class="table-responsive">
                <table id="datatables-table" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Approval Reference ID</th>
                        <th>Requested by</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created at</th>
                        <th class="action-header" >Action</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pendingValues as $i)
                        <tr>
                            <td>{{$i->reference_id}}</td>
                            <td>{{$i->requested_by}}</td>
                            <td>{{$i->description}}</td>
                            <td>{{$i->status}}</td>
                            <td>{{$i->created_at}}</td>
                            <td>

                            @if (auth()->user()->user_type == '2' or auth()->user()->user_type == '3')
                                <!--a href="" class="btn btn-success btn-sm">Preview</a-->
                                <a href="preview_action/{{ $i->reference_id }}" class="btn btn-info btn-sm">Preview</a>
                                <a href="{{ route('auth.approve')}}/{{ $i->reference_id }}" class="btn btn-success btn-sm">Approve</a>
                                <a href="decline_action/{{ $i->reference_id }}" class="btn btn-danger btn-sm">Decline</a>
                            @endif


                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side -->
@endsection

@section('vendors-js')
    <script src="{{asset('')}}cms/vendor/libs/datatables/jquery.dataTables.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive/datatables.responsive.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js"></script>
    <!-- Flat Picker -->
    <script src="{{asset('')}}cms/vendor/libs/moment/moment.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.js"></script>
@endsection

@section('page-js')
    <script>
        $(document).ready(function() {
            $('#datatables-table').DataTable({
                responsive: true

            });


        });
        $('.dataTables_filter input').css('width', '700px');

        function sweetAlert() {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it!",
                customClass: {confirmButton: "btn btn-primary me-3", cancelButton: "btn btn-label-secondary"},
                buttonsStyling: !1
            }).then(function (t) {
                t.value ? Swal.fire({
                    icon: "success",
                    title: "Deleted!",
                    text: "Your file has been deleted.",
                    customClass: {confirmButton: "btn btn-success"}
                }) : t.dismiss === Swal.DismissReason.cancel && Swal.fire({
                    title: "Cancelled",
                    text: "Your imaginary file is safe :)",
                    icon: "error",
                    customClass: {confirmButton: "btn btn-success"}
                })
            })
        }
    </script>
@endsection
