@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Reports</h2>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <select name="event_id" class="form-select" onchange="this.form.submit()">
                @foreach($events as $ev)
                    <option value="{{ $ev->event_id }}" {{ $selectedEvent?->event_id === $ev->event_id ? 'selected' : '' }}>{{ $ev->event_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8 d-flex align-items-center">
            <span class="text-muted small">Showing data for: <strong class="ms-1 text-iia-blue">{{ $selectedEvent?->event_name }}</strong></span>
        </div>
    </form>

    <div class="row g-3">
        @foreach($reports as $report)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $report['title'] }}</h5>
                    <p class="card-text text-muted small flex-grow-1">{{ $report['desc'] }}</p>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.reports.show', ['type' => $report['type'], 'event_id' => $selectedEvent?->event_id]) }}"
                           class="btn btn-iia-blue btn-sm flex-grow-1"><i class="bx bx-show"></i> View</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
