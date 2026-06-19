@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Attire Sizes</h2>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="event_id" class="form-select" onchange="this.form.submit()">
                @foreach($events as $ev)
                    <option value="{{ $ev->event_id }}" {{ $selectedEventId === $ev->event_id ? 'selected' : '' }}>{{ $ev->event_name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Add Size</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.attire-sizes.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Size Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. XL" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event</label>
                            <select name="event_id" class="form-select" required>
                                @foreach($events as $e)
                                    <option value="{{ $e->event_id }}" {{ $selectedEventId === $e->event_id ? 'selected' : '' }}>{{ $e->event_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-iia-blue"><i class="bx bx-save"></i> Add</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between">
                    <span>Existing Sizes</span>
                    <span class="badge bg-primary">{{ $sizes->total() }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Size</th><th>Event</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($sizes as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->name }}</td>
                                <td>{{ $s->event->event_name ?? $s->event_id }}</td>
                                <td>
                                    <form action="{{ route('admin.attire-sizes.destroy', $s) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete size {{ $s->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No sizes for this event.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($sizes->hasPages())<div class="card-footer">{{ $sizes->links() }}</div>@endif
            </div>
        </div>
    </div>
</div>
@endsection
