@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Import Members</h2>
        <div>
            <a href="{{ route('admin.members.template') }}" class="btn btn-success me-2">
                <i class="bx bx-download"></i> Download Template
            </a>
            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted">
                Upload an Excel file (.xls, .xlsx, .csv) with the following columns:
            </p>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" style="max-width:500px;">
                    <thead class="table-light">
                        <tr>
                            <th><code>member_id</code></th>
                            <th><code>name</code> <span class="text-danger">*</span></th>
                            <th><code>email</code> <span class="text-danger">*</span></th>
                            <th><code>phone</code></th>
                            <th><code>company</code></th>
                            <th><code>is_executive</code></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>IIA-001</td>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>0999123456</td>
                            <td>ACME Corp</td>
                            <td>No</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small">Existing members are updated by email. New members get status "Member" by default.</p>

            <form method="POST" action="{{ route('admin.members.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx,.csv" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Import</button>
            </form>
        </div>
    </div>
</div>
@endsection
