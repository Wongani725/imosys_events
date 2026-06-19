@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Master Meal Tags</h2>
        <a href="{{ route('admin.master-meal-tags.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Tag</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Unique Code</th>
                            <th>Event</th>
                            <th>Member</th>
                            <th>Total Meals</th>
                            <th>QR Code</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tags as $tag)
                        <tr>
                            <td><code>{{ $tag->unique_code }}</code></td>
                            <td>{{ $tag->event->event_name ?? $tag->event_id }}</td>
                            <td>{{ $tag->member->participant ?? 'N/A' }}</td>
                            <td>{{ $tag->total_meals }}</td>
                            <td>
                                @if($tag->unique_code)
                                    <img src="{{ route('qrcode', $tag->unique_code) }}" width="50" height="50">
                                @endif
                            </td>
                            <td>{{ $tag->creator->name ?? 'N/A' }}</td>
                            <td>{{ $tag->created_at?->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.name-tags.master.single', $tag) }}" class="btn btn-sm btn-success" title="Download Name Tag">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('admin.master-meal-tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No master meal tags.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $tags->links() }}
        </div>
    </div>
</div>
@endsection
