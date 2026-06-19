<!DOCTYPE html>
<html>

<head>
    <title>ICAM participant </title>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>

        body {
            margin: 0;
            padding: 0;
            background-color: red;
        }

        @media (max-width: 600px) {
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



    </style>
</head>
<body>

@extends('layouts.app')
@section('content')
    <div class="card bg-dark text-light border-light mb-4" style="padding: 100px">

        <form id="myForm">
            <div>
                <div style="margin-top: 70px">

                    <h3 style="color: rgb(237, 28, 36); text-align: center; margin-top: 4%">
                        <span style="font-size: 25px;color: rgb(237, 28, 36);"><b>{{ substr($participant->event_name, 0, 4) }}</b> </span><br>
                        <span style="font-size: 15px;color: rgb(237, 28, 36);">
                    <b>{{ substr($participant->event_name, 5) }}
                    </b>
                </span>
                    </h3>
                    <b> <h3 style="color: white;margin-top: -6%;font-size:12px;margin-left: 47px;">Theme</h3></b>





                    <h3 class="text" style="color: black; margin-left: 40px; font-size: 13px; text-transform: uppercase;">
                        <b>{{ $participant->theme }}</b>
                    </h3>

                    <span>
            @if (file_exists(public_path('avatars/' . $participant->reference_code . '.jpg')))
                            <img src="{{ asset('avatars/' . $participant->reference_code . '.jpg') }}?{{ time() }}"  style="margin-top: -10px;width: 130px; height:130px;margin-left: 40px;">
                        @else
                            <span><img src="{{ url('/images/qRcode_avatar_image.png') }}" style="margin-top: -10px;width: 130px; height:130px;margin-left: 40px;"></span>
                        @endif
        </span>
                    <span><img src="{{ asset("{$participant->qrcode_path}") }}" style=" margin-top: -10px;width: 130px; height:130px;margin-left: 11px;"></span>
                    <div style="width: 460px; margin-top: 10px; padding: 1px; display: flex; justify-content: center;">
                        <p style="margin-top: 12px; text-align: center;">
                <span style="font-size: 15px; color: white; margin-top: 10px; padding: 10px;">
                    <b>{{ $participant->participant }}</b><br>
                     <b>{{ $participant->reference_code }}</b>
                </span>
                        </p>
                    </div>
                    <br>
                </div>

                <div style="margin-top: 25px;">
                    <p style="color: rgb(237, 28, 36); display: flex; justify-content: center; font-size: 20px; margin-top: -50px;">ICAM</p>
                </div>
                <div style="  margin-left: 50px; margin-top: -30px;">
                    <p>
                        <b>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: black; font-size: 22px;">{{ substr($participant->start_date, 8, 2) }} - {{ substr($participant->end_date, 8, 2) }}</span></b><br>
                        <span style="color: rgb(237,28,36);">
                    <b><span  style="color: rgb(237,28,36);" class="monthNumber">{{ substr($participant->start_date, 6, 1) }}</span>  {{ substr($participant->start_date, 0, 4) }}</b>
                </span>
                    </p>
                </div>
                {{--                        <div style="margin-top: 25px;">--}}
                {{--                            <p style="color: rgb(237, 28, 36); display: flex; justify-content: center; font-size: 20px; margin-top: -50px;">ICAM</p>--}}
                {{--                        </div>--}}
                {{--                        <div style="  margin-left: 50px; margin-top: -30px;">--}}
                {{--                            <p>--}}
                {{--                                <b>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: black; font-size: 22px;">8 - 10</span></b><br>--}}
                {{--                                <span style="color: rgb(237,28,36);">--}}
                {{--                    <b>June 2023</b>--}}
                {{--                </span>--}}
                {{--                            </p>--}}
                {{--                        </div>--}}

                <div style="margin-left: 280px; margin-top: -100px">
                    @if (file_exists(public_path('avatars/' . $participant->event_id . '.jpg')))
                        <img src="{{ asset('avatars/' . $participant->event_id . '.jpg') }}?{{ time() }}"  alt="Centered Image" width="150px" height="150px">
                    @else
                        <img src="{{ asset('images/image-removebg-preview (1).png') }}" alt="Centered Image" width="150px" height="150px">
                    @endif
                </div>
                <br><br><br><br>


            </div>
            <div style=" margin-top: -100px; ">
                <img src="{{ asset('background_images/advert_image.png') }}"  alt="Centered Image" width="400px" height="30px">
            </div>
        </form>
        {{--    <form id="myForm">--}}
        {{--        <div>--}}

        {{--            <h3 style="color: rgb(237, 28, 36); text-align: center; margin-top: 6%">--}}
        {{--                <span style="font-size: 25px;color: rgb(237, 28, 36);"><b>{{ substr($participant->event_name, 0, 4) }}</b> </span><br>--}}
        {{--                <span style="font-size: 15px;color: rgb(237, 28, 36);">--}}
        {{--                    <b>{{ substr($participant->event_name, 5) }}--}}
        {{--                    </b>--}}
        {{--                </span>--}}
        {{--            </h3>--}}
        {{--            <b> <h3 style="color: white;margin-top: -3%;font-size:12px;margin-left: 45px;">Theme</h3></b>--}}

        {{--            <h3 class="text" style="color: black; margin-left: 30px; font-size: 13px; text-transform: uppercase;">--}}
        {{--                <b>{{ $participant->theme }}</b>--}}
        {{--            </h3>--}}

        {{--            <span>--}}
        {{--            @if (file_exists(public_path('avatars/' . $participant->reference_code . '.jpg')))--}}
        {{--                    <img src="{{ asset('avatars/' . $participant->reference_code . '.jpg') }}?{{ time() }}"  style="margin-top: 10px;margin-left: 45px; width: 130px; height:130px;">--}}
        {{--                @else--}}
        {{--                    <span><img src="{{ url('/images/qRcode_avatar_image.png') }}" style="margin-top: 10px;margin-left: 45px;width: 130px; height:130px;"></span>--}}
        {{--                @endif--}}
        {{--        </span>--}}
        {{--            <span><img src="{{ asset("{$participant->qrcode_path}") }}" style=" margin-top: 10px;width: 130px; height:130px;"></span>--}}
        {{--            <div style="width: 460px; margin-top: 10px; padding: 1px; display: flex; justify-content: center;">--}}
        {{--                <p style="margin-top: 0px; text-align: center;">--}}
        {{--                <span style="font-size: 20px; color: white; margin-top: 10px; padding: 10px;">--}}
        {{--                    <b>{{ $participant->participant }}</b>--}}
        {{--                </span>--}}
        {{--                </p>--}}
        {{--            </div>--}}
        {{--            <br>--}}

        {{--            <div style="margin-top: 25px;">--}}
        {{--                <p style="color: rgb(237, 28, 36); display: flex; justify-content: center; font-size: 20px; margin-top: -50px;">ICAM</p>--}}
        {{--            </div>--}}
        {{--            <div style="  margin-left: 50px; margin-top: -30px;">--}}
        {{--                <p>--}}
        {{--                    <b>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: black; font-size: 22px;">{{ substr($participant->start_date, 8, 2) }} - {{ substr($participant->end_date, 8, 2) }}</span></b><br>--}}
        {{--                    <span style="color: rgb(237,28,36);">--}}
        {{--                    <b><span  style="color: rgb(237,28,36);" class="monthNumber">{{ substr($participant->start_date, 6, 1) }}</span>  {{ substr($participant->start_date, 0, 4) }}</b>--}}
        {{--                </span>--}}
        {{--                </p>--}}
        {{--            </div>--}}
        {{--            --}}{{--                        <div style="margin-top: 25px;">--}}
        {{--            --}}{{--                            <p style="color: rgb(237, 28, 36); display: flex; justify-content: center; font-size: 20px; margin-top: -50px;">ICAM</p>--}}
        {{--            --}}{{--                        </div>--}}
        {{--            --}}{{--                        <div style="  margin-left: 50px; margin-top: -30px;">--}}
        {{--            --}}{{--                            <p>--}}
        {{--            --}}{{--                                <b>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: black; font-size: 22px;">8 - 10</span></b><br>--}}
        {{--            --}}{{--                                <span style="color: rgb(237,28,36);">--}}
        {{--            --}}{{--                    <b>June 2023</b>--}}
        {{--            --}}{{--                </span>--}}
        {{--            --}}{{--                            </p>--}}
        {{--            --}}{{--                        </div>--}}
        {{--            <div style="margin-left: 280px; margin-top: -100px">--}}
        {{--                <img src="{{ asset('images/image-removebg-preview (1).png') }}" alt="Centered Image" width="150px" height="150px">--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--    </form>--}}

        <center><button type="button" onclick="downloadFormImage()">Download as Image</button></center>
        <script>
            function downloadFormImage() {
                html2canvas(document.querySelector("#myForm")).then(function(canvas) {
                    var imageData = canvas.toDataURL("image/png");

                    // Create a temporary link element
                    var link = document.createElement("a");
                    link.href = imageData;
                    link.download = "{{ $participant->participant}}.png";
                    link.click();
                });
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

    </div>
</body>
</html>


</body>
</html>
@endsection
