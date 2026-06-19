<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>

@extends('layouts.app')

@section('content')
    <head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
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
            color: white;
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


        #table1 {
            border-collapse: collapse;
            width: 100%;
        }
        #table1 th
        {
            background-color: red;
        }
        #table1 th, #myTable td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;

        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">

                <div class="row mb-3">
                    <div class="col-sm-2">
                        <select onchange="location = this.value;" class="form-select float-start me-2" style="background-color: #37a739; color: white;">
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


                        <span><img src="{{ url('/MEI_LOGO.png') }}" style="margin-top: 0.1%;width: 100px; height:100px;"></span>
                        <b>  <h3 style="color:black; font-size:16px; " >Malawi Engineering Institution</h3></b>
                        {{--                        <p style="color:black; font-size:12px; margin-top: -1%; padding-top: 1px;">info@iim.mw | P.O. Box 2040, Blantyre | 01 835 169 | iim.org.mw</p>--}}
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
{{--                        <table id="table1">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th style="background-color: red">Date</th>--}}
{{--                                <th style="background-color: red">Hotel</th>--}}
{{--                                --}}{{--            <th>Day Redeemed</th>--}}
{{--                                <th style="background-color: red">Lunch</th>--}}
{{--                                <th style="background-color: red">Supper</th>--}}
{{--                                <th style="background-color: red">Total Redeemed</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <?php--}}
{{--                            $dailyRedeemed = [];--}}
{{--                            $totalRedeemed = 0;--}}
{{--                            $lunchRedeemed = 0;--}}
{{--                            $supperRedeemed = 0;--}}
{{--                            ?>--}}
{{--                            @foreach($mealScans as $mealScan)--}}
{{--                                <?php--}}
{{--                                // Check if the current day and hotel combination has already been processed--}}
{{--                                $key = $mealScan->date . '_' . $mealScan->hotel_name;--}}
{{--                                if (isset($dailyRedeemed[$key])) {--}}
{{--                                    // If it has, increment the counts for lunch, supper, and total redeemed--}}
{{--                                    if ($mealScan->time < '16:00:00') {--}}
{{--                                        // Increment lunch if the meal coupon was redeemed in the morning and before 4pm--}}
{{--                                        $dailyRedeemed[$key]['lunch']++;--}}
{{--                                    } else {--}}
{{--                                        // Increment supper if the meal coupon was redeemed after 4pm--}}
{{--                                        $dailyRedeemed[$key]['supper']++;--}}
{{--                                    }--}}
{{--                                    $dailyRedeemed[$key]['redeemed']++;--}}
{{--                                } else {--}}
{{--                                    // If it hasn't, add a new entry for the day and hotel and initialize the counts--}}
{{--                                    $dailyRedeemed[$key] = [--}}
{{--                                        'date' => $mealScan->date,--}}
{{--                                        'hotel_name' => $mealScan->hotel_name,--}}
{{--//                    'day' => $mealScan->day,--}}
{{--                                        'lunch' => ($mealScan->time < '16:00:00') ? 1 : 0,--}}
{{--                                        'supper' => ($mealScan->time >= '16:00:00') ? 1 : 0,--}}
{{--                                        'redeemed' => 1--}}
{{--                                    ];--}}
{{--                                }--}}
{{--                                // Increment the total redeemed count for all entries--}}
{{--                                $totalRedeemed++;--}}
{{--                                $lunchRedeemed++;--}}
{{--                                $supperRedeemed++;--}}
{{--                                ?>--}}
{{--                            @endforeach--}}

{{--                            @foreach($dailyRedeemed as $data)--}}
{{--                                <tr>--}}
{{--                                    <td>{{ $data['date'] }}</td>--}}
{{--                                    <td>{{ $data['hotel_name'] }}</td>--}}
{{--                                    --}}{{--                <td>{{ $data['day'] }}</td>--}}
{{--                                    <td>{{ $data['lunch'] }}</td>--}}
{{--                                    <td>{{ $data['supper'] }}</td>--}}
{{--                                    <td>{{ $data['redeemed'] }}</td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
{{--                            </tbody>--}}
{{--                            <tfoot>--}}
{{--                            <tr>--}}
{{--                                <td colspan="2" align="right">Total</td>--}}
{{--                                <td>{{ $lunchRedeemed }}</td>--}}
{{--                                <td>{{ $supperRedeemed }}</td>--}}
{{--                                <td>{{ $totalRedeemed }}</td>--}}
{{--                            </tr>--}}
{{--                            </tfoot>--}}
{{--                        </table>--}}

                        <table id="table1">
                            <thead>
                            <tr>
                                <th style="background-color: #37a739">Date</th>
                                <th style="background-color: #37a739">Hotel</th>
                                {{-- <th>Day Redeemed</th> --}}
                                <th style="background-color: #37a739">Lunch</th>
                                <th style="background-color: #37a739">Supper</th>
                                <th style="background-color: #37a739">Total Redeemed</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $dailyRedeemed = [];
                            $totalRedeemed = 0;
                            $lunchRedeemed = 0;
                            $supperRedeemed = 0;
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
                                        // 'day' => $mealScan->day,
                                        'lunch' => ($mealScan->time < '16:00:00') ? 1 : 0,
                                        'supper' => ($mealScan->time >= '16:00:00') ? 1 : 0,
                                        'redeemed' => 1
                                    ];
                                }
                                // Increment the total redeemed count for all entries
                                $totalRedeemed++;
                                $lunchRedeemed++;
                                $supperRedeemed++;
                                ?>
                            @endforeach

                            @foreach($dailyRedeemed as $data)
                                <tr>
                                    <td>{{ $data['date'] }}</td>
                                    <td>
                                        {{ $data['hotel_name'] }}

                                    </td>
                                    <td>
                                        <a href="{{ route('hotel-participants-lunch', ['hotel_name' => $data['hotel_name']]) }}" class="btn btn-success btn-sm">
                                            {{ $data['lunch'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('hotel-participants-supper', ['hotel_name' => $data['hotel_name']]) }}" class="btn btn-success btn-sm">
                                        {{ $data['supper'] }}</td>
                                        </a>

                                    <td>
                                        <a href="{{ route('hotel-participants', ['hotel_name' => $data['hotel_name']]) }}" class="btn btn-success btn-sm">
                                            {{ $data['redeemed'] }}
                                        </a>
                                    </td>

                                </tr>
                            @endforeach

                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2" align="right">Total</td>
                                <td>
                                    <?php
                                    // Calculate the total lunch redeemed count
                                    $totalLunchRedeemed = array_reduce($dailyRedeemed, function ($carry, $item) {
                                        return $carry + $item['lunch'];
                                    }, 0);
                                    echo $totalLunchRedeemed;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Calculate the total supper redeemed count
                                    $totalSupperRedeemed = array_reduce($dailyRedeemed, function ($carry, $item) {
                                        return $carry + $item['supper'];
                                    }, 0);
                                    echo $totalSupperRedeemed;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Calculate the total redeemed count
                                    $totalRedeemed = array_reduce($dailyRedeemed, function ($carry, $item) {
                                        return $carry + $item['redeemed'];
                                    }, 0);
                                    echo $totalRedeemed;
                                    ?>
                                </td>
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
    <button onclick="downloadTable('table1')" style="background-color: #37a739; color: white;">Download Table as Excel</button>

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
