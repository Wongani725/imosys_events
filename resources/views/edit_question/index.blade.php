@extends('layouts.app')

@section('title', 'Edit Question')

@section('content')
<div class="container-fluid py-4">
    @if(session('success_message'))
        <div class="alert alert-success alert-dismissible">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Evaluation Question</h2>
        <a href="{{ route('evaluate-here', ['id' => $question->event_id]) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_question') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $question->id }}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Question <span class="text-danger">*</span></label>
                            <input type="text" name="Question" class="form-control" value="{{ $question->questions }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select name="Section" class="form-select" required>
                                <option value="">Select a Section</option>
                                <option value="PRE-ARRIVAL" {{ $question->section == 'PRE-ARRIVAL' ? 'selected' : '' }}>PRE-ARRIVAL</option>
                                <option value="ARRIVAL" {{ $question->section == 'ARRIVAL' ? 'selected' : '' }}>ARRIVAL</option>
                                <option value="CONFERENCE LOCATION/FACILITIES" {{ $question->section == 'CONFERENCE LOCATION/FACILITIES' ? 'selected' : '' }}>CONFERENCE LOCATION/FACILITIES</option>
                                <option value="CONFERENCE SESSIONS" {{ $question->section == 'CONFERENCE SESSIONS' ? 'selected' : '' }}>CONFERENCE SESSIONS</option>
                                <option value="SPEAKERS" {{ $question->section == 'SPEAKERS' ? 'selected' : '' }}>SPEAKERS</option>
                                <option value="IIA &amp; ACTIVITIES/FUNCTIONS" {{ $question->section == 'IIA & ACTIVITIES/FUNCTIONS' ? 'selected' : '' }}>IIA &amp; ACTIVITIES/FUNCTIONS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event ID <span class="text-danger">*</span></label>
                            <input type="text" name="Event_id" class="form-control" value="{{ $question->event_id }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select name="Type" class="form-select" required>
                                <option value="text" {{ $question->type == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="radio" {{ $question->type == 'radio' ? 'selected' : '' }}>Radio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div>
                                <button type="button" class="add-option btn btn-sm btn-outline-primary mb-2"><i class="bx bx-plus"></i> Add Option</button>
                            </div>
                            <div class="options-container">
                                @foreach($options as $option)
                                <div class="option input-group mb-2">
                                    <input type="text" name="options[0][]" class="form-control" value="{{ $option->value }}">
                                    <button type="button" class="delete-option btn btn-outline-danger btn-sm">Delete</button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Speakers</label>
                            <div>
                                <button type="button" class="add-speaker btn btn-sm btn-outline-primary mb-2"><i class="bx bx-plus"></i> Add Speaker</button>
                            </div>
                            <div class="speakers-container">
                                @foreach($speakers as $speaker)
                                <div class="speaker input-group mb-2">
                                    <input type="text" name="speakers[]" class="form-control" value="{{ $speaker }}">
                                    <button type="button" class="delete-speaker btn btn-outline-danger btn-sm">Delete</button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3"><i class="bx bx-save"></i> Update Question</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const optionsContainer = document.querySelector('.options-container');
        const addOptionButton = document.querySelector('.add-option');
        const speakersContainer = document.querySelector('.speakers-container');
        const addSpeakerButton = document.querySelector('.add-speaker');

        addOptionButton.addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'option input-group mb-2';
            div.innerHTML = '<input type="text" name="options[0][]" class="form-control"><button type="button" class="delete-option btn btn-outline-danger btn-sm">Delete</button>';
            optionsContainer.appendChild(div);
        });

        optionsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-option')) e.target.closest('.option').remove();
        });

        addSpeakerButton.addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'speaker input-group mb-2';
            div.innerHTML = '<input type="text" name="speakers[]" class="form-control"><button type="button" class="delete-speaker btn btn-outline-danger btn-sm">Delete</button>';
            speakersContainer.appendChild(div);
        });

        speakersContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-speaker')) e.target.closest('.speaker').remove();
        });
    });
</script>
@endSection
