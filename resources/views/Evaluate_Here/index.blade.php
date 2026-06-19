@extends('layouts.app')

@section('title', 'Evaluation Questions')

@section('content')
<div class="container-fluid py-4">
    @if(session('success_message'))
        <div class="alert alert-success alert-dismissible">{{ session('success_message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('message'))
        <div class="alert alert-success alert-dismissible">{{ session('message') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Evaluation Questions</h2>
        <div>
            <a href="{{ route('create_evaluation_questions') }}/{{ $event_id ?? '' }}" class="btn btn-primary"><i class="bx bx-plus"></i> Create Question</a>
            <a href="{{ route('events') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="questions-table">
                <thead class="table-light">
                    <tr>
                        <th>Question</th>
                        <th>Section</th>
                        <th>Event ID</th>
                        <th>Type</th>
                        <th class="text-center" style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr data-id="{{ $row->id }}">
                        <td>{{ $row->Question }}</td>
                        <td><span class="badge bg-secondary">{{ $row->Section }}</span></td>
                        <td><code>{{ $row->Event_id }}</code></td>
                        <td><span class="badge bg-info">{{ $row->Type }}</span></td>
                        <td class="text-center">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-edit-alt"></i> Edit
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('edit_question', ['id' => $row->id]) }}">Edit Question</a></li>
                                    <li><a class="dropdown-item" href="{{ route('edit_options', ['id' => $row->id]) }}">Edit Options</a></li>
                                    <li><a class="dropdown-item" href="{{ route('edit_speakers', ['id' => $row->id]) }}">Edit Speakers</a></li>
                                </ul>
                            </div>
                            <a href="{{ route('delete_question', ['id' => $row->id, 'event_id' => $event_id]) }}"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this question?')">
                                <i class="bx bx-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No questions created yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endSection
