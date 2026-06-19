<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>

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

                <div class="row mb-3">
                    <div class="col-sm-2">
                        <select onchange="location = this.value;" class="form-select float-start me-2">
                            <option selected disabled>Choose an event</option>
                            @foreach(\App\Models\Event::pluck('event_name') as $eventName)
                                <option value="{{ route('event-report2', ['event' => $eventName]) }}">{{ $eventName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-8"></div>
                    <div class="col-sm-2 pt-1">
                        <button class="btn btn-danger btn  text-white btn-block float-end ms-2" onclick="downloadFormPDF()"><i class="fa ">Download Report</i> </button>
                    </div>
                </div>

                <div class="pdf-page">
                    <div class="row">
                        <div class="col-md-12">


                            <span><img src="{{ url('/images/ICAM_logo.png') }}" style="margin-top: 2%;width: 150px; height:40px;"></span>
                            <b>  <h3 style="color:black; font-size:16px; " >The Institute of Chartered Accountants in Malawi</h3></b>
                            <p style="color:black; font-size:12px; margin-top: -1%; padding-top: 1px;">icam@icam.mw | P.O Box 1,Blantyre | 01895216 | www.icam.mw</p>
                            <br>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <b> <h1 style="font-size: 24px; margin-top: -3%">{{ $event->event_name }} : Number of meal coupons redeemed per hotel report</h1> </b>
                            <h1 style="margin-top: -2%; font-size: 20px"></h1>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table>
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Hotel</th>
                                    {{--            <th>Day Redeemed</th>--}}
                                    <th>Lunch</th>
                                    <th>Supper</th>
                                    <th>Total Redeemed</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $dailyRedeemed = [];
                                $totalRedeemed = 0;
                                ?>
                                @foreach($mealScans as $mealScan)
                                    <?php
                                    // Check if the current day and hotel combination has already been processed
                                    $key = $mealScan->date . '_' . $mealScan->hotel_name;
                                    if (isset($dailyRedeemed[$key])) {
                                        // If it has, increment the counts for lunch, supper, and total redeemed
                                        if ($mealScan->time < '16:00:00') {
                                            // Increment lunch if the meal coupon was redeemed in the morning and before 4pm
                                            $dailyRedeemed[$key]['lunch']++;
                                        } else {
                                            // Increment supper if the meal coupon was redeemed after 4pm
                                            $dailyRedeemed[$key]['supper']++;
                                        }
                                        $dailyRedeemed[$key]['redeemed']++;
                                    } else {
                                        // If it hasn't, add a new entry for the day and hotel and initialize the counts
                                        $dailyRedeemed[$key] = [
                                            'date' => $mealScan->date,
                                            'hotel_name' => $mealScan->hotel_name,
//                    'day' => $mealScan->day,
                                            'lunch' => ($mealScan->time < '16:00:00') ? 1 : 0,
                                            'supper' => ($mealScan->time >= '16:00:00') ? 1 : 0,
                                            'redeemed' => 1
                                        ];
                                    }
                                    // Increment the total redeemed count for all entries
                                    $totalRedeemed++;
                                    ?>
                                @endforeach

                                @foreach($dailyRedeemed as $data)
                                    <tr>
                                        <td>{{ $data['date'] }}</td>
                                        <td>{{ $data['hotel_name'] }}</td>
                                        {{--                <td>{{ $data['day'] }}</td>--}}
                                        <td>{{ $data['lunch'] }}</td>
                                        <td>{{ $data['supper'] }}</td>
                                        <td>{{ $data['redeemed'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4" align="right">Total</td>
                                    <td>{{ $totalRedeemed }}</td>
                                </tr>
                                </tfoot>
                            </table>
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
