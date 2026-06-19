@extends('layouts.app')

@section('title', 'Participation Attire Report')

@section('vendor-css')
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" />
@endsection

@section('content')
    <div class="container">
        <h1>Participation Report – Event {{ $event_id }}</h1>

        <div class="mb-3">
            <span class="badge bg-primary">Physical Participants: {{ $physicalCount }}</span>
            <span class="badge bg-success">Virtual Participants: {{ $virtualCount }}</span>
            <span class="badge bg-dark">Total: {{ $physicalCount + $virtualCount }}</span>
        </div>

        <div class="card">
            <div class="card-body">
                <a href="{{ route('participation-attires.export') }}" class="btn btn-primary mb-3">Export Report</a>

                <div class="table-responsive">
                    <table id="balancesTable" class="table table-striped table-bordered nowrap" style="width:100%;">
                        <thead>
                        <tr>
                            <th>Participant Name</th>
                            <th>Company</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Attire Size</th>
                            <th>Attire Color</th>
{{--                            <th>Gender</th>--}}
                            <th>Mode of Attendance</th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bookers as $booker)
                            <tr>
                                <td>{{ $booker->name }}</td>
                                <td>{{ $booker->company ?? 'N/A' }}</td>
                                <td>{{ $booker->email }}</td>
                                <td>{{ $booker->phone_number ?? 'N/A' }}</td>
                                <td>{{ $booker->attireSize->attire_size ?? 'N/A' }}</td>
                                <td>{{ $booker->attireColor->color ?? 'N/A' }}</td>
{{--                                <td>{{ $booker->gender ?? 'N/A' }}</td>--}}
                                <td>{{ $booker->mode_of_attendance ?? 'N/A' }}</td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#balancesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "Search participant:",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection
