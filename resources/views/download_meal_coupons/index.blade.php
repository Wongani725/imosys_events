<!DOCTYPE html>
<html>
<head>
    <title>ICAM participant</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-o8ze7KvO+g6qZUbikx0tj01fjl6TCvGZSfH8v+/fCrjDkWZ88t6cd+OGz8lnzFbF" crossorigin="anonymous">

    <style>
        /* Your custom CSS styles here */
        /* For example: */
        body {
            background-color: #f2f2f2;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* ... Add your custom styles ... */
        .pdf-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10px;
            /*width: 595px; !* A4 width *!*/
            /*height: 842px; !* A4 height *!*/
            /*margin-bottom: 34px; !* Equal spacing for left, right, top, and bottom *!*/
            padding-left: -20px; /* Add padding as needed */
        }
        #myForm {
        {{--background-image: url('{{ asset('images/ICAM Card-02 (2)' . '.png')}}');--}}
/*background-repeat: no-repeat;*/
            /*background-size: cover;*/
            /*background-position: center;*/
            /* padding: 20px; */
            /*width: 400px; !* Adjust the width as needed *!*/
            /*height: 600px; !* Adjust the height as needed *!*/
            margin: 0 auto;
            display: flex;
            flex-direction: column; /* Ensure elements stack vertically */
            justify-content: center;
            align-items: center;
        }

    </style>
</head>
<body>
@extends('layouts.app2')
@section('content')
    <div style="margin-top: 2%; padding: 20px" class="card">

        <center>
            <h1 style="color: black; font-size: 20px;">{{ $participants->first()->event_name }} Extra Meal Coupons</h1>
            <button  type="button" onclick="downloadFormPDF()" class="btn btn-danger text-white btn-block">
                Download as PDF
            </button>
        </center>
    </div>
    <!-- Your existing content and HTML structure goes here -->
    <!-- For example: -->


    <!-- Add the red download button -->


    <!-- The PDF content and QR code display sections -->
    <div id="pdfContent" style=""></div>

    <div class="container">
        @php
            $processedReferenceCodes = [];
        @endphp

        @foreach ($mealCoupons as $coupon)
            @php
                $participantRefCode = $coupon->participant_reference_code;
                $participant = $participants->where('reference_code', $participantRefCode)->first();
                $countQRCodesForRefCode = $mealCoupons->where('participant_reference_code', $participantRefCode)->count();
            @endphp

            @if ($countQRCodesForRefCode === 1)
                @continue
            @endif

            @if ($countQRCodesForRefCode > 1)
                @if (!isset($processedReferenceCodes[$participantRefCode]))
                    @php
                        $processedReferenceCodes[$participantRefCode] = true;
                    @endphp
                    @continue
                @endif
            @endif

            <div >
                <form id="myForm" class="pdf-page">
                    @csrf
                    <div class="col-md-6 mt-3">
                        <img src="{{ route('qrcode', $coupon->unique_code) }}" alt="Meal Coupon QR Code" class="qrcode-image" ><br>
                        @if ($participant)
                            <b style="color:red">{{ $participant->participant }}</b><br>
                        @else
                            <b style="color:red">Participant data not found</b><br>
                        @endif
                        <br>
                    </div>
                </form>
            </div>
        @endforeach
        {{--   <center></center> <button onclick="downloadFormPDF()" class="btn btn-danger text-white btn-block pdf-page"> download as PDF </button>--}}

    </div>
    {{--<div style="margin-top: 2%; padding: 20px" class="card">--}}
    <center>

        <button id="downloadButton" type="button" onclick="downloadFormPDF()" class="btn btn-danger text-white btn-block">
            Download as PDF
        </button>
    </center>
    {{--</div>--}}



    <!-- Your existing scripts and JavaScript functions -->
    <script>
        // Your existing JavaScript code...
        // For example:
        function toggleMenu() {
            // Code to toggle the menu...
        }

    </script>
    <script>
        function downloadFormPDF() {
            var pages = document.querySelectorAll(".pdf-page");
            var pdfContent = [];

            pages.forEach(function (page, index) {
                setTimeout(function () {
                    // Wait until the previous page is processed before capturing the next one
                    html2canvas(page, {
                        useCORS: true,
                        allowTaint: true,
                        scrollX: 0,
                        scale: 2,
                        scrollY: 0,
                        // Remove windowWidth and windowHeight options
                    }).then(function (canvas) {
                        var imgData = canvas.toDataURL("image/png");
                        var pdfPage = {
                            image: imgData,
                            width: 940, // Adjust the width to match the A4 page dimensions
                            height: 95, // Adjust the height to match the A4 page dimensions
                            marginBottom: 50,

                        };
                        pdfContent.push(pdfPage);
                        if (pdfContent.length === pages.length) {
                            generatePDF();
                        }
                    });
                }, index * 2000);
            });

            function generatePDF() {
                var downloadButton = document.getElementById("downloadButton");
                downloadButton.style.display = "none";

                var docDefinition = {
                    pageSize: { width: 595, height: 842 },
                    content: pdfContent,
                };
                pdfMake.createPdf(docDefinition).download("meal_coupons.pdf");

                downloadButton.style.display = "block";
            }
        }
    </script>





    <script>
        var monthNumberElements = document.getElementsByClassName('monthNumber');
        var monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        for (var i = 0; i < monthNumberElements.length; i++) {
            var monthNumberElement = monthNumberElements[i];
            var monthNumber = parseInt(monthNumberElement.textContent);
            var monthName = monthNames[monthNumber - 1];
            monthNumberElement.textContent = monthName;
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#changeLocationForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                // Make an AJAX request to update the event location image
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Update the event location image for all participants in the UI
                        $('.event-location-image').attr('src', response.newImageURL);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
    </script>

    <script>

        var style = document.createElement("style");
        style.innerHTML = "@media print { #downloadButton { display: none; } }";
        document.head.appendChild(style);
    </script>


@endsection
</body>
</html>
