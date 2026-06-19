@extends('layouts.app')

@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <title>ICAM participant | Edit</title>
    <style>

        .container {
            border: 2px solid white;
            padding: 10px;
            width: 100%;
            margin: 0 auto;
            border-radius: 5px;
            background-color:white;
            hieght:70%;}

        .submit-button {
            margin-bottom: 70%;
            background-color: #8c8c8c; /* Set the background color */
            color:black; /* Set the text color */
            padding: 8px 15px; /* Adjust the padding as needed */
            border: none; /* Remove the default border */
            border-radius: 5px; /* Add rounded corners */
            font-size: 16px; /* Set the font size */
            cursor: pointer; /* Add a pointer cursor on hover */
        }
        table
        {
            margin: auto;
            background-color: white;
            padding: 40px;
        <!--- color: white;-->
        }
        tr
        {

            height: 60px;
            align-items: center;
            align-content: center;
            margin: auto;

        }
        div
        {
            align-items: center;
            align-items: center;
            margin: auto;
        <!--margin-left: 3%;-->
        }
        form
        {
            align-content: center;
            align-items: center;
            margin: auto;
        }

        td {

        }

        H1
        {
            margin: auto;
            text-align: center;
            font-size: 0.5em;
        }

    </style>




</head>
<body>
@section('content')

    <div class="container">
        <form action = "{{route('update_participant')}}" method = "post" class="form-group" style="width:70%; margin-left:15%;
     background-color: white; padding: 40px;">

            @csrf


            <input type = "hidden" name = "_token" value = "<?php echo csrf_token(); ?>">
            <input type="hidden" name="id" value="{{$data[0]->reference_code}}">
            <H1>Update Participant</H1>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="reference_code">Reference Code</label>
                    <input type="text" class="form-control" name="reference_code" value="<?php echo$data[0]->reference_code; ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="participant">Participant Name</label>
                    <input type="text" class="form-control" name="participant" value="<?php echo$data[0]->participant; ?>">
                </div>


                <div class="form-group col-md-6">
                    <label for="email_address">Email</label>
                    <input type="email" class="form-control" name="email_address" value="<?php echo$data[0]->email_address; ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" name="phone_number" value="<?php echo$data[0]->phone_number; ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="company_name">Company Name</label>
                    <input type="text" class="form-control" name="company_name" value="<?php echo$data[0]->company_name; ?>">
                </div>


                <div class="form-group col-md-6">
                    <label for="status">Status</label>
                    <select class="form-control" name="status">
                        <option value="Member" <?php if ($data[0]->status === 'Member') echo 'selected'; ?>>Member</option>
                        <option value="Non Member" <?php if ($data[0]->status === 'Non Member') echo 'selected'; ?>>Non Member</option>
                    </select>
                </div>


                <div class="form-group col-md-6">
                    <label for="status">Position</label>
                    <input type="text" class="form-control" name="position" value="<?php echo$data[0]->position; ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="status">Gender</label>
                    <select class="form-control" name="gender">
                        <option value="Male" <?php if ($data[0]->gender === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($data[0]->gender === 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>


                <input type = 'submit' value = "Update" class="submit-button" />
            </div>
        </form>
    </div>
@endsection
</body>
</html>

