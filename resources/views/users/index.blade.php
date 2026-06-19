@extends('layouts.app')

@section('title', env('APP_NAME').'| Users')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}
@endsection

@section('content')
{{--    <h4 class="fw-bold py-3 mb-4">--}}
{{--        <span class="text-muted fw-light">DataTables /</span> Advanced--}}
{{--    </h4>--}}

    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Users List</h5>
        <div class="card-datatable text-nowrap">
            <table class="datatables-ajax table table-bordered" id="table">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Code</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <!--/ Ajax Sourced Server-side profile_photo_url-->
@endsection

@section('vendors-js')
    <script src="{{asset('')}}cms/vendor/libs/datatables/jquery.dataTables.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive/datatables.responsive.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js"></script>
    <!-- Flat Picker -->
    <script src="{{asset('')}}cms/vendor/libs/moment/moment.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/jitbrains/js/simplify.js"></script>
@endsection

@section('page-js')
    <script>
        var table;
        $(document).ready(function() {
            dataTable();
            console.log(DecodeEntities(`{{$services}}`))
        } );

        function dataTable()
        {
            table = $('#table').DataTable({
                processing: true,
                serverSide: false,
                lengthChange: false,
                ordering: false,
                pageLength: 10,
                responsive: true,
                ajax:{
                    "url": "{{route('user_index')}}",
                    "dataType": 'json',
                },

                "dom": '<"top"fi>t<"bottom"lp><"clear">',
                "columnDefs": [
                    {
                        "targets": [0],
                        "data": null,
                        "render": function ( data, type, row ) {
                            let profile = row.profile_photo_path === null ? row.profile_photo_url : row.profile_photo_path;
                            return `<img src="${row.profile_photo_path}" class="provider-logo" style="width: 30px !important;"/>`
                        }
                    },
                    {
                        targets: [1],
                        visible: false,
                        searchable: true,
                    },
                    {
                        "targets": -1,
                        "data": null,
                        "render": function ( data, type, row ) {
                            let actions =  Link({
                                url: `{{route('add_user_as_service_provider')}}/${row.unique_code}`,
                                content: `<i class="fa fa-funnel-dollar" aria-hidden="true"></i>`,
                                classes: `btn btn-primary make-service-provider`,
                            });

                            // let actions = `<a href="" class="btn btn-primary make-service-provider">
                            //     <i class="fa fa-funnel-dollar" aria-hidden="true"></i>
                            // </a>`;

                            return actions;
                        },
                        "defaultContent": ''
                    },],
                "columns": [
                    {"data": "profile_photo_path"},
                    {"data": "email"},
                    {"data": "name"},
                    {"data": "gender"},
                    {"data": "phone"},
                    {"data": "unique_code"},
                    {"data": "created_at"},
                    {"data": ""},
                ]
            });
        }
    </script>

    <script>
        let services = DecodeEntities(`{{$services}}`);
        // makeUserServiceProvider()
        function makeUserServiceProvider() {
            // e.preventDefault();
            alert(services)
        }

        $(".make-service-provider").on("click", function (event) {
            //
            event.preventDefault();
            alert(100)
        })
    </script>
@endsection
