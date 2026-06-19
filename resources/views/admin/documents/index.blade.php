@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Documents — {{ $event->event_name }}</h2>
        <a href="{{ route('events') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3">
        {{-- Upload Form --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="bx bx-upload"></i> Upload Document</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.documents.store', $event->event_id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Max 20MB. PDF, DOC, JPG, PNG</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="brochure">Brochure</option>
                                    <option value="program">Program</option>
                                    <option value="report">Report</option>
                                    <option value="document">Document</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" name="is_public" value="1" class="form-check-input" id="isPublic" checked>
                                    <label class="form-check-label" for="isPublic">Public</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-iia-blue"><i class="bx bx-upload"></i> Upload</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Document List --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between">
                    <span>Uploaded Documents</span>
                    <span class="badge bg-primary">{{ $documents->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Title</th><th>Type</th><th>Visibility</th><th>Uploaded</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $d)
                            <tr>
                                <td class="fw-semibold">{{ $d->title }}</td>
                                <td><span class="badge bg-info">{{ $d->type }}</span></td>
                                <td>{!! $d->is_public ? '<span class="badge bg-success">Public</span>' : '<span class="badge bg-secondary">Private</span>' !!}</td>
                                <td>{{ $d->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $d->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                    <form action="{{ route('admin.documents.destroy', ['event_id' => $event->event_id, 'document' => $d]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete {{ $d->title }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
