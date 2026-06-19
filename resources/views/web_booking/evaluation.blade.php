@extends('layouts.web_app')

@section('title', 'Event Evaluation')

@push('styles')
<style>
    .eval-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .rating-star { font-size: 28px; cursor: pointer; color: #ddd; transition: color 0.2s; }
    .rating-star.active, .rating-star:hover { color: #ffc107; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="card eval-card mb-4">
        <div class="card-body text-center">
            <h3>{{ $event->event_name }} — Evaluation</h3>
            <p class="text-muted">Your attendance: <strong>{{ $eligibility['percentage'] }}%</strong> ({{ $eligibility['attended'] }}/{{ $eligibility['total_sessions'] }} sessions)</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('member.evaluation.submit', $event->event_id) }}">
        @csrf

        {{-- General Questions --}}
        @if($questions->count())
        <div class="card eval-card mb-4">
            <div class="card-header bg-white fw-bold">General Evaluation</div>
            <div class="card-body">
                @foreach($questions as $index => $q)
                    @if(!empty($q->section) && strtolower($q->section) === 'speakers') @continue @endif
                <div class="mb-4">
                    <p class="fw-bold mb-2">{{ $index + 1 }}. {{ $q->questions }}</p>
                    @if($q->type === 'radio')
                        @if($q->options)
                            <div class="d-flex gap-2 flex-wrap justify-content-center">
                                @foreach(explode(',', $q->options) as $opt)
                                <label class="btn btn-outline-primary btn-sm">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ trim($opt) }}" required> {{ trim($opt) }}
                                </label>
                                @endforeach
                            </div>
                        @else
                            <div class="d-flex gap-2 flex-wrap justify-content-center">
                                @for($i = 1; $i <= 5; $i++)
                                <label class="btn btn-outline-primary btn-sm">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $i }}" required> {{ $i }}
                                </label>
                                @endfor
                            </div>
                        @endif
                    @elseif($q->type === 'options' && $q->options)
                        <div class="d-flex justify-content-center">
                        <select name="answers[{{ $q->id }}]" class="form-select w-auto" required>
                            <option value="">Select...</option>
                            @foreach(json_decode($q->options, true) ?? explode(',', $q->options) as $opt)
                                <option value="{{ trim($opt) }}">{{ trim($opt) }}</option>
                            @endforeach
                        </select>
                        </div>
                    @else
                        <textarea name="answers[{{ $q->id }}]" class="form-control" rows="2" placeholder="Your answer..."></textarea>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Speaker Ratings --}}
        @if($speakers->count())
        <div class="card eval-card mb-4">
            <div class="card-header bg-white fw-bold">Rate the Speakers</div>
            <div class="card-body">
                @foreach($speakers as $speaker)
                <div class="mb-4 p-3 bg-light rounded text-center">
                    <p class="fw-bold mb-2">{{ $speaker->name }} @if($speaker->title)<small class="text-muted">({{ $speaker->title }})</small>@endif</p>
                    <div class="d-flex gap-1 mb-2 justify-content-center star-group" data-speaker="{{ $speaker->id }}">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="rating-star" data-value="{{ $i }}">&#9733;</span>
                        @endfor
                        <input type="hidden" name="speaker_ratings[{{ $speaker->id }}]" value="" required>
                    </div>
                    <textarea name="speaker_comments[{{ $speaker->id }}]" class="form-control text-center" rows="1" placeholder="Comment (optional)"></textarea>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <button type="submit" class="btn w-100 py-3 text-white fw-bold" style="background-color:#006198;font-size:18px;">
            Submit Evaluation & Get Certificate
        </button>
    </form>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.star-group').forEach(function(group) {
        var stars = group.querySelectorAll('.rating-star');
        var hiddenInput = group.querySelector('input[type="hidden"]');

        stars.forEach(function(star) {
            star.addEventListener('click', function() {
                var value = parseInt(this.getAttribute('data-value'));
                hiddenInput.value = value;

                stars.forEach(function(s, idx) {
                    if (idx < value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });

            star.addEventListener('mouseenter', function() {
                var value = parseInt(this.getAttribute('data-value'));
                stars.forEach(function(s, idx) {
                    if (idx < value) {
                        s.style.color = '#e6a800';
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                stars.forEach(function(s) {
                    if (!s.classList.contains('active')) {
                        s.style.color = '';
                    } else {
                        s.style.color = '#ffc107';
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
