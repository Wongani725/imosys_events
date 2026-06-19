<!DOCTYPE html>
<html>
<head>
    <title>ICAM participant</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: red;
        }

        .content {
            display: grid;
            background-color: bisque;
            height: 100vh;
            place-items: center;
        }

        form {
            padding: 80px;
            width: 500px;
            margin-left: 30%;
        }

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

        .box {
            width: 200px;
            height: 20px;
            background-color: darkorange;
            border: 1px solid gray;
            margin-bottom: -80px;
            margin-left: -50px;
        }

        .box2 {
            width: 200px;
            height: 200px;
            background-color: white;
            border: 1px solid gray;
            margin-left: -50px;
        }

        .box3 {
            width: 200px;
            height: 200px;
            background-color: white;
            border: 1px solid gray;
            margin-right: -100px;
        }

        .boxform {
        }

        .inline-elements {
            display: inline;
        }

        .myForm {
            background-size: cover;
            background-repeat: no-repeat;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
        }

        .pdf-page {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .registration-form {
            padding: 80px;
            width: 500px;
            margin-left: 30%;
            background-color: bisque;
        }

    </style>
</head>
<body>
@extends('layouts.app')
@section('content')
    <div id="pdfContent" style="display: none;"></div>
    @foreach($participants as $index => $participant)
        @if($index % 4 === 0)
            @if($index !== 0)
                {{--    </div>--}}
            @endif

            <div class="pdf-page">
                @endif
                <form class="participant-form" style="background-image: url('{{ asset('background_images/' . $participant->event_name . '.png')}}'); background-size: cover; margin-left: -20px;">
                    <div class="boxform">
                        <br><br>
                        <h3 style="color: white; margin-left: 10px"><b>{{ $participant->event_name }}</b></h3>
                        <p style="color: white; margin-left: 10px;"><span style="color: #eaa015;"><b>THEME:</b></span><i>{{ $participant->event_name }}</i></p>
                        <br>
                        <div class="container">
                            <div class="element" style="margin-left: 10px;margin-right: 20px;">
                                <p><i class="fa-regular fa-calendar-days" style="color: #eaa015;">&nbsp;&nbsp;&nbsp;&nbsp;</i><span
                                        style="font-size: 12px;">{{ $participant->start_date }} - {{ $participant->end_date }}</span>
                                </p>
                            </div>

                            <div class="element">
                                <i class="fa-sharp fa-solid fa-location-dot" style="color: #eaa015;">&nbsp;&nbsp;&nbsp;</i><span
                                    style="font-size: 12px;">{{ $participant->event_venue }}</span>
                            </div>
                        </div>
                        <span>
                    @if (file_exists(public_path('avatars/' . $participant->reference_code . '.jpg')))
                                <img src="{{ asset('avatars/' . $participant->reference_code . '.jpg') }}?{{ time() }}" class="box2" style="margin-top: 20px;">
                            @else
                                <span><img src="{{url('/images/qRcode_avatar_image.png')}}" class="box2" style="margin-top: 20px;"></span>
                            @endif
                </span>
                        <span><img src="{{ route('qrcode', $participant->reference_code) }}" class="box3"
                                   style="margin-right:-500px; margin-top: 20px;"></span>

                        <b>
                            <p><span style="font-size: 15px;text-transform: uppercase; margin-left: -50px">{{ $participant->participant }}</span>
                            </p>
                        </b>
                        <b>
                            <p style="color: #eaa015; margin-left: -50px; font-size: 13px; margin-top: -20px">{{ $participant->company_name }}</p>
                        </b>
                        <b>
                            <p style="margin-left: -50px; margin-bottom: -0.05px"><span
                                    style="color: #eaa015; font-size: 10.3px;">Status: </span><span
                                    style="color: white; font-size: 10.3px">{{ $participant->status }}</span></p>
                        </b>
                        <b>
                            <p style="margin-left: -50px; margin-bottom: -0.05px"><span
                                    style="color: #eaa015; font-size: 10.3px;">Reference code: </span><span
                                    style="color: white; font-size: 10.3px">{{ $participant->reference_code }}</span></p>
                        </b>

                        <div class="box"></div>
                    </div>
                </form>

                {{--        <div style="height: 500px; width: 400px; background-color: red"></div>--}}

                @if($index % 4 === 3)
            </div>
            <div class="pdf-page">
                @for ($i = 0; $i < 4; $i++)
                    <form class="registration-form">
                        <h1>Event Registration</h1>
                        <label for="name">Name:</label>
                        <input type="text" id="name" required><br><br>

                        <label for="email">Email:</label>
                        <input type="email" id="email" required><br><br>

                        <label for="phone">Phone:</label>
                        <input type="tel" id="phone" required><br><br>

                        <label for="attendees">Number of Attendees:</label>
                        <input type="number" id="attendees" required><br><br>

                        <input type="submit" value="Register">
                    </form>
                @endfor
            </div>
        @endif

    @endforeach
    {{--    </div>--}}

    <center>
        <button type="button" onclick="downloadFormPDF()">Download All as PDF</button>
    </center>

    <script>
        function downloadFormPDF() {
            var pages = document.querySelectorAll(".pdf-page");
            var pdfContent = [];

            pages.forEach(function (page) {
                console.log("NEW PAGE")
                console.log(page)
                html2canvas(page, {
                    useCORS: true,
                    allowTaint: true,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: page.offsetWidth,
                    windowHeight: page.offsetHeight,
                }).then(function (canvas) {
                    var imgData = canvas.toDataURL("image/png");
                    var pdfPage = {
                        image: imgData,
                        width: 595,
                    };
                    pdfContent.push(pdfPage);
                    if (pdfContent.length === pages.length) {
                        generatePDF();
                    }
                });
            });

            function generatePDF() {
                var docDefinition = {
                    pageSize: 'A4',
                    content: pdfContent,
                };

                pdfMake.createPdf(docDefinition).download("name_tags.pdf");
            }
        }
    </script>
@endsection
</body>
</html>
