@extends('layouts.app')

@section('title', 'Events')

@section('vendor-css')
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('cms/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-css')
@endsection

@section('content')
    <div class="container">
        @if ($message = Session::get('success'))
            <div id="success-alert" class="alert alert-success alert-dismissible fade show">
                <button type="button" class=" btn close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>

            <script>
                setTimeout(function () {
                    $('#success-alert').fadeOut('slow');
                }, 60000); // 1 minute
            </script>
    @endif

    <!-- Button trigger modal -->

            @if(auth()->check() && in_array(auth()->user()->email, [
                'info@imosys.mw'
            ]))
                <button type="button"
                        class="btn mb-4"
                        data-bs-toggle="modal"
                        data-bs-target="#exampleModal"
                        style="background-color: #006198; color: white;">
                    Import Members
                </button>
            @endif

   <button type="button" class="btn mb-4" data-bs-toggle="modal" data-bs-target="#addMemberModal" style="background-color: #006198; color: white;">
       Add Members
   </button>

   <form action="{{ route('fileupload') }}" method="GET" class="d-flex">
       <input type="text" name="search" class="form-control" placeholder="Search members..." value="{{ request()->input('search') }}">
       <button type="submit" class="btn ms-2"  style="background-color: #006198; color: white;">Search</button>
   </form>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Import Members</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <form action="{{ route('import_file') }}" method="POST" enctype="multipart/form-data">
                   @csrf
                   <input type="hidden" name="event_id" value="{{ $event_id }}">

                   <div class="form-group">
                       <label>
                           Select File to Upload
                           <small class="text-muted">(Only Excel: .xlsx or .xls)</small>
                       </label>
                       <div class="input-group">
                           <input type="file" class="form-control" name="uploaded_file" id="uploaded_file" required>

                           @if ($errors->has('uploaded_file'))
                               <p class="text-danger small mb-0" id="file-error">
                                   {{ $errors->first('uploaded_file') }}
                               </p>
                           @endif

                           <div class="input-group-append">
                               <button class="btn" type="submit" style="background-color: #006198; color: white;">
                                   <i class="ft-upload mr-1"></i> Upload
                               </button>
                           </div>
                       </div>
                   </div>
               </form>

           </div>
       </div>
   </div>
</div>
<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <form method="POST" action="{{ route('update_member') }}">
           @csrf
           @method('PUT')
           <input type="hidden" name="id" id="edit-id">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Edit Member Info</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">

                   <div class="mb-3">
                       <label class="form-label">Full Name *</label>
                       <input type="text" class="form-control" name="participant" id="edit-participant">
                   </div>

                   <div class="mb-3">
                       <label class="form-label">Company Name</label>
                       <input type="text" class="form-control" name="company_name" id="edit-company">
                   </div>
                   <div class="mb-3">
                       <label class="form-label">Address</label>
                       <input type="text" class="form-control" name="address" id="edit-address">
                   </div>
                   <div class="mb-3">
                       <label class="form-label">Phone Number</label>
                       <input type="text" class="form-control" name="phone_number" id="edit-phone">
                   </div>
                   <div class="mb-3">
                       <label class="form-label">Email</label>
                       <input type="email" class="form-control" name="email_address" id="edit-email">
                   </div>

                   <div class="mb-3">
                       <label class="form-label">Date Joined</label>
                       <input type="date" class="form-control" name="datejoined" id="edit-date">
                   </div>

               </div>
               <div class="modal-footer">
                   <button type="submit" class="btn btn-success">Save Changes</button>
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               </div>
           </div>
       </form>
   </div>
</div>

{{--            Add Member Modal--}}
            <div class="modal fade" id="addMemberModal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('add_member') }}">
                        @csrf
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">Add Member Info</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="participant" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email_address" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone_number">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Company Name *</label>
                                    <input type="text" class="form-control" name="company_name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date Joined *</label>
                                    <input type="date" class="form-control" name="datejoined" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="Member">Member</option>
                                        <option value="Non Member">Non Member</option>
                                        <option value="Student">Student</option>
                                    </select>
                                </div>



                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Add Member</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
   <h4 class="card-title">IIA MEMBERS</h4>

<div class="row mt-4">
   <div class="col-md-12">
       <table id="members-table" class="table table-striped table-bordered nowrap" style="width:100%;">
           <thead>
           <tr>
               <th>Member ID</th>
               <th>Member Name</th>
               <th>Contacts</th>
               <th>Company Name</th>
               <th>Status</th>
               <th>Address</th>
               <th>Actions</th>
           </tr>
           </thead>
           <tbody>
           @foreach ($data as $booker)
               <tr>
                   <td>{{ $booker->member_id }}</td>
                   <td>{{ $booker->participant }}</td>
                   <td>{{ $booker->email_address }} / {{ $booker->phone_number }}</td>
                   <td>{{ $booker->company_name }}</td>
                   <td>{{ $booker->status }}</td>
                   <td>{{ $booker->address }}</td>
                   <td>
                       <button type="button" class="btn btn-sm btn-primary edit-btn"
                               data-bs-toggle="modal"
                               data-bs-target="#editMemberModal"
                               data-id="{{ $booker->id }}"
                               data-participant="{{ $booker->participant }}"
                               data-company="{{ $booker->company_name }}"
                               data-address="{{ $booker->address }}"
                               data-phone="{{ $booker->phone_number }}"
                               data-email="{{ $booker->email_address }}"
                               data-datejoined="{{ $booker->datejoined }}"

                               Edit>Edit
                       </button>
                   </td>

               </tr>

           @endforeach
           </tbody>
       </table>
   </div>
</div>
</div>
@endsection

@section('page-js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
   // $('#members-table').DataTable({
   //     responsive: true,
   //     pageLength: 10,
   //     lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
   //     language: {
   //         search: "Search member:",
   //         lengthMenu: "Show _MENU_ entries"
   //     }
   // });

   $(document).ready(function () {
       $('#members-table').DataTable({
           responsive: true,
           pageLength: 10,
           lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
           language: {
               search: "Search booking:",
               lengthMenu: "Show _MENU_ entries"
           },
           // Enable search across all data, even in the hidden rows
           searching: true,
           paging: true, // Keeps pagination working
       });
   });

   // Bind modal population logic
   $(document).on('click', '.edit-btn', function () {
       $('#edit-id').val($(this).data('id'));
       $('#edit-participant').val($(this).data('participant'));

       $('#edit-company').val($(this).data('company'));
       $('#edit-address').val($(this).data('address'));
       $('#edit-phone').val($(this).data('phone'));
       $('#edit-email').val($(this).data('email'));
       $('#edit-date').val($(this).data('datejoined'));
       $('#edit-password').val($(this).data('password'));
   });
});
</script>
@endsection
