@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Failed Email Logs</h1>

        @if ($failedEmails->isEmpty())
            <div class="alert alert-info">
                No failed emails found.
            </div>
        @else
            <ol class="list-group">
                @foreach ($failedEmails as $index => $log)
                    <li class="list-group-item text-danger">
                        <strong>{{ $index + 1 }}</strong> - {{ $log->error_message }}
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
@endsection
