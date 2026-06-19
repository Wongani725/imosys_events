@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Open-Ended Feedback'])

    @forelse($feedbackData as $f)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">{{ $f->question }}</h5>
            <small class="text-muted">{{ $f->section ?: 'General' }} — {{ count($f->responses) }} responses</small>
        </div>
        @if(count($f->responses))
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Participant</th><th>Company</th><th>Response</th></tr>
                </thead>
                <tbody>
                    @foreach($f->responses as $r)
                    <tr>
                        <td>{{ $r->participant }}</td>
                        <td>{{ $r->company }}</td>
                        <td>{{ $r->answer }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="card-body text-muted">No text responses for this question.</div>
        @endif
    </div>
    @empty
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No text-based questions found for this event.</div></div>
    @endforelse
</div>
@endsection
