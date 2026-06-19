@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    @include('admin.reports._header', ['title' => 'Certificate Eligibility'])

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #006198;">
                <h3 class="fw-bold text-primary mb-0">{{ $confirmed->count() }}</h3>
                <small class="text-muted">Confirmed Participants</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #28a745;">
                <h3 class="fw-bold text-success mb-0">{{ count($submittedRefs) }}</h3>
                <small class="text-muted">Evaluation Completed</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3" style="border-left:4px solid #17a2b8;">
                <h3 class="fw-bold text-info mb-0">{{ $confirmed->count() - count($submittedRefs) }}</h3>
                <small class="text-muted">Pending Evaluation</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Company</th><th>Status</th><th>Certificate</th></tr>
                </thead>
                <tbody>
                    @foreach($confirmed as $p)
                    @php $submitted = in_array($p->reference_code, $submittedRefs); @endphp
                    <tr>
                        <td>{{ $p->participant }}</td>
                        <td>{{ $p->email_address }}</td>
                        <td>{{ $p->company_name }}</td>
                        <td>{!! $submitted ? '<span class="badge bg-success">Eligible</span>' : '<span class="badge bg-secondary">Missing Evaluation</span>' !!}</td>
                        <td>
                            @if($submitted)
                            <a href="{{ route('download_certificate_pdf', ['reference_code' => $p->reference_code, 'event_id' => $event->event_id]) }}" class="btn btn-sm btn-outline-success"><i class="bx bx-download"></i> Cert</a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
