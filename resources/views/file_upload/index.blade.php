@extends('layouts.app')

@section('title', 'Events')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}
@endsection

<div class="container">

    <div class="card-header bg-secondary dark bgsize-darken-4 white card-header">

        <h4 class="text-white"></h4>

    </div>

    <div class="row justify-content-centre" style="margin-top: 4%">
        @section('content')
        <div class="col-md-8">

            <div class="card">

                <div class="card-header bgsize-primary-4 white card-header">

                    <h4 class="card-title">Import Excel Data</h4>

                </div>

                <div class="card-body">

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-block">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{ $message }}</strong>
                        </div>
                        <br>
                    @endif
                    <form action="{{url("import_file")}}" method="post" enctype="multipart/form-data">
                        @csrf

                        <fieldset>
                            <input type="text" class="form-control" placeholder="Event id" name="event_id" value="{{$event_id}}" hidden>
                            <label>Select File to Upload  <small class="warning text-muted">{{__('Please upload only Excel (.xlsx or .xls) files')}}</small></label>

                            <div class="input-group">

                                <input type="file" required class="form-control" name="uploaded_file" id="uploaded_file">

                                @if ($errors->has('uploaded_file'))

                                    <p class="text-right mb-0">

                                        <small class="danger text-muted" id="file-error">{{ $errors->first('uploaded_file') }}</small>

                                    </p>

                                @endif

                                <div class="input-group-append" id="button-addon2">

                                    <button class="btn btn-success square" type="submit"><i class="ft-upload mr-1"></i> Upload</button>

                                </div>

                            </div>

                        </fieldset>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <script>

        $(document).ready(function() {

            $('#example').DataTable();

        } );

    </script>



@endsection
