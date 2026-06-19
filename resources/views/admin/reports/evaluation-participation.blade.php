@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Evaluation Response Rate'])

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #006198;">
                <h3 class="fw-bold text-primary mb-0">{{ $confirmed }}</h3>
                <small class="text-muted">Confirmed Participants</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #28a745;">
                <h3 class="fw-bold text-success mb-0">{{ $submissions }}</h3>
                <small class="text-muted">Evaluations Submitted</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid {{ $rate >= 70 ? '#28a745' : '#ffc107' }};">
                <h3 class="fw-bold mb-0 {{ $rate >= 70 ? 'text-success' : 'text-warning' }}">{{ $rate }}%</h3>
                <small class="text-muted">Response Rate</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Participant</th><th>Email</th><th>Company</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($participants as $p)
                    <tr>
                        <td>{{ $p->participant }}</td>
                        <td>{{ $p->email_address }}</td>
                        <td>{{ $p->company_name }}</td>
                        <td>{!! in_array($p->reference_code, $submittedRefs) ? '<span class="badge bg-success">Submitted</span>' : '<span class="badge bg-secondary">Not Submitted</span>' !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
