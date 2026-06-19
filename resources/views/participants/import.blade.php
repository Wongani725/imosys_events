@extends('layouts.app') <!-- or your actual layout -->

@section('content')
    <div class="container mt-5">
        <h4>Upload Excel File for Event ID: {{ $id }}</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('import_members') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="excel_file">Choose Excel File:</label>
                <input type="file" name="excel_file" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">Import</button>
        </form>
    </div>
@endsection
