@extends('layouts.app')

@section('title', env('APP_NAME').': Services')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
    <style>
        .provider-logo{
            width: 30px !important;
            height: 30px !important;
        }
        /*table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,*/
        /*table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {*/
        /*    left: auto;*/
        /*    right: -69vw;*/
        /*}*/
    </style>
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}
@endsection

@section('content')
    <div class="card">
        <h5 class="card-header">{{$title}}</h5>
        <div class="card-body datatable text-nowrap">
            <table id="table" class="table table-bordered">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Entry Date</th>
                    <th>Reference</th>
                    <th>Expiry Date</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('vendors-js')
    <script src="{{asset('')}}cms/vendor/libs/datatables/jquery.dataTables.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive/datatables.responsive.js"></script>
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js">
    <script src="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js"></script>
    <!-- Flat Picker -->
    <script src="{{asset('')}}cms/vendor/libs/moment/moment.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/jitbrains/js/simplify.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>
@endsection

@section('page-js')
    <script src="{{asset('')}}cms/js/tables-datatables-advanced.js"></script>

    <script>
        var table;
        $(document).ready(function() {
            dataTable();
        } );

        function dataTable()
        {
            table = $('#table').DataTable({
                processing: true,
                serverSide: false,
                lengthChange: false,
                ordering: false,
                responsive: true,
                pageLength: 10,
                ajax:{
                    "url": "{{route('report_promo_participants')}}",
                    "dataType": 'json',
                },

                "dom": '<"top"fi>t<"bottom"lp><"clear">',
                "columnDefs": [

                ],
                // dom: 'Bfrtip',
                // buttons: true,
                // buttons: {
                //     buttons: [
                //         'copy',
                //         { extend: 'excel', text: 'Save as Excel' }
                //     ]
                // },
                "columns": [
                    {"data": "name"},
                    {"data": "phone"},
                    {"data": "entry_date"},
                    {"data": "payment_reference"},
                    {"data": "end_date"},
                ],

                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5', 'excelHtml5', 'pdfHtml5', 'csvHtml5'
                ]
            });
        }
    </script>


    // h
    <script>
        $(document).on("click", ".delete-button", function (event) {
            event.preventDefault();
            let clickedButton = $(this);
            let serviceProviderName = clickedButton.data("name"),
                serviceProviderReference = clickedButton.data("reference");
// alert(`${serviceProviderName} - ${serviceProviderReference}`);
            Swal.fire({
                title: "Are you sure?",
                text: `Permanently delete ${serviceProviderName}!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: 'Continue',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: (login) => {
                    return fetch(`{{route('service_provider_delete')}}`,  {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            reference: `${serviceProviderReference}`,
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            table.ajax.reload(null, false);
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
            }).then((result) => {
                let response = result.value;
                table.ajax.reload(null, false);
                if (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: `${response.msg}`,
                        customClass: {confirmButton: "btn btn-success"}
                    })
                }
            });


            {{--Swal.fire({--}}
            {{--    title: "Are you sure?",--}}
            {{--    text: "You won't be able to revert this!",--}}
            {{--    icon: "warning",--}}
            {{--    showCancelButton: !0,--}}
            {{--    confirmButtonText: "Yes, delete it!",--}}
            {{--    customClass: {confirmButton: "btn btn-primary me-3", cancelButton: "btn btn-label-secondary"},--}}
            {{--    buttonsStyling: !1,--}}
            {{--    showLoaderOnConfirm: true,--}}
            {{--    allowOutsideClick: () => !Swal.isLoading(),--}}
            {{--    preConfirm: function(){--}}
            {{--        return $.ajax({--}}
            {{--            url: "{{route('service_provider_delete')}}",--}}
            {{--            dataType: "json",--}}
            {{--            success: function (response) {--}}
            {{--                Swal.fire({--}}
            {{--                    icon: "success",--}}
            {{--                    title: "Deleted!",--}}
            {{--                    text: "Your file has been deleted.",--}}
            {{--                    customClass: {confirmButton: "btn btn-success"}--}}
            {{--                })--}}
            {{--            },--}}
            {{--            error: function (status,  result, ) {--}}
            {{--                Swal.fire({--}}
            {{--                    title: "Cancelled",--}}
            {{--                    text: "Your imaginary file is safe :)",--}}
            {{--                    icon: "error",--}}
            {{--                    customClass: {confirmButton: "btn btn-success"}--}}
            {{--                })--}}
            {{--            }--}}
            {{--        }); //Your ajax function here--}}
            {{--    }--}}
            {{--})--}}
            {{--    .then(function (t) {--}}
            {{--    if(t.value) {--}}
            {{--        Swal.showLoading(Swal.getDenyButton());--}}

            {{--        setTimeout(function () {--}}
            {{--            Swal.hideLoading()--}}
            {{--        }, 500);--}}
            {{--        $.ajax({--}}
            {{--            url: "{{route('service_provider_delete')}}",--}}
            {{--            dataType: "json",--}}
            {{--            success: function (response) {--}}
            {{--                Swal.fire({--}}
            {{--                    icon: "success",--}}
            {{--                    title: "Deleted!",--}}
            {{--                    text: "Your file has been deleted.",--}}
            {{--                    customClass: {confirmButton: "btn btn-success"}--}}
            {{--                })--}}
            {{--            },--}}
            {{--            error: function (status,  result, ) {--}}
            {{--                Swal.fire({--}}
            {{--                    title: "Cancelled",--}}
            {{--                    text: "Your imaginary file is safe :)",--}}
            {{--                    icon: "error",--}}
            {{--                    customClass: {confirmButton: "btn btn-success"}--}}
            {{--                })--}}
            {{--            }--}}
            {{--        })--}}
            {{--    }--}}
            {{--})--}}
        })
    </script>
@endsection
