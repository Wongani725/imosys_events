@extends('layouts.web_app')

@section('title', 'Evaluation Form')

@push('styles')
<style>
    .evaluation-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .evaluation-card .card-header {
        background-color: #006198;
        color: #fff;
        border-radius: 16px 16px 0 0;
        padding: 14px;
        text-align: center;
        font-size: 24px;
    }
    .question-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    .radio-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .radio-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .speakers-table {
        width: 100%;
        border-collapse: collapse;
    }
    .speakers-table th, .speakers-table td {
        padding: 8px;
        border: 1px solid #dee2e6;
        text-align: center;
    }
    .speakers-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .section-heading {
        color: #006198;
        font-weight: 700;
        margin-top: 24px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e7ae57;
    }
    .btn-submit {
        background-color: #97D700;
        border: none;
        color: #fff;
        padding: 10px 40px;
        border-radius: 6px;
        font-size: 16px;
    }
    .btn-submit:hover {
        background-color: #7ab800;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="evaluation-card card">
        <div class="card-header">
            <h5 class="mb-0" style="color:#fff;">Evaluation Form</h5>
        </div>
        <div class="card-body p-4">
            <h4 class="text-center mb-3" style="color:#006198;">Dear Esteemed Member</h4>
            <p class="text-muted">
                We believe in continuous improvement and involvement of our members. Your feedback helps us engage in activities that add value to our members and the profession at large.
            </p>

            <form action="{{ route('evaluation-form', ['reference_code' => $reference_code, 'event_id' => $event_id]) }}" method="post" class="mt-4">
                @csrf

                @php
                    $sectionOrder = [
                        'PRE-ARRIVAL',
                        'ARRIVAL',
                        'CONFERENCE LOCATION/FACILITIES',
                        'CONFERENCE SESSIONS',
                        'SPEAKERS',
                        'IIA & ACTIVITIES/FUNCTIONS',
                    ];
                    $questionNumber = 1;
                @endphp

                @foreach ($sectionOrder as $section)
                    @php
                        $sectionQuestions = $questions->where('section', $section);
                    @endphp

                    @if ($sectionQuestions->count() > 0)
                        <h5 class="section-heading">{{ $section }}</h5>

                        @foreach ($sectionQuestions as $question)
                            <div class="mb-4">
                                <label class="question-label">{{ $questionNumber }}. {{ $question->questions }}</label>

                                @if ($question->type === 'radio')
                                    @if ($section === 'SPEAKERS' && $speakers->count() > 0)
                                        <table class="speakers-table">
                                            <thead>
                                                <tr>
                                                    <th>Speaker</th>
                                                    @foreach ($optionsByQuestion[$question->id] as $option)
                                                        <th style="width:120px;">{{ $option }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($speakers as $speaker)
                                                <tr>
                                                    <td style="width:200px;text-align:left;">{{ $speaker->name ?? $speaker->speaker_name }}</td>
                                                    @foreach ($optionsByQuestion[$question->id] as $option)
                                                        <td style="width:120px;">
                                                            <input type="radio" name="ratings[{{ $question->id }}][{{ $speaker->id }}]" value="{{ $option }}">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="radio-buttons">
                                            @foreach ($optionsByQuestion[$question->id] as $option)
                                                <label class="radio-label">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                @elseif ($question->type === 'text')
                                    <input type="text" name="text_answer[{{ $question->id }}]" class="form-control" style="max-width:400px;">
                                @endif
                                @php $questionNumber++; @endphp
                            </div>
                        @endforeach
                    @endif
                @endforeach

                <hr>

                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" name="Name" class="form-control" value="{{ $participantName }}" readonly style="background:#f0f0f0;max-width:400px;">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="Email" class="form-control" value="{{ $participantEmail }}" readonly style="background:#f0f0f0;max-width:400px;">
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

