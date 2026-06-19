@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Individual Participant Answers'])

    <div class="mb-3">
        <input type="text" id="searchParticipant" class="form-control" placeholder="Search by name or reference code..." style="max-width:400px;">
    </div>

    @forelse($individualData as $i)
    <div class="card border-0 shadow-sm mb-3 participant-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong class="participant-name">{{ $i->participant }}</strong>
                <small class="text-muted ms-2">{{ $i->reference_code }}</small>
                @if($i->company)<br><small class="text-muted">{{ $i->company }}</small>@endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Question</th><th>Section</th><th>Answer</th></tr>
                </thead>
                <tbody>
                    @foreach($questions as $q)
                    @php $answer = $i->answers[$q->id] ?? ''; @endphp
                    @if($answer !== '' && $answer !== null)
                    <tr>
                        <td>{{ $q->questions }}</td>
                        <td><span class="badge bg-secondary">{{ $q->section ?: 'General' }}</span></td>
                        <td>{{ $answer }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No evaluation submissions for this event.</div></div>
    @endforelse
</div>

<script>
    document.getElementById('searchParticipant')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.participant-card').forEach(card => {
            const name = card.querySelector('.participant-name')?.textContent?.toLowerCase() || '';
            card.style.display = name.includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
