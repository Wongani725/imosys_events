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
        .hotel-name {
            margin-bottom: 300px;
        }
        .event-container{
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top:25px;
            padding: 5px;
        }


             /* Table Styling */
         #myTable {
             border-collapse: collapse;
             width: 100%;
         }
        #myTable th
        {
            background-color: #37a739;
        }
        #myTable th, #myTable td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;

        }

        /* Header Row Styling */
        #myTable thead tr {
            background-color: red;
            color: white;
        }

        /* Total Row Styling */
        /*#myTable tbody tr:last-child {*/
        /*    background-color: red;*/
        /*    color: white;*/
        /*}*/


    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <div class="card">
        <div class="card-body">
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
                        <b> <h1 style="font-size: 24px; margin-top: -3%">{{ $event->event_name }} : Participant meal coupons report</h1> </b>
                        <h1 style="margin-top: -2%; font-size: 20px"></h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">

                        <div>
                            <label for="selectDay">Select Day:</label>
                            <select id="selectDay" onchange="filterTableByDay(this.value)">
                                <option value="0">All</option>
                                <option value="1">Day 1</option>
                                <option value="2">Day 2</option>
                                <option value="3">Day 3</option>
                            </select>
                            <table class="" id="myTable">
                                <thead>
                                <tr>
                                    <th>Participant Unique Code</th>
                                    <th>Participant</th>
                                    <th>Scanned Time</th>
                                    <th class="filterable-day">Day</th>
                                    <th>Total Meals</th>
                                    <th>Total Meals Scanned</th>
                                    <th>Meals Remaining</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($report as $participant)
                                        <tr>
                                            <td>{{ $participant->unique_code }}</td>
{{--                                            <td>{{ $participant->participant }}</td>--}}
                                            <td>{{ $participant->name }}</td>
                                            <td>{{ $participant->time }}</td>
                                            <td class="filterable-day">{{ $participant->day}}</td>
                                            <td>{{ $participant->totalMeals }}</td>
                                            <td>{{ $participant->totalMealsScanned }}</td>
                                            <td>{{ $participant->totalMealsRemaining }}</td>

                                        </tr>
                                    @endforeach

                                </tbody>
{{--                                <tfoot>--}}
{{--                                <!-- Add the total row here -->--}}
{{--                                <tr id="totalRow">--}}
{{--                                    <td colspan="4"><strong>Total</strong></td>--}}

{{--                                    <td><strong id="totalMeals">0</strong></td>--}}
{{--                                    <td><strong id="totalMealsScanned">0</strong></td>--}}
{{--                                    <td><strong id="mealsRemaining">0</strong></td>--}}
{{--                                </tr>--}}
{{--                                </tfoot>--}}

                            </table>
{{--                            <table>--}}
{{--                                <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>Reference Code</th>--}}
{{--                                    <th>Participant</th>--}}
{{--                                    <th class="filterable-day">Day</th>--}}
{{--                                    <th>Total Meals</th>--}}
{{--                                    <th>Total Meals Scanned</th>--}}
{{--                                    <th>Total Meals Remaining</th>--}}
{{--                                </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody>--}}
{{--                                @foreach ($report as $participant)--}}
{{--                                    <tr>--}}
{{--                                        <td>{{ $participant->reference_code }}</td>--}}
{{--                                        <td>{{ $participant->participant }}</td>--}}
{{--                                        <td class="filterable-day">{{ $participant->day }}</td>--}}
{{--                                        <td>{{ $participant->totalMeals }}</td>--}}
{{--                                        <td>{{ $participant->totalMealsScanned }}</td>--}}
{{--                                        <td>{{ $participant->totalMealsRemaining }}</td>--}}
{{--                                    </tr>--}}
{{--                                @endforeach--}}
{{--                                </tbody>--}}
{{--                                <tfoot>--}}
{{--                                <tr>--}}
{{--                                    <td colspan="3"></td> <!-- Empty cells for Reference Code, Participant, and Day -->--}}
{{--                                    <td><strong>Total Meals:</strong></td>--}}
{{--                                    <td>{{ $totalMealsSum }}</td> <!-- Display the calculated sum of "Total Meals" -->--}}
{{--                                    <td></td> <!-- Empty cell for Total Meals Remaining -->--}}
{{--                                </tr>--}}
{{--                                </tfoot>--}}
{{--                            </table>--}}


                        </div>

                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                        <script>
                            function filterTableByDay(selectedDay) {
                                if (selectedDay === '0') {
                                    // Show all rows when 'All' is selected
                                    $("#myTable tbody tr").show();
                                } else {
                                    // Show rows with matching "Day" value and hide others
                                    $("#myTable tbody tr").hide().filter(function() {
                                        return $(this).find(".filterable-day").text().trim() === selectedDay;
                                    }).show();
                                }
                            }

                            function calculateMealsRemaining() {
                                // Loop through each row in the table (except the last row for the Total)
                                $("#myTable tbody tr:not(:last-child)").each(function() {
                                    // Get the "Total Meals" and "Total Meals Scanned" values for each row
                                    const totalMeals = parseInt($(this).find("td:eq(6)").text());
                                    const totalMealsScanned = parseInt($(this).find("td:eq(7)").text());

                                    // Calculate "Meals Remaining" for this row and update the value
                                    const mealsRemaining = totalMeals - totalMealsScanned;
                                    $(this).find("td:eq(8)").text(mealsRemaining > 0 ? mealsRemaining : 0);
                                });

                                // Calculate and update the "Meals Remaining" for the Total Row
                                const totalMeals = parseInt($("#totalMeals").text());
                                const totalMealsScannedAll = countRecordsExcludingNA();
                                const mealsRemaining = totalMeals - totalMealsScannedAll;
                                $("#totalMealsScanned").text(totalMealsScannedAll);
                                $("#mealsRemaining").text(mealsRemaining > 0 ? mealsRemaining : 0);
                            }

                            function countRecordsExcludingNA() {
                                let totalMealsScanned = 0;
                                // Loop through each visible row in the table (except the last row for the Total)
                                $("#myTable tbody tr:visible:not(:last-child)").each(function() {
                                    const hotelName = $(this).find("td:eq(2)").text().trim(); // Get the value of the "Hotel Name" column
                                    if (hotelName !== 'N/A') {
                                        totalMealsScanned++;
                                    }
                                });
                                return totalMealsScanned;
                            }

                            function updateTotalRow() {
                                const uniqueReferenceCodes = [];
                                let totalMeals = 0;

                                // Loop through each visible row in the table (except the last row for the Total)
                                $("#myTable tbody tr:visible:not(:last-child)").each(function () {
                                    const referenceCode = $(this).find("td:eq(0)").text().trim(); // Get the participant reference code

                                    // Check if the reference code has already been processed
                                    if (!uniqueReferenceCodes.includes(referenceCode)) {
                                        const totalMealsForRow = parseInt($(this).find("td:eq(6)").text());
                                        totalMeals += totalMealsForRow;

                                        uniqueReferenceCodes.push(referenceCode);
                                    }
                                });

                                // Update the "Total Row" with the calculated values
                                $("#totalMeals").text(totalMeals);

                                // Calculate and update the "Meals Remaining" for the Total Row
                                const totalMealsScannedAll = countRecordsExcludingNA();
                                const mealsRemaining = totalMeals - totalMealsScannedAll;
                                $("#totalMealsScanned").text(totalMealsScannedAll);
                                $("#mealsRemaining").text(mealsRemaining > 0 ? mealsRemaining : 0);
                            }

                            // Call the function when the page loads and whenever the table is filtered
                            $(document).ready(function () {
                                filterTableByDay('0');
                                calculateMealsRemaining();
                                updateTotalRow();

                                $("#selectDay").on("change", function () {
                                    filterTableByDay(this.value);
                                    calculateMealsRemaining();
                                    updateTotalRow();
                                });
                            });
                        </script>
                        <br>
                        <button onclick="downloadTable('myTable')" style="background-color: #696cff; color: white;">Download as Excel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div>

        <script>
            function downloadTable(myTable) {
                var table = document.getElementById(myTable);
                var html = table.outerHTML;
                var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
                var link = document.createElement('a');
                link.download = myTable + '.xls';
                link.href = url;
                link.click();
            }
        </script>

        <?php
        $index = 1; // Declare and initialize the variable
        $index++; // Increment the value of $index by 1
        ?>
        @if($index)


        </tbody>

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
{{--<table class="" id="myTable">--}}
{{--    <thead>--}}
{{--    <tr>--}}
{{--        <th>Participant Unique Code</th>--}}
{{--        <th>Participant</th>--}}
{{--                                            <th>Hotel Name</th>--}}
{{--        <th class="filterable-day">Day</th>--}}
{{--                                            <th>Time</th>--}}
{{--        <th>Date</th>--}}
{{--        <th>Total Meals</th>--}}
{{--        <th>Total Meals Scanned</th>--}}
{{--        <th>Meals Remaining</th>--}}
{{--    </tr>--}}
{{--    </thead>--}}
{{--    <tbody>--}}
{{--    @foreach ($participantData as $participant)--}}
{{--        @php--}}
{{--            $seenTimes = [];--}}
{{--        @endphp--}}

{{--        @foreach ($participant['appearances'] as $appearance)--}}
{{--            @php--}}
{{--                // Check if the time has been seen before for this participant--}}
{{--                if (in_array($appearance['time'], $seenTimes)) {--}}
{{--                    continue; // Skip this row if duplicate time--}}
{{--                }--}}
{{--                $seenTimes[] = $appearance['time']; // Add time to the seenTimes array--}}
{{--            @endphp--}}

{{--            <tr>--}}
{{--                <td>{{ $participant['participant_reference_code'] }}</td>--}}
{{--                <td>{{ $participant['participant'] }}</td>--}}
{{--                                                            <td>{{ $appearance['hotel_name'] }}</td>--}}
{{--                <td class="filterable-day">{{ $appearance['day'] }}</td>--}}
{{--                <td>{{ $appearance['time'] }}</td>--}}
{{--                                                            <td>{{ $appearance['date'] }}</td>--}}
{{--                <td>{{ $participant['total_meals'] }}</td>--}}
{{--                @if ($appearance['hotel_name'] === 'N/A' || $appearance['day'] === 'N/A' || $appearance['time'] === 'N/A' || $appearance['date'] === 'N/A')--}}
{{--                    <td>0</td> <!-- Total Meals Scanned for N/A -->--}}
{{--                @else--}}
{{--                    @if (count($seenTimes) > 0)--}}
{{--                        <td>{{ count($seenTimes) }}</td> <!-- Display the incremental value -->--}}
{{--                    @else--}}
{{--                        <td>0</td> <!-- Total Meals Scanned when no appearances -->--}}
{{--                    @endif--}}
{{--                @endif--}}
{{--                <td>--}}
{{--                    @php--}}
{{--                        // Calculate "Meals Remaining" for this participant and this specific appearance--}}
{{--                        $mealsRemaining = $participant['total_meals'] - (count($seenTimes) > 0 ? count($seenTimes) : 0);--}}
{{--                    @endphp--}}
{{--                    {{ $mealsRemaining > 0 ? $mealsRemaining : 0 }}--}}
{{--                </td>--}}

{{--            </tr>--}}
{{--        @endforeach--}}
{{--    @endforeach--}}

{{--    </tbody>--}}
{{--    <tfoot>--}}
{{--    <!-- Add the total row here -->--}}
{{--    <tr id="totalRow">--}}
{{--        <td colspan="4"><strong>Total</strong></td>--}}

{{--        <td><strong id="totalMeals">0</strong></td>--}}
{{--        <td><strong id="totalMealsScanned">0</strong></td>--}}
{{--        <td><strong id="mealsRemaining">0</strong></td>--}}
{{--    </tr>--}}
{{--    </tfoot>--}}

{{--</table>--}}
