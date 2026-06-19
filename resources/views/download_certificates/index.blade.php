<!DOCTYPE html>
<html>
<head>
    <title>IIA</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <style>
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #0056b3;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
        }
        .pdf-page {
            display: grid;
            gap: 20px;
            margin-bottom: 40px;
        }
        #ICAMprogramme {
            display: inline-block;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100% 100%;
        }
    </style>
</head>
<body>
@extends('layouts.app')
@section('content')

    <div style="margin-top: 2%">
        <button id="downloadButton" type="button" class="btn btn-success" onclick="downloadFormPDF()">Download Certificates</button>
    </div>

<br>
    <div id="pdfContent" style=""></div>
    @foreach($participants as $participant)
            <div class="pdf-page">
                <div class="card">
                    <div style="display: flex; justify-content: center; margin-top:150px; align-items: center; height: 100vh;">
                        <div class="card" style="width: 95vw; max-width: 800px; height: 1000px; position: relative;">
                            <form id="ICAMprogramme" class="programme-form"
                                  style="width: 100%; height: 100%; position: relative;
                                  background-image: url('{{ $participant->certificate_background ? asset($participant->certificate_background) : '' }}');
                                  background-size: cover; background-position: center;">
                                <div style="position: absolute; bottom: 490px; left: 0; right: 0; text-align: center;">
                                    <label>
                                        <br>
                                        <span style="font-size: 30px; color: red; display: inline-block;"><b>{{ $participant->participant }}</b></span>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                    <br><br><br><br><br><br><br>
                </div>
            </div>
    @endforeach
    <br>

    <script>
        function downloadFormPDF() {
            var pages = document.querySelectorAll(".pdf-page");
            var pdfContent = [];

            pages.forEach(function (page) {
                setTimeout(function () {
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
                            width: 555,
                            height: 642
                        };
                        pdfContent.push(pdfPage);
                        if (pdfContent.length === pages.length) {
                            generatePDF();
                        }
                    });
                });
            });

            function generatePDF() {
                var downloadButton = document.getElementById("downloadButton");
                downloadButton.style.display = "none";

                var docDefinition = {
                    pageSize: { width: 595, height: 842 },
                    content: pdfContent,
                };
                pdfMake.createPdf(docDefinition).download("IIA_Certificates.pdf");

                downloadButton.style.display = "block";
            }
        }
    </script>

    <style type="text/css">
        @media print { #downloadButton { display: none; } }
    </style>

@endsection
</body>
</html>
