@extends('layouts.app')

@section('title', env('APP_NAME').' | Terms and Conditions')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <a href="{{ route('events') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bx bx-arrow-back"></i>
                        </a>
                        Terms and Conditions
                        @if($event)
                            <small class="text-muted ms-2">— {{ $event->event_name }} ({{ $event->event_id }})</small>
                        @endif
                    </span>
                    @if($event)
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termsModal">
                            {{ $terms ? 'Edit' : 'Add' }} Terms
                        </button>
                    @endif
                </h5>
                <div class="card-body">
                    @if($terms)
                        <div class="border p-3" style="white-space: pre-line;">
                            {!! nl2br($terms->terms) !!}
                        </div>
                    @elseif($event)
                        <p class="text-muted">No terms added for this event yet.</p>
                    @else
                        <p class="text-muted">Select an event to manage its terms and conditions.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($event)
    <!-- Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ $terms ? route('terms.update', $terms->id) : route('terms.store') }}">
                    @csrf
                    @if($terms)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">{{ $terms ? 'Edit' : 'Add' }} Terms and Conditions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Event</label>
                            <input type="text" class="form-control" value="{{ $event->event_name }} ({{ $event->event_id }})" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="termsContent" class="form-label">Content</label>
                            <textarea name="content" id="termsContent" class="form-control" rows="10" required>{{ old('content', $terms->terms ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">{{ $terms ? 'Update' : 'Add' }}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endSection
