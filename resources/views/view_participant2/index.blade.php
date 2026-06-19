<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IIA Participant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <style>
        body { margin: 0; background: #f5f7fb; font-family: 'Segoe UI', sans-serif; }
        .alert { padding: 10px; margin: 10px auto; max-width: 600px; border-radius: 5px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px; margin: 20px auto; max-width: 700px; }
        .card h2 { color: #006198; text-align: center; }
        .btn-iia { background: #006198; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-iia:hover { background: #004d7a; }
        .btn-gold { background: #e7ae57; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-gold:hover { background: #d49a3e; }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        #myForm2 { width: 420px; height: 620px; margin: 0 auto; display: flex; flex-direction: column; justify-content: center; align-items: center; background-size: contain; background-repeat: no-repeat; background-position: center; }
        .qrcode-image { width: 100px; height: 100px; object-fit: contain; }
    </style>
</head>
<body>

@php
    $progImgPath = public_path('background_images/' . $participant->event_id . '_programme.png');
    $progImgUrl = file_exists($progImgPath) ? asset('background_images/' . $participant->event_id . '_programme.png') : null;
@endphp

@foreach (['success', 'error', 'info'] as $msg)
    @if(session($msg))
        <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }}">{{ session($msg) }}</div>
    @endif
@endforeach

{{-- Name Tag --}}
<div class="card">
    <h2>Name Tag</h2>
    @if($participant->image)
        <form id="myForm2" style="background-image:url('{{ asset($participant->image) }}');">
            @else
                <form id="myForm2" style="background:#fff;">
                    @endif
                    <div style="margin-top:230px;">{!! QrCode::format('svg')->size(140)->margin(1)->generate($participant->reference_code) !!}</div>
                    <div class="text-center" style="margin-top:5px;">
                        <span style="font-size:22px;color:#000;font-weight:bold;">{{ $participant->participant }}</span><br>
                        <span style="font-size:18px;color:#006198;font-weight:bold;">{{ $participant->company_name }}</span>
                    </div>
                </form>
                <div class="text-center mt-3" style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
                    <button onclick="downloadFormImage()" class="btn-iia"><i class="fas fa-download"></i> Download Name Tag</button>
                    <a href="{{ route('member.event-resources', $participant->reference_code) }}" class="btn-gold" style="text-decoration:none;display:inline-block;padding:10px 24px;border-radius:6px;"><i class="fas fa-file-alt"></i> View Event Resources</a>
                </div>
</div>

{{-- Meal Coupons --}}
@if($mealCoupons && count($mealCoupons))
    <div class="card">
        <h2>Meal Coupons</h2>
        <p class="text-center text-muted" style="font-size:14px;">Extra participants — use at the food counter.</p>
        <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
            @php $couponIdx = 0; @endphp
            @foreach($mealCoupons as $coupon)
                @php $couponIdx++; @endphp
                <div class="text-center" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;width:160px;">
                    <div id="coupon-{{ $coupon->unique_code }}">
                        <div>{!! QrCode::format('svg')->size(130)->margin(1)->generate($coupon->unique_code) !!}</div>
                        <div style="margin-top:6px;"><strong style="font-size:13px;">{{ $participant->participant }}</strong></div>
                        <div style="margin-top:2px;color:#006198;font-size:11px;font-weight:bold;">
                            @if($coupon->status === 'spouse')
                                Spouse
                            @else
                                Extra {{ $couponIdx }}
                            @endif
                        </div>
                    </div>
                    <button onclick="downloadCoupon('coupon-{{ $coupon->unique_code }}', '{{ $participant->participant }}-{{ $coupon->status === 'spouse' ? 'Spouse' : 'Extra'.$couponIdx }}')" class="btn-iia" style="margin-top:8px;padding:4px 10px;font-size:11px;">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Programme --}}
@if($progImgUrl)
    <div class="card">
        <h2>Event Programme</h2>
        <img src="{{ $progImgUrl }}" style="width:100%;max-width:600px;display:block;margin:auto;border-radius:8px;">
    </div>
@endif

{{-- Event Resources --}}
<div class="card" style="display:flex;flex-direction:column;">
    <h2>Event Resources</h2>
    <p class="text-center text-muted" style="font-size:14px;">Scan the QR code below to access event resources.</p>
    <div style="display:flex;justify-content:center;margin-top:auto;">
        <div class="text-center mt-2">
            <div>{!! QrCode::format('svg')->size(180)->margin(1)->generate(route('member.event-resources', $participant->reference_code)) !!}</div>
        </div>
    </div>
</div>
</div>
</div>

{{-- Download All --}}
<div class="text-center mb-4">
    <button onclick="downloadFormPDF()" id="downloadButton" class="btn-iia"><i class="fas fa-download"></i> Download All</button>
</div>

<script>
    function downloadFormImage() {
        html2canvas(document.querySelector("#myForm2")).then(c => {
            const a = document.createElement("a"); a.href = c.toDataURL("image/png"); a.download = "{{ $participant->participant }}.png"; a.click();
        });
    }

    function downloadCoupon(elementId, filename) {
        html2canvas(document.getElementById(elementId), { useCORS: true, scale: 2 }).then(c => {
            const a = document.createElement("a"); a.href = c.toDataURL("image/png"); a.download = filename + ".png"; a.click();
        });
    }

    function convertAndDownloadImage(url, name, participant) {
        const c = document.createElement('canvas'), ctx = c.getContext('2d');
        const img = new Image();
        img.onload = () => { c.width = img.width; c.height = img.height + 20; ctx.drawImage(img, 0, 0); ctx.font = '14px Arial'; ctx.fillStyle = '#000'; ctx.fillText(participant, 10, img.height + 15); const a = document.createElement('a'); a.href = c.toDataURL('image/png'); a.download = name + '.png'; a.click(); };
        img.src = url;
    }

    function downloadFormPDF() {
        const pages = document.querySelectorAll(".card");
        const content = [];
        let done = 0;
        pages.forEach((page, idx) => {
            setTimeout(() => {
                html2canvas(page, { scale: 2 }).then(c => {
                    content.push({ image: c.toDataURL("image/png"), width: 500, height: 600, marginBottom: 20 });
                    done++;
                    if (done === pages.length) {
                        pdfMake.createPdf({ pageSize: { width: 595, height: 842 }, content }).download("participant.pdf");
                    }
                });
            }, idx * 500);
        });
    }
</script>
</body>
</html>
