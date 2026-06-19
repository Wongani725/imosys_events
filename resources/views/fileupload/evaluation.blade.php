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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- add js script to be included in head section--}}
@endsection

@section('content')
<div class="card" style=" text-align: center; width:100%; margin-top: 1%; align-content: center">






    <div class="card-body">

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
            <br>
        @endif



            <form action="{{ route('import_evaluation') }}" method="POST">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event_id }}">
                <button type="submit" class="btn btn-primary square"><i class="ft-upload mr-1"></i> Email evaluations</button>
            </form>

    </div>

</div>

<br>
<a href="{{ route('doughnut-charts.show', ['evaluationId' => $evaluationId]) }}" class="btn btn-primary">View Doughnut Charts</a>

<a href="{{ route('show-graph') }}" class="btn btn-primary">Show Graph</a>
<a href="{{ route('evaluation-data') }}" class="btn btn-primary">View Evaluation Data</a>



    <!-- Ajax Sourced Server-side -->
<br>
    <div class="card">
        <h5 class="card-header" style="background-color: #696cff; margin-top: 2%; text-align: center; color:white;font-size: 24px;">Participants Evaluation</h5>
        <br>


        <div class="card-body text-nowrap">


            <br>
            <h2 style="text-align: center;">Table of Evaluations</h2>
            <div class="table-responsive">
                <table id="datatables-table" class="table table-bordered">
                    <thead>
                    <tr>

                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
{{--                        <th>Question ID</th>--}}
{{--                        <th>Radio Answer</th>--}}
{{--                        <th>Text Answer</th> <!-- Display the text_answer -->--}}
{{--                        <th>Rating</th>--}}
{{--                        <th>Participant name</th>--}}
{{--                        <th>Email</th>--}}
{{--                        <th>Feedback</th>--}}
{{--                        <th>Q1</th>--}}
{{--                        <th>Q2</th>--}}
{{--                        <th>Q3</th>--}}
{{--                        <th>Q4</th>--}}
{{--                        <th>Q5</th>--}}
{{--                        <th>Q6</th>--}}
{{--                        <th>Q7</th>--}}
{{--                        <th>Q8</th>--}}
{{--                        <th>Q9</th>--}}
{{--                        <th>Q10</th>--}}
{{--                        <th>Q11</th>--}}
{{--                        <th>Q12</th>--}}
{{--                        <th>Q13</th>--}}
{{--                        <th>Q14</th>--}}
{{--                        <th>Q15</th>--}}
{{--                        <th>Q16</th>--}}
{{--                        <th>Q17</th>--}}
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($evaluations as $evaluation)
                        <tr>
{{--                            <td>{{$evaluation->Name}}</td>--}}
{{--                            <td>{{$evaluation->Email}}</td>--}}
{{--                            <td>{{$evaluation->Feedback}}</td>--}}
{{--                            <td>{{$evaluation->q1}}</td>--}}
{{--                            <td>{{$evaluation->q2}}</td>--}}
{{--                            <td>{{$evaluation->q3}}</td>--}}
{{--                            <td>{{$evaluation->q4}}</td>--}}
{{--                            <td>{{$evaluation->q5}}</td>--}}
{{--                            <td>{{$evaluation->q6}}</td>--}}
{{--                            <td>{{$evaluation->q7}}</td>--}}
{{--                            <td>{{$evaluation->q8}}</td>--}}
{{--                            <td>{{$evaluation->q9}}</td>--}}
{{--                            <td>{{$evaluation->q10}}</td>--}}
{{--                            <td>{{$evaluation->q11}}</td>--}}
{{--                            <td>{{$evaluation->q12}}</td>--}}
{{--                            <td>{{$evaluation->q13}}</td>--}}
{{--                            <td>{{$evaluation->q14}}</td>--}}
{{--                            <td>{{$evaluation->q15}}</td>--}}
{{--                            <td>{{$evaluation->q16}}</td>--}}
{{--                            <td>{{$evaluation->q17}}</td>--}}

                            <td>{{ $evaluation->name }}</td>
                            <td>{{ $evaluation->email }}</td>
                            <td><a href="{{ route('view-evaluation', ['evaluationId' => $evaluation->id]) }}" class="btn btn-primary">View Evaluation</a></td> <!-- Display the question text -->
{{--                            <td>{{ $evaluation->question_id }}</td>--}}
{{--                            <td>{{ $evaluation->answer }}</td>--}}
{{--                            <td>{{ $evaluation->text_answer }}</td> <!-- Display the text_answer -->--}}
{{--                            <td>{{ $evaluation->rating }}</td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <br>

        <!-- Add a centered heading before the chart -->
{{--        <h2 style="text-align: center;">Statistical Graph for Q1 to Q13 Evaluations</h2>--}}
{{--        <canvas id="evaluationChart"></canvas>--}}


        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <script>

        $(document).ready(function() {

            $('#example').DataTable();

        } );

    </script>




@endsection

        @section('vendors-js')
            <script src="{{asset('')}}cms/vendor/libs/datatables/jquery.dataTables.js"></script>
            <script src="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
            <script src="{{asset('')}}cms/vendor/libs/datatables-responsive/datatables.responsive.js"></script>
            <script src="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js"></script>
            <!-- Flat Picker -->
            <script src="{{asset('')}}cms/vendor/libs/moment/moment.js"></script>
            <script src="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.js"></script>
            <script src="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.js"></script>


        @endsection
        @section('page-js')
{{--            <script>--}}
{{--                // Assuming you already have an array of objects containing evaluation data (evaluationData) in your view--}}
{{--                var evaluations = @json($evaluationData);--}}

{{--                // Create an array to hold the average values for q1 to q13--}}
{{--                var averages = [];--}}

{{--                // Calculate average ratings for each question (q1 to q13)--}}
{{--                for (var i = 1; i <= 13; i++) {--}}
{{--                    var questionColumn = "q" + i;--}}
{{--                    var ratings = evaluations.map(evaluation => evaluation[questionColumn]).filter(value => value !== null);--}}
{{--                    var average = ratings.length > 0 ? ratings.reduce((a, b) => a + b, 0) / ratings.length : 0;--}}
{{--                    averages.push(average.toFixed(2));--}}
{{--                }--}}

{{--                // Chart.js data and options--}}
{{--                var chartData = {--}}
{{--                    labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'Q9', 'Q10', 'Q11', 'Q12', 'Q13'],--}}
{{--                    datasets: [--}}
{{--                        {--}}
{{--                            label: 'Q1 to Q13 Evaluation',--}}
{{--                            data: averages,--}}
{{--                            backgroundColor: 'rgba(75, 192, 192, 0.2)', // Change the color as per your preference--}}
{{--                            borderColor: 'rgba(75, 192, 192, 1)', // Change the color as per your preference--}}
{{--                            borderWidth: 1,--}}
{{--                        },--}}
{{--                    ],--}}
{{--                };--}}

{{--                var chartOptions = {--}}
{{--                    responsive: true,--}}
{{--                    scales: {--}}
{{--                        y: {--}}
{{--                            beginAtZero: true,--}}
{{--                            max: 5, // Set the max value based on your rating scale (1 to 5 in this case)--}}
{{--                            stepSize: 1, // The step size between ticks on the y-axis--}}
{{--                        },--}}
{{--                    },--}}
{{--                };--}}

{{--                // Get the context of the canvas element where the chart will be drawn--}}
{{--                var ctx = document.getElementById('evaluationChart').getContext('2d');--}}

{{--                // Create the chart--}}
{{--                var myChart = new Chart(ctx, {--}}
{{--                    type: 'bar', // Set the chart type to 'bar'--}}
{{--                    data: chartData,--}}
{{--                    options: chartOptions,--}}
{{--                });--}}
{{--            </script>--}}


            <script>
                $(document).ready(function() {
                    $('#datatables-table').DataTable({
                        responsive: true
                    });
                });

                function sweetAlert() {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, delete it!",
                        customClass: {confirmButton: "btn btn-primary me-3", cancelButton: "btn btn-label-secondary"},
                        buttonsStyling: !1
                    }).then(function (t) {
                        t.value ? Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            customClass: {confirmButton: "btn btn-success"}
                        }) : t.dismiss === Swal.DismissReason.cancel && Swal.fire({
                            title: "Cancelled",
                            text: "Your imaginary file is safe :)",
                            icon: "error",
                            customClass: {confirmButton: "btn btn-success"}
                        })
                    })
                }
            </script>


@endsection
