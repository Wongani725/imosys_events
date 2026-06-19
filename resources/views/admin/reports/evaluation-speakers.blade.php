@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Speaker Ratings'])

    <div class="row g-3 mb-4">
        @forelse($speakerData as $s)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">{{ $s->name }}</h5>
                            @if($s->title)<small class="text-muted">{{ $s->title }}</small>@endif
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $s->avg >= 4 ? 'success' : ($s->avg >= 3 ? 'warning' : 'danger') }} fs-5">{{ number_format($s->avg, 1) }}</span>
                            <small class="d-block text-muted">{{ $s->total }} ratings</small>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            @php $pct = $s->total > 0 ? round(($s->distribution[$i] ?? 0) / $s->total * 100) : 0; @endphp
                            <div class="d-flex align-items-center mb-1 small">
                                <span style="width:20px;">{{ $i }}</span>
                                <div class="progress flex-grow-1 mx-2" style="height:8px;">
                                    <div class="progress-bar bg-{{ $i >= 4 ? 'success' : ($i >= 3 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <span style="width:30px;">{{ $pct }}%</span>
                            </div>
                        @endfor
                    </div>
                    @if(count($s->comments))
                    <hr>
                    <small class="text-muted">Comments:</small>
                    <ul class="small mb-0 mt-1">
                        @foreach($s->comments as $c)
                        <li class="mb-1">{{ $c }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No speaker ratings for this event.</div></div></div>
        @endforelse
    </div>
</div>
@endsection
