@extends('layouts.app')

@section('title', 'Edit Speakers')

@section('content')
<div class="container-fluid py-4">
    @if(session('success_message'))
        <div class="alert alert-success alert-dismissible">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Speakers</h2>
        <a href="{{ route('evaluate-here', ['id' => $question->event_id]) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_speakers', ['id' => $question->id]) }}" method="post">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" value="{{ $question->questions }}" disabled>
                </div>

                <div class="mb-3">
                    <div>
                        <button type="button" class="add-speaker btn btn-sm btn-outline-primary mb-2"><i class="bx bx-plus"></i> Add Speaker</button>
                    </div>
                    <div class="speakers-container">
                        @foreach($speakers as $index => $speaker)
                        <div class="speaker input-group mb-2">
                            <input type="text" name="speakers[{{ $index }}]" class="form-control" value="{{ $speaker }}">
                            <button type="button" class="delete-speaker btn btn-outline-danger btn-sm">Delete</button>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3"><i class="bx bx-save"></i> Update Speakers</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('.speakers-container');
        let speakerIndex = {{ count($speakers) }};
        document.querySelector('.add-speaker').addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'speaker input-group mb-2';
            div.innerHTML = `<input type="text" name="speakers[${speakerIndex}]" class="form-control"><button type="button" class="delete-speaker btn btn-outline-danger btn-sm">Delete</button>`;
            container.appendChild(div);
            speakerIndex++;
        });
        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-speaker')) e.target.closest('.speaker').remove();
        });
    });
</script>
@endSection
