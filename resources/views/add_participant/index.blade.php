@extends('layouts.app')

@section('title', env('APP_NAME').'| Form')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/bootstrap-select/bootstrap-select.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/tagify/tagify.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/formvalidation/dist/css/formValidation.min.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/dropzone/dropzone.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}
@endsection

@section('content')

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- FormValidation -->
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Add Participant</h5>
                <div class="card-body">
                    <form action = "{{ url('add_participant2') }}" method = "post" id="formValidationExamples" class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label class="form-group">Event Name:</label>
                            <input type="text" class="form-control" placeholder="Event name" name="Event_name" value="{{$event_name}}" disabled>

                            <input type="text" class="form-control" placeholder="Event id" name="event_id" value="{{$event_id}}" hidden>
                        </div>

                        <div class="col-md-6">
                            <label>Participant Name (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="participant name" name="participant" required>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Email Address (Optional)</label>
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="email address" name="email_address" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Status (required)</label>
                            <div class="input-group">
                                <select class="form-control" name="status" required>
                                    <option value="Member">Member</option>
                                    <option value="Non Member">Non Member</option>
                                    <option value="Student">Student</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Company Name (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="company name" name="company_name" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Gender (required)</label>
                            <div class="input-group">
                                <select class="form-control" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Phone Number (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="phone number" name="phone_number" required>
                            </div>

                        </div>

                        <div class="col-12">
                            <button type="submit"  value = "Add student" class="btn" style="background-color: #e7ae57; color: white;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- /FormValidation -->
    </div>


    </div>
@endsection

@section('vendors-js')
    <script src="{{asset('')}}cms/vendor/libs/select2/select2.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/moment/moment.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/tagify/tagify.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>
    <script src="{{asset('')}}cms/vendor/libs/dropzone/dropzone.js"></script>
@endsection

@section('page-js')
    <script>
        "use strict";!function(){
            var a=`<div class="dz-preview dz-file-preview">
                    <div class="dz-details">
                      <div class="dz-thumbnail">
                        <img data-dz-thumbnail>
                        <span class="dz-nopreview">No preview</span>
                        <div class="dz-success-mark"></div>
                        <div class="dz-error-mark"></div>
                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                        <div class="progress">
                          <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
                        </div>
                      </div>
                      <div class="dz-filename" data-dz-name></div>
                      <div class="dz-size" data-dz-size></div>
                    </div>
            </div>`;
            new Dropzone("#dropzone-basic",{previewTemplate:a,parallelUploads:1,maxFilesize:5,addRemoveLinks:!0,maxFiles:1}),
                new Dropzone("#dropzone-multi",{previewTemplate:a,parallelUploads:1,maxFilesize:5,addRemoveLinks:!0})}();
    </script>
    <script src="{{asset('')}}cms/js/form-validation.js"></script>
@endsection

