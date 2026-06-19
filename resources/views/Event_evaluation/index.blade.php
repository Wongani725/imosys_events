@extends('layouts.app')

@section('title', 'Create Evaluation Question')

@section('content')
<div class="container-fluid py-4">
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('success_message'))
        <div class="alert alert-success alert-dismissible">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create Evaluation Question</h2>
        <a href="{{ route('evaluate-here', ['id' => $event_id]) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('evaluation_questions') }}/{{ $event_id ?? '' }}" method="post">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Question <span class="text-danger">*</span></label>
                            <input type="text" name="Question" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select name="Section" class="form-select" required>
                                <option value="" disabled selected>Select a Section</option>
                                <option value="PRE-ARRIVAL">PRE-ARRIVAL</option>
                                <option value="ARRIVAL">ARRIVAL</option>
                                <option value="CONFERENCE LOCATION/FACILITIES">CONFERENCE LOCATION/FACILITIES</option>
                                <option value="CONFERENCE SESSIONS">CONFERENCE SESSIONS</option>
                                <option value="SPEAKERS">SPEAKERS</option>
                                <option value="IIA &amp; ACTIVITIES/FUNCTIONS">IIA &amp; ACTIVITIES/FUNCTIONS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select name="Type" class="form-select" required>
                                <option value="text">Text</option>
                                <option value="radio">Radio</option>
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
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Speakers</label>
                            <div>
                                <button type="button" class="add-speaker btn btn-sm btn-outline-primary mb-2"><i class="bx bx-plus"></i> Add Speaker</button>
                            </div>
                            <div class="speakers-container">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3"><i class="bx bx-save"></i> Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
    const optionsContainer = document.querySelector('.options-container');
    const addOptionButton = document.querySelector('.add-option');

    addOptionButton.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'option input-group mb-2';
        div.innerHTML = `
            <input type="text" name="options[0][]" class="form-control" placeholder="Option value">
            <button type="button" class="delete-option btn btn-outline-danger btn-sm">Delete</button>
        `;
        optionsContainer.appendChild(div);
    });

    optionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-option')) {
            e.target.closest('.option').remove();
        }
    });

    const speakersContainer = document.querySelector('.speakers-container');
    const addSpeakerButton = document.querySelector('.add-speaker');

    addSpeakerButton.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'speaker input-group mb-2';
        div.innerHTML = `
            <input type="text" name="speakers[]" class="form-control" placeholder="Speaker name">
            <button type="button" class="delete-speaker btn btn-outline-danger btn-sm">Delete</button>
        `;
        speakersContainer.appendChild(div);
    });

    speakersContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-speaker')) {
            e.target.closest('.speaker').remove();
        }
    });
</script>
@endSection
