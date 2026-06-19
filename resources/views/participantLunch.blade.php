@extends('layouts.app')

@section('content')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>

    <style>
        .pdf-page {
            /*display: grid;*/
            /*grid-template-columns: repeat(2, 1fr);*/
            gap: 20px;
            /*margin-bottom: 40px;*/
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
            background-color: red;
            font-weight: bold;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .table {
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody + tbody {
            border-top: 2px solid #dee2e6;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .table-bordered thead th,
        .table-bordered thead td {
            border-bottom-width: 2px;
        }

        .table-borderless th,
        .table-borderless td,
        .table-borderless thead th,
        .table-borderless tbody + tbody {
            border: 0;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table-hover tbody tr:hover {
            color: #212529;
            background-color: rgba(0, 0, 0, 0.075);
        }
    </style>

    <div class="col-sm-10 pt-1">
        <button class="btn btn-primary float-end ms-2" onclick="downloadFormPDF()"><i class="fa fa-download"></i></button>
    </div>
    <div class="pdf-page">
        <div class="container">
            <h1>Lunch meal report for {{ $hotelName }}</h1>
            <div>
                <label for="selectDay">Select Day:</label>
                <select id="selectDay" onchange="updateTotalRow(this.value)">
                    <option value="0">All</option>
                    <option value="1">Day 1</option>
                    <option value="2">Day 2</option>
                    <option value="3">Day 3</option>
                </select>
            <table id="participants-table" class="table table-striped table-bordered">
                <thead>
                <tr style="background-color: red; color: white;">
                    <th style="color: white">Participant Unique Codes</th>
                    <th style="color: white">Name</th>
                    <th style="color: white" class="filterable-day">Day</th>
                    <th style="color: white">Date</th>
                    <th style="color: white">Time</th>
{{--                    <th style="color: white">Redeemed</th>--}}
                </tr>
                </thead>
                <tbody>
                @php
                    $totalParticipants = 0;
                @endphp

                @foreach ($participants as $participant)
                    @php
                        $totalParticipants++;
                    @endphp
                    <tr>
                        <td>{{ $participant->unique_code }}</td>
                        <td>{{ $participantNames[$participant->participant_reference_code] ?? '' }}</td>
                        <td class="filterable-day">{{ $participant->day }}</td>
                        <td>{{ $participant->date }}</td>
                        <td>{{ $participant->time }}</td>
{{--                        <td>{{ $participant->redeemed }}</td>--}}
                    </tr>
                @endforeach


                </tbody>
                <tfoot>
                <!-- Total Row -->
                <tr style="background-color: #f2f2f2;">
                    <td colspan="1" style="text-align: left;"><strong>Total meals for hotel {{ $hotelName }}: </strong></td>
                    <td><strong id="totalMeals">0</strong></td>
                </tr>
                </tfoot>
            </table>
            </div>
            <button onclick="downloadTable('participants-table')">Download Table as Excel</button>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            function updateTotalRow(selectedDay) {
                console.log("Selected Day:", selectedDay);

                if (selectedDay === '0') {
                    // Show all rows when 'All' is selected
                    $("#participants-table tbody tr").show();
                } else {
                    // Show rows with matching "Day" value and hide others
                    $("#participants-table tbody tr").hide().filter(function () {
                        return $(this).find(".filterable-day").text().trim() === String(selectedDay);
                    }).show();
                }

                // Update the total meals count in the total row
                let totalMeals = $("#participants-table tbody tr:visible").length;
                $("#totalMeals").text(totalMeals);
                console.log("Total Meals:", totalMeals);
            }


        </script>


        <?php
        $index = 1; // Declare and initialize the variable
        $index++; // Increment the value of $index by 1
        ?>
        @if($index)
        </tbody>
        <tfoot>

        </tfoot>
        </table>
    </div>


    </div>
    @endif
    <style>
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
