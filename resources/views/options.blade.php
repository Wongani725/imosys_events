@extends('layouts.app')

@section('title', 'Edit Options')

@section('content')
<div class="container-fluid py-4">
    @if(session('success_message'))
        <div class="alert alert-success alert-dismissible">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Options</h2>
        <a href="{{ route('evaluate-here', ['id' => $question->event_id]) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('update_options', ['id' => $question->id]) }}" method="post">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" value="{{ $question->questions }}" disabled>
                </div>

                <div class="mb-3">
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

                <button type="submit" class="btn btn-primary mt-3"><i class="bx bx-save"></i> Update Options</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('.options-container');
        document.querySelector('.add-option').addEventListener('click', () => {
            const div = document.createElement('div');
            div.className = 'option input-group mb-2';
            div.innerHTML = '<input type="text" name="options[0][]" class="form-control"><button type="button" class="delete-option btn btn-outline-danger btn-sm">Delete</button>';
            container.appendChild(div);
        });
        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-option')) e.target.closest('.option').remove();
        });
    });
</script>
@endSection
