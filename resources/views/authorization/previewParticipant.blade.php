@extends('layouts.app')

@section('title', env('APP_NAME').'| Approval Form')

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
    @php
        $i= 0;
    @endphp

    @if ($participant_details)
    <div class="col-12">
                            <a class="btn btn-success" style='color:white;' href="{{ route ('auth.approve')}}/{{ $reference_id }}" >Approve</a>
                            <!--a class="btn btn-warning" style='color:white;' href="{{ route ('auth.updateApprove')}}/{{ $reference_id }}" >Update and Approve</a-->
                            <a class="btn btn-danger" style='color:white;' href="{{ route ('auth.decline')}}/{{ $reference_id }}" >Decline</a>
    </div>
    @endif         
    @foreach($participant_details as $participant)
    @php
        $i++;
    @endphp
    <div class="row" style='margin-bottom:20px'>
        <!-- FormValidation -->
        <div class="col-12">

            <div class="card">
                <h5 class="card-header">Preview Participant: {{$i}}</h5>
                <div class="card-body">


                    <form action = "" method = "post" id="formValidationExamples" class="row g-3">
                        <input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>"><input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>">

                        <div class="col-md-6">
                            <label class="form-group">Event Name:</label>
                            <input type="text" class="form-control" placeholder="Event name" name="Event_name" value="{{$event_name}}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-group">Reference Code (required)</label>

                            <div class="input-group">
                                <input disabled type="text" class="form-control" disabled placeholder="reference code" name="reference_code" value='{{$participant->reference_code}}' required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-group">Meals (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="meals" name="meals" value='{{$participant->meals}}' required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Extra Meals (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="extra meals" value='{{$participant->extra_meals}}' name="extra_meals" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Participant Name (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control"   disabled placeholder="participant name" name="participant" value='{{$participant->participant}}' required>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Email Address (required)</label>
                            <div class="input-group">
                                <input type="text"  disabled class="form-control" placeholder="email address" name="email_address" value='{{$participant->email_address}}' required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Status (required)</label>
                            <div class="input-group">
                                <select class="form-control" name="status" disabled required>
                                    <option value="{{$participant->status}}" disabled>Member</option>
                                    <option value="Member">Member</option>
                                    <option value="Non Member">Non Member</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                                <label>Company Name (required)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" disabled placeholder="company name" value='{{$participant->company_name}}' name="company_name" required>
                                </div>
                            </div>

                        <div class="col-md-6">
                            <label>Position (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="position" value='{{$participant->position}}' name="position" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Gender (required)</label>
                            <div class="input-group">
                                <select class="form-control" name="gender" disabled required>
                                    <option value="Male" value='{{$participant->gender}}' hidden>Male</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label>Balance (required)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="balance" value='{{$participant->balance}}' name="balance" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Phone Number (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control"  disabled placeholder="phone number" value='{{$participant->phone_number}}' name="phone_number" required>
                            </div>

                        </div>


                        <div class="col-md-6">
                            <label>Attire Type (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="attire type" value='{{$participant->attire_type}}' name="attire_type" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Attire Size (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled placeholder="attire size" value='{{$participant->attire_size}}' name="attire_size" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Hotel (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" disabled value='{{$participant->hotel}}' placeholder="hotel" name="hotel" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Room Type (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="room type" disabled value='{{$participant->room_type}}' name="room_type" >
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label>Room Number (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="room number" disabled value='{{$participant->room_number}}' name="room_number" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>No. of Extra bed (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="No. of extra bed" disabled value='{{$participant->extra_bed}}' name="no_of_extra_bed">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Invoice Reference (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="invoice reference" disabled value='{{$participant->invoice_number}}' name="invoice_reference" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Lunch hotel (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="lunch hotels" disabled value='{{$participant->lunch_hotel}}' name="lunch_hotels" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Dinner hotel (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="dinner hotel" disabled value='{{$participant->dinner_hotel}}' name="dinner_hotel" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Hotel fees (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="hotel fees" disabled value='{{$participant->hotel_fees}}' name="hotel_fees" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Cost / meal (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="cost per meal" disabled value='{{$participant->cost_per_meal}}' name="cost_per_meal" >
                            </div>
                        </div>
                        <div class="col-md-6">
                        <label>Meals total cost (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="meals total cost" disabled value='{{$participant->meals_total_cost}}' name="meals_total_cost" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Breakfast fees (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="breakfast fees" disabled value='{{$participant->breakfast_fees}}' name="breakfast_fees" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>No. of breakfast (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="No. of breakfast" disabled value='{{$participant->no_of_breakfast}}' name="no_of_breakfast" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Extra bed (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="extra bed" disabled value='{{$participant->extra_bed}}' name="extra_bed" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Total hotel extra fees (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="total hotel extra fees" disabled value='{{$participant->total_hotel_extra_fees}}' name="total_hotel_extra_fees" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Participation fees (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="participation fees" disabled value='{{$participant->participation_fees}}' name="participation_fees" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Total amount (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="total amount" disabled value='{{$participant->total_amount}}' name="total_amount" >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Amount paid (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="amount paid" disabled value='{{$participant->amount_paid}}' name="amount_paid" >
                            </div>
                        </div>


                        <div class="col-md-6">
                            <label>Receipt number (Optional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="receipt number" disabled  value='{{$participant->receipt_number}}' name="receipt_number" >
                            </div>
                        </div>


                    </form>
                    

                </div>
            </div>
        </div>
        <!-- /FormValidation -->
    </div>
    @endforeach

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

