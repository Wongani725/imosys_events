<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>

<!-- Add these links in the head section of your HTML file -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<!-- Add this script tag at the bottom of your HTML file, just before the closing body tag -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

{{--<style>--}}
{{--    #ICAMprogramme--}}
{{--    {--}}

{{--        background-image: url('{{ asset('background_images/icam_programme' . '.png')}}');--}}
{{--        --}}{{--background-image: url('{{ asset('images/ICAM Card-02 (2)' . '.png')}}');--}}
{{--        background-repeat: no-repeat;--}}
{{--        /*background-size: cover;*/--}}
{{--        background-position: center;--}}
{{--        background-size: contain;--}}

{{--    }--}}
{{--</style>--}}
{{--<div style="display: flex; justify-content: center; align-items: center; ">--}}
{{--    <div class="card">--}}
{{--        <h1 style="font-size: 24px; padding: 10px; text-align: center;">ICAM Programme</h1>--}}
{{--        <form id="ICAMprogramme" style="width: 1000px; height: 1000px">--}}
{{--            <!-- Your form content goes here -->--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}

<style>
    #ICAMprogramme {
        background-image: url('{{ asset('background_images/iim programme' . '.jpg')}}');

        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
    }
</style>


<div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div class="card" style="width: 90vw; max-width: 800px; height:800px; ">
        <div class="card header" style="background-color:red">
            <h1 style="font-size: 24px; padding: 10px; text-align: center; color: white">IIM Programme</h1>

        </div>
<div class="card body" style="background-color:red">
        <form id="ICAMprogramme" style="width: 90%; height: 90%; margin-top: 40px; align-items: center;margin-left: 30px">
            <!-- Your form content goes here -->
        </form>
    </div>
</div>
</div>
<br><br>
<center><button type="button" onclick="downloadFormImage()">Download as Image</button></center>
<script>
    function downloadFormImage() {
        html2canvas(document.querySelector("#ICAMprogramme")).then(function(canvas) {
            var imageData = canvas.toDataURL("image/png");

            // Create a temporary link element
            var link = document.createElement("a");
            link.href = imageData;
            link.download = "IIM_Programme.png";
            link.click();
        });
    }
</script>

