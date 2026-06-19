@extends('layouts.app')

@section('title', env('APP_NAME').' | Sponsors')

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="card">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    Sponsors
                    <button class="btn" style="background-color: #37a739; color: white;" data-bs-toggle="modal" data-bs-target="#addSponsorModal">
                        Add Sponsor
                    </button>
                </h5>
                <!-- Add Modal -->
                <div class="modal fade" id="addSponsorModal" tabindex="-1" aria-labelledby="addSponsorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('sponsors.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Sponsor Name</label>
                                        <input type="text" name="sponsor" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control" min="0" value="0">
                                        <small class="text-muted">Lower number = displayed first</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sponsor Image</label>
                                        <input type="file" name="file_path" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #37a739; color: white;">Save</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    @if($sponsors->count())
                        <div class="row">
                            @foreach($sponsors as $sponsor)
                                <div class="col-md-3 mb-4">
                                    <div class="border p-3 h-100">
                                        <h5>{{ $sponsor->sponsor }}</h5>
                                        <span class="badge bg-secondary">Priority: {{ $sponsor->priority ?? 0 }}</span><br/>
                                        @if($sponsor->file_path)
                                            <img src="{{ asset($sponsor->file_path) }}" alt="Sponsor Image" class="img-fluid mb-2" style="max-height:150px;">
                                        @endif <br/>
                                        <p><strong>Start:</strong> {{ $sponsor->start_date }}</p>
                                        <p><strong>End:</strong> {{ $sponsor->end_date }}</p>
                                        <button class="btn btn-sm" style="background-color: #37a739; color: white;" data-bs-toggle="modal" data-bs-target="#editSponsorModal{{ $sponsor->id }}">Edit</button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSponsorModal{{ $sponsor->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editSponsorModal{{ $sponsor->id }}" tabindex="-1" aria-labelledby="editSponsorModalLabel{{ $sponsor->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('sponsors.update', $sponsor->id) }}" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editSponsorModalLabel{{ $sponsor->id }}">Edit Sponsor</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sponsor Name</label>
                                                        <input type="text" name="sponsor" class="form-control" required value="{{ old('sponsor', $sponsor->sponsor) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Priority</label>
                                                        <input type="number" name="priority" class="form-control" min="0" value="{{ old('priority', $sponsor->priority ?? 0) }}">
                                                        <small class="text-muted">Lower number = displayed first</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" name="start_date" class="form-control" required value="{{ old('start_date', $sponsor->start_date) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" name="end_date" class="form-control" required value="{{ old('end_date', $sponsor->end_date) }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Sponsor Image</label>
                                                        <input type="file" name="file_path" class="form-control">
                                                        @if($sponsor->file_path)
                                                            <small class="d-block mt-1">Current: <a href="{{ asset($sponsor->file_path) }}" target="_blank">View</a></small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn" style="background-color: #37a739; color: white;">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteSponsorModal{{ $sponsor->id }}" tabindex="-1" aria-labelledby="deleteSponsorModalLabel{{ $sponsor->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('sponsors.destroy', $sponsor->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteSponsorModalLabel{{ $sponsor->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the sponsor "<strong>{{ $sponsor->sponsor }}</strong>"?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No sponsors added yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
