@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add Member</h2>
        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.members.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Member ID</label>
                        <input type="text" name="member_id" class="form-control" value="{{ old('member_id') }}" placeholder="e.g. IIA-001">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="participant" class="form-control" value="{{ old('participant') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email_address" class="form-control" value="{{ old('email_address') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Member" {{ old('status') === 'Member' ? 'selected' : '' }}>Member</option>
                            <option value="Non-Member" {{ old('status') === 'Non-Member' ? 'selected' : '' }}>Non-Member</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="is_executive" value="1" class="form-check-input" id="isExec" {{ old('is_executive') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isExec">Executive</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Credit (MWK)</label>
                        <input type="number" name="credit" step="0.01" min="0" class="form-control" value="{{ old('credit', 0) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Debt (MWK)</label>
                        <input type="number" name="debt" step="0.01" min="0" class="form-control" value="{{ old('debt', 0) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Save Member</button>
            </form>
        </div>
    </div>
</div>
@endsection
