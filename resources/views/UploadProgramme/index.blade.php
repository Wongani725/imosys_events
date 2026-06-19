<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>

@extends('layouts.app')

@section('title', 'Events')

@section('vendor-css')
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="{{asset('')}}cms/vendor/libs/sweetalert2/sweetalert2.css" />
@endsection

@section('page-css')
    {{-- add css links and style tag for current page--}}
@endsection

@section('head-js')
    {{-- add js script to be included in head section--}}
@endsection

<style>
    .pdf-page {
        /*display: grid;*/
        /*grid-template-columns: repeat(2, 1fr);*/
        gap: 20px;
        margin-bottom: 40px;
    }


    #ICAMprogramme
    {
        background-image: url("{{ asset('background_images/' . $id . '_programme.png') }}");

        {{--background-image: url('{{ asset('images/ICAM Card-02 (2)' . '.png')}}');--}}
        background-repeat: no-repeat;
        /*background-size: cover;*/
        background-position: center;
        background-size: contain;

    }
</style>





        @section('content')
            <div class="card">




                    <div class="card-header bgsize-primary-4 white card-header">

                        <h4 class="card-title">Upload Programme</h4>

                    </div>

                    <div class="card-body">

                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-block">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>{{ $message }}</strong>
                            </div>
                            <br>
                        @endif
                        <form action="{{ route('import_program', ['id' => $id]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label for="image_advert">Insurance Events Programme</label>
                                <input type="file" name="icam_programme" class="form-control" accept="image/*" required>
                            </div>


                                    <div class="input-group-append" id="button-addon2">

                                        <button class="btn btn-primary square" type="submit"><i class="ft-upload mr-1"></i> Upload</button>

                                    </div>



                        </form>



                </div>

            </div><br><br>
            <div style="display: flex; justify-content: center; align-items: center; ">
                <div class="card">
                    <h1 style="font-size: 24px; padding: 10px; text-align: center;">Insurance Events Programme</h1>
                    <form id="ICAMprogramme" style="width: 1000px; height: 1000px">
                        <!-- Your form content goes here -->
                    </form>
                </div>
            </div>


            <center><button type="button" onclick="downloadFormImage()">Download as Image</button></center>
            <script>
                function downloadFormImage() {
                    html2canvas(document.querySelector("#ICAMprogramme")).then(function(canvas) {
                        var imageData = canvas.toDataURL("image/png");

                        // Create a temporary link element
                        var link = document.createElement("a");
                        link.href = imageData;
                        link.download = "ICAM_Programme.png";
                        link.click();
                    });
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
                pdfMake.createPdf(docDefinition).download("ICAM events programme.pdf");
            }
        }
    </script>



    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

    <script>

        $(document).ready(function() {

            $('#example').DataTable();

        } );

    </script>



@endsection
