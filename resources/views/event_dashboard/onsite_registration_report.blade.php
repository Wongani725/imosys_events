<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

@extends('layouts.app')

@section('content')
    <style>


        .report {
            margin-top: 30px;
        }

        .report table {
            width: 100%;
            border-collapse: collapse;
        }

        .report th,
        .report td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .report th {
            background-color: #f2f2f2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }
        .pdf-page {
            /*display: grid;*/
            /*grid-template-columns: repeat(2, 1fr);*/
            gap: 20px;
            /*margin-bottom: 40px;*/
        }
        .align-left-top {
            margin-top: 1%;
            margin-right: 15%;
            float: none;
            display: block;
            margin-left: 0;
            margin-right: auto;
            text-align: left;
        }
        align-left-top-2 {
            margin-top: 1%;
            margin-right: 15%;
            float: none;
            display: block;
            margin-left: 0;
            margin-left: auto;
            text-align: right;


        }

        #table1 {
            border-collapse: collapse;
            width: 100%;
        }
        #table1 th
        {
            background-color: #e7ae57;
        }
        #table1 th, #myTable td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;

        }

        /* Header Row Styling */
        #table1 thead tr {
            background-color: red;
            color: white;
        }
        .event-container{
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top:25px;
            padding: 5px;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-2">
                    <select onchange="location = this.value;" class="form-select float-start me-2" style="background-color: #e7ae57; color: white;">
                        <option selected disabled>Choose an event</option>
                        @foreach(\App\Models\Event::pluck('event_name') as $eventName)
                            <option value="{{ route('onsite-registration-report2', ['event' => $eventName]) }}">{{ $eventName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-8"></div>
                <div class="col-sm-2 pt-1">
                    <button class="btn btn-success float-end ms-2" onclick="downloadFormPDF()"><i class="fa fa-download"></i></button>
                </div>
            </div>

            <div class="pdf-page">
                <div class="row">
                    <div class="col-md-12">


                        <span>
                            <img src="{{ asset('logo.jpeg') }}" style="margin-top: 0.1%; width: 100px; height: 100px;" alt="Company Logo">
                        </span>
                        <b>
                            <h3 style="color: black; font-size: 16px;">Malawi Law Society</h3>
                        </b>
                        <br>


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <b> <h1 style="font-size: 24px; margin-top: -3%">{{ $event->event_name }} : Onsite Registration Report</h1> </b>
                        <h1 style="margin-top: -2%; font-size: 20px"></h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table id="table1">
                            <thead>
                            <tr>
                                <th>Participant ID</th>
                                <th>Participant Name</th>
                                <th>Status</th>
                                <th>Organisation</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($registrationsWithParticipants as $registration)
                                <tr>
                                    <td>{{ $registration->participant_id }}</td>
                                    <td>{{ $registration->participant }}</td>
                                    <td>{{ $registration->status }}</td>
                                    <td>{{ $registration->company_name }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="1"><strong>Total Participants: </strong></td>
                                <td><strong>{{ count($registrationsWithParticipants) }}</strong></td>
                                <td></td>
                                <td></td><td></td>

                            </tr>
                            </tbody>

                        </table>




                        <br>
                        <button onclick="downloadTable('table1')" style="background-color: #696cff; color: white;">Download Table 1 as Excel</button>

                        <script>
                            function downloadTable(tableId) {
                                var table = document.getElementById(tableId);
                                var html = table.outerHTML;
                                var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
                                var link = document.createElement('a');
                                link.download = tableId + '.xls';
                                link.href = url;
                                link.click();
                            }
                        </script>



                    </div>
                </div>



            </div>
        </div>

    </div>
    <div>
        <div class=" align-left-top" style="margin-top: 1%; margin-left: 1%; margin-right: 15%;">

        </div>




        <?php
        $index = 1; // Declare and initialize the variable
        $index++; // Increment the value of $index by 1
        ?>
        @if($index)


        </tbody>
        <tfoot>
        {{--                    <tr>--}}
        {{--                        /<td colspan="1">Total Redeemed:</td>--}}
        {{--                        <td>{{ $totalRedeemed }}</td>--}}
        {{--                    </tr>--}}
        </tfoot>
        </table>
    </div>


    </div>
    @endif


    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        h1 {
            font-size: 16px;
            /*color: rgb(237, 28, 36);*/
        }

        h2 {
            font-size: 20px;
            /*color: rgb(237, 28, 36);*/
        }

        .pdf-page {
            display: grid;
            grid-template-columns: repeat(1, 1fr);

            margin-bottom: 40px;
            /*justify-items: center;*/
            align-items: center;
            width: 100%;
        }

    </style>
    <script>
        function downloadFormPDF() {
            var pages = document.querySelectorAll(".pdf-page");
            var pdfContent = [];

            pages.forEach(function(page, index) {
                console.log("NEW PAGE")
                console.log(page)
                setTimeout(function() {
                    // Wait until the previous page is processed before capturing the next one
                    html2canvas(page, {
                        useCORS: true,
                        allowTaint: true,
                        scale: 2,
                        scrollX: 0,
                        scrollY: 0,
                        windowWidth: page.offsetWidth,
                        windowHeight: page.offsetHeight,
                        backgroundColor: null // Set background color to null for transparent background
                    }).then(function(canvas) {
                        var imgData = canvas.toDataURL("image/png");
                        var pdfPage = {
                            image: imgData,
                            fit: [595, 842] // Adjust the dimensions to match the A4 page size (595x842)
                        };
                        pdfContent.push(pdfPage);
                        if (pdfContent.length === pages.length) {
                            generatePDF();
                        }
                    });
                }, index * 2000);
            });

            function generatePDF() {
                var docDefinition = {
                    pageSize: "A4",
                    pageOrientation: "portrait", // Set the page orientation to portrait
                    content: pdfContent,
                };
                pdfMake.createPdf(docDefinition).download("EVENTS REPORT.pdf");
            }
        }
    </script>

@endsection
