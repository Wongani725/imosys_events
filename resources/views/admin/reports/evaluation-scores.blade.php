@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Evaluation Scores by Section'])

    @php $grouped = collect($scores)->groupBy('section'); @endphp
    @foreach($grouped as $section => $sectionScores)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">{{ $section ?: 'General' }}</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Question</th><th>Avg Score</th><th>Responses</th><th>Distribution</th></tr>
                </thead>
                <tbody>
                    @foreach($sectionScores as $s)
                    <tr>
                        <td>{{ $s->question }}</td>
                        <td><span class="badge bg-{{ $s->avg >= 4 ? 'success' : ($s->avg >= 3 ? 'warning' : 'danger') }} fs-6">{{ number_format($s->avg, 1) }}</span></td>
                        <td>{{ $s->total }}</td>
                        <td>
                            @foreach($s->counts as $val => $cnt)
                                <span class="badge bg-secondary me-1">{{ $val }}: {{ $cnt }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if(empty($scores))
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No radio-type questions found for this event.</div></div>
    @endif
</div>
@endsection
