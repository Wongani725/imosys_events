
<!DOCTYPE html>
<html>
<head>
    <title>ICAM participant</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: red;
        }
        #myForm {
            {{--background-image: url('{{ asset('images/ICAM Card-02 (2)' . '.png')}}');--}}
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            /* padding: 20px; */
            width: 400px; /* Adjust the width as needed */
            height: 600px; /* Adjust the height as needed */
            margin: 0 auto;
            display: flex;
            flex-direction: column; /* Ensure elements stack vertically */
            justify-content: center;
            align-items: center;
        }
        @media (max-width: 400px) {
            #myForm {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            #myForm div {
                margin-top: 10px;
            }

            #myForm h3 {
                margin-top: 0;
            }

            #myForm p.text {
                margin: 0;
                text-align: center;
            }

            #myForm span {
                display: flex;
                justify-content: center;
                margin-top: 10px;
            }

            #myForm span img {
                margin: 10px 0;
                width: 100px;
                height: 100px;
            }

            #myForm div:nth-child(3) {
                width: auto;
            }
        }


        @media (max-width: 768px) {
            #image {
                width: 10px;
                height: 10px;
                display: flex;
                flex-wrap: wrap;
            }
            .image-container {
                flex-basis: 45%; /* Adjust the percentage value as needed */
            }

        }
        /*form {*/
        /*    padding: 80px;*/
        /*    width: 400px;*/
        /*    margin-left: 30%;*/
        /*    height:600px;*/
        /*}*/

        h3 {
            color: white;
            font-size: 200px;
        }

        i {
            color: white;
        }

        span {
            color: white;
        }

        table {
            color: black; /* Set the font color to black */
            font-size: 12px; /* Adjust the font size as needed */
        }

        th {
            background-color: lightgray; /* Set a light gray background color for table headers */
            font-weight: bold; /* Make the table headers bold */
        }

        td {
            background-color: white; /* Set a white background color for table cells */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Set an alternate background color for even rows */
        }

        .container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
        }
        .pdf-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10px;
            /*width: 595px; !* A4 width *!*/
            /*height: 842px; !* A4 height *!*/
            margin-bottom: 34px; /* Equal spacing for left, right, top, and bottom */
            padding-left: -20px; /* Add padding as needed */
        }

        @media print {
            .hide-on-print {
                display: none !important;
            }
        }

        table {
            border: 3px solid #c1bfbf;
            /*width: 400px;*/
            margin-left:2%;
            height:100px;
        }

        table td p{
            padding: 0px;
            font-size: 10px;

        }

    </style>
</head>
<body>
@extends('layouts.app')
@section('content')

    <!-- Add this link to include Font Awesome CSS in your HTML file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-o8ze7KvO+g6qZUbikx0tj01fjl6TCvGZSfH8v+/fCrjDkWZ88t6cd+OGz8lnzFbF" crossorigin="anonymous">

    <style>
        /* Custom CSS for the red download button */
        #downloadButton {
            background-color: red;
            border: none;
            padding: 2px;
            border-radius: 10px;
            cursor: pointer;
            float: right;
            margin-right: 30px;
            width: 70px;
        }

        /* Icon styles */
        #downloadButton .fas {
            font-size: 40px;
            color: white;
        }
    </style>
    <style>
        /* Default image size */
        .avatar-image,
        .qr-code-image {
            width: 140px;
            height: 140px;
        }

        /* Adjust image size on smaller screens */
        @media (max-width: 608px) {
            .avatar-image,
            .qr-code-image {
                width: 150px;
                height: 150px;
                display: flex;
            }
            .row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-gap: 10px;
                justify-content: center;
            }
            .participant
            {
                justify-content: center;
                font-size: 24px;
                text-transform: uppercase;
                color: white;
                text-align: center;
                margin-left: 140px;
                white-space: nowrap;
            }
            .reference
            {
                justify-content: center;
                font-size: 24px;
                text-transform: uppercase;
                color: white;
                margin-left: 100px;
                text-align: center;
            }
            .position
            {
                white-space: nowrap;
                font-size: 30px;
                margin-left: 200px;
                text-align: center;
            }
        }
    </style>


    <div style="margin-top: 2%; padding: 20px"  class="card">
        <h1 style="color: black; font-size: 20px;">{{ $participants->first()->event_name }} Nametags</h1>

        <button id="downloadButton" type="button" onclick="downloadFormPDF()">
            <i class="fas fa-download"></i>
        </button>
    </div>

    {{--    <form id="eventLocationForm" style="float: right; margin-top: -3%" enctype="multipart/form-data" action="{{ route('upload-event-location-image') }}" method="POST"style="margin-left: 100px;">--}}
    {{--        @csrf--}}
    {{--        <input type="hidden" name="event_id" value="{{ $event_id }}">--}}
    {{--        <div class="input-group-append" style="float: right">--}}
    {{--            <input type="file" class="custom-file-input" id="avatarInput" name="image" accept="image/*" required>--}}
    {{--            <button class="btn btn-primary" type="submit">Change Event Location Image</button>--}}
    {{--        </div>--}}
    {{--    </form>--}}

    @foreach ($participants as $participant)
        <div>
            <!-- Display participant details -->
        </div>
    @endforeach


    <br><br><br><br>
    <div id="pdfContent" style=""></div>
    @foreach($participants as $index => $participant)
        @if($index % 4 === 0)
            @if($index !== 0)
                {{--    </div>--}}
            @endif

            <div class="pdf-page">
                @endif


                <form id="myForm">
                    <div class="container" style="margin-top: 20px; display: flex; justify-content: center;">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 d-flex justify-content-center">
                                @if (file_exists(public_path('avatars/' . $participant->reference_code . '.jpg')))
                                    <img class="avatar-image img-fluid" src="{{ asset('avatars/' . $participant->reference_code . '.jpg') }}?{{ time() }}" alt="Avatar Image">
                                @else
                                    <img class="avatar-image img-fluid rounded border border-dark" src="{{ url('/avatars/avatar5.png') }}" alt="Avatar Image Placeholder">
                                @endif
                            </div>

                            <div class="col-md-6 col-sm-6 d-flex justify-content-center">
                                <img class="qr-code-image img-fluid" src="{{ route('qrcode', $participant->reference_code) }}" alt="QR Code Image">
                            </div>
                        </div>
                    </div>


                    <div class="container "style="margin-top: 20px; justify-content: center">
                        <div class="row justify-content-center">
                            <div class="col text-center participant">
                                <span style="font-size: 24px; text-transform: uppercase; color: white;"><b>{{ $participant->participant }}</b></span> <br>
                                {{--                                <div class="reference" style="font-size: 10px; margin-top: -5px; color: white"><b>{{ $participant->reference_code }}</b></div>--}}
                                <div class="row justify-content-center">
                                    <div class="col-6 text-center">
                                        @if ($participant->position)
                                            <div style="margin-top: -20px; margin-left: 60px">
                                                <p class="position" style="color: white; font-size: 25px; margin-top: 10px;white-space: nowrap;">{{ $participant->position }}</p>
                                            </div>
                                        @else
                                            <div style="margin-top: 30px; margin-left: 60px">
                                                <p class="position" style="color: white; font-size: 25px; margin-top: -40px; width: 100%; white-space: nowrap;">{{ $participant->company_name }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container mt-4" style="justify-content: center; margin-top: 220px;">
                        <div class="reference" style="font-size: 15px; margin-top: -30px; margin-left:300px;color: red"><b>{{ $participant->reference_code }}</b></div>

                        {{--                        <div class="row justify-content-center">--}}
                        {{--                            <div class="col-6 text-center">--}}
                        {{--                                @if ($participant->position)--}}
                        {{--                                    <div style="margin-top: 25px;">--}}
                        {{--                                        <p class="position" style="color: rgb(237, 28, 36); font-size: 25px; margin-top: -30px;white-space: nowrap;">{{ $participant->position }}</p>--}}
                        {{--                                    </div>--}}
                        {{--                                @else--}}
                        {{--                                    <div style="margin-top: 25px;">--}}
                        {{--                                        <p class="position" style="color: rgb(237, 28, 36); font-size: 25px; margin-top: -30px; width: 100%; white-space: nowrap;">{{ $participant->company_name }}</p>--}}
                        {{--                                    </div>--}}
                        {{--                                @endif--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>

                </form>

                @if($index % 4 === 3)
            </div>
            <div class="pdf-page "  >
                @for ($i = 0; $i < 4; $i++)
                    <img src="{{ asset('background_images/ICAM Programme-01.png') }}"  alt="Centered Image"  style="display: block; width: 100%; height: auto; border-radius: 5px;">
                @endfor
            </div>
        @endif

    @endforeach
    <div class="container">
        @foreach ($participants as $index => $participant)
            @if ($index % 4 === 0)
                <div class="row">
                    @endif
                    <div class="col-md-3">
                        <img src="{{ asset('background_images/ICAM Programme-01.png') }}" alt="Centered Image" style="display: block; width: 100%; height: auto; border-radius: 5px;">
                    </div>
                    @if ($index % 4 === 3 || $loop->last)
                </div>
            @endif
        @endforeach
    </div>

    <script>
        function downloadFormPDF() {
            //  downloadButton.classList.remove('hide-on-print');
            //document.getElementById("downloadButton").style.display = "none";
            var pages = document.querySelectorAll(".pdf-page");
            var pdfContent = [];

            pages.forEach(function (page, index) {
                console.log("NEW PAGE")
                console.log(page)
                setTimeout(function () {
                    // Wait until the previous page is processed before capturing the next one
                    html2canvas(page, {
                        useCORS: true,
                        allowTaint: true,
                        scrollX: 0,
                        scale: 2,
                        scrollY: 0,
                        windowWidth: page.offsetWidth,
                        windowHeight: page.offsetHeight,
                    }).then(function (canvas) {
                        var imgData = canvas.toDataURL("image/png");
                        var pdfPage = {
                            image: imgData,
                            width: 540,
                            height: 742, // Adjust the height to match the A4 page dimensions
                            marginBottom:50,
                            marginLeft:-10
                        };
                        pdfContent.push(pdfPage);
                        if (pdfContent.length === pages.length) {
                            generatePDF();
                        }
                    });
                }, index * 2000);

            });

            function generatePDF() {
                // Hide the button before generating the PDF
                var downloadButton = document.getElementById("downloadButton");
                downloadButton.style.display = "none";

                var docDefinition = {
                    pageSize: { width: 595, height: 842 },
                    content: pdfContent,
                };
                pdfMake.createPdf(docDefinition).download("name_tags.pdf");

                // Restore the button's visibility after the PDF generation is complete (optional)
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
