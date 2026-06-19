<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Master Tag - {{ $referenceCode }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <style>
        body { margin: 0; padding: 0; background: #f5f7fb; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .name-tag {
            width: 420px; height: 620px;
            border-radius: 10px;
            background-size: cover; background-repeat: no-repeat; background-position: center;
            box-sizing: border-box;
            display: flex; flex-direction: column;
            justify-content: flex-start;
            align-items: center; padding-bottom: 40px;
        }
        .name-tag {
            background-image: url('{{ $backgroundImage }}');
            padding-top: 350px;
        }
        .qrcode-wrapper { display: flex; justify-content: center; margin-bottom: 10px; }
        .qrcode-wrapper svg { width: 100px; height: 100px; }
        .participant-name { font-size: 22px; color: #000; font-weight: bold; text-align: center; text-transform: uppercase; }
        .company-name { font-size: 18px; color: #006198; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div id="tagContainer">
        <div class="name-tag">
            <div class="qrcode-wrapper" style="margin-top: 10px;">
                {!! QrCode::format('svg')->size(140)->margin(1)->generate($referenceCode) !!}
            </div>
            <div class="participant-name">Mastertag</div>
            <div class="company-name">IIA</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            let tag = document.querySelector('.name-tag');

            html2canvas(tag, {
                useCORS: true, allowTaint: true, scale: 2,
                scrollX: 0, scrollY: 0,
                windowWidth: tag.offsetWidth, windowHeight: tag.offsetHeight
            }).then(canvas => {
                pdfMake.createPdf({
                    pageSize: { width: 595, height: 842 },
                    content: [{
                        image: canvas.toDataURL("image/png"),
                        fit: [540, 742]
                    }]
                }).download("master_tag_{{ $referenceCode }}.pdf");
            });
        });
    </script>
</body>
</html>
