@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Job Progress</h2>
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Queue</th>
                <th>Attempts</th>
                <th>Payload</th>
                <th>Email Address</th>
                <!-- Add more columns as needed -->
            </tr>
            </thead>
            <tbody>
            @foreach ($jobs as $key => $job)
                <?php
                $columnValue = $job->payload;
                $payload = unserialize($columnValue);
                if (is_array($payload) && isset($payload['data']['command']['participant']['email_address'])) {
                    $emailAddress = $payload['data']['command']['participant']['email_address'];
                } else {
                    $emailAddress = 'N/A';
                }
                ?>
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $job->id }}</td>
                    <td>{{ $job->queue }}</td>
                    <td>{{ $job->attempts }}</td>
                    <td>{{ $columnValue }}</td>
                    <td>{{ $emailAddress }}</td>
                    <!-- Add more columns as needed -->
                </tr>
            @endforeach
            </tbody>
        </table>

        {{--        <table class="table">--}}
{{--            <thead>--}}
{{--            <tr>--}}
{{--                <th>ID</th>--}}
{{--                <th>Queue</th>--}}
{{--                <th>Attempts</th>--}}
{{--                <!-- Add more columns as needed -->--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach ($jobs as $job)--}}
{{--                <tr>--}}
{{--                    <td>{{ $job->id }}</td>--}}
{{--                    <td>{{ $job->queue }}</td>--}}
{{--                    <td>{{ $job->attempts }}</td>--}}
{{--                    <!-- Add more columns as needed -->--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}
    </div>
@endsection
