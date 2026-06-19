<!DOCTYPE html>
<html>
<head>
    <title>Certificate - {{ $participant->participant }}</title>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .cert-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 20px; max-width: 900px; margin: 0 auto; }
        #certificate {
            width: 100%;
            max-width: 800px;
            height: 565px;
            margin: 0 auto;
            background-image: url('{{ asset($participant->certificate_background ?? 'images/default_bg.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100% 100%;
            position: relative;
        }
        #certificate .name-overlay {
            position: absolute;
            bottom: 210px;
            left: 0;
            right: 0;
            text-align: center;
        }
        #certificate .name-overlay span {
            font-size: 28px;
            color: #006198;
            font-weight: bold;
        }
        .btn-iia { background: #006198; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-iia:hover { background: #004d7a; color: #fff; }
        .btn-gold { background: #e7ae57; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-gold:hover { background: #d49a3e; color: #fff; }
    </style>
</head>
<body>
    <div class="cert-card">
        <div class="text-center mb-4">
            <h2 style="color:#006198;">Certificate of Attendance</h2>
        </div>

        <div id="certificate">
            <div class="name-overlay">
                <span>{{ $participant->participant }}</span>
            </div>
        </div>

        <div class="text-center mt-4" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <button onclick="downloadPDF()" class="btn-iia"><i class="fas fa-file-pdf"></i> Download PDF</button>
            <button onclick="downloadImage()" class="btn-iia"><i class="fas fa-image"></i> Download Image</button>
            <a href="{{ route('member-dashboard') }}" class="btn btn-outline-secondary" style="padding:10px 24px;border-radius:6px;text-decoration:none;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <script>
        function downloadImage() {
            html2canvas(document.querySelector("#certificate")).then(function(canvas) {
                var link = document.createElement("a");
                link.href = canvas.toDataURL("image/png");
                link.download = "{{ $participant->participant }}.png";
                link.click();
            });
        }

        function downloadPDF() {
            html2canvas(document.querySelector("#certificate"), { scale: 2 }).then(function(canvas) {
                var imgData = canvas.toDataURL("image/png");
                var doc = {
                    pageSize: { width: 800, height: 565 },
                    content: [{ image: imgData, width: 800, height: 565 }]
                };
                pdfMake.createPdf(doc).download("{{ $participant->participant }}.pdf");
            });
        }
    </script>
</body>
</html>
