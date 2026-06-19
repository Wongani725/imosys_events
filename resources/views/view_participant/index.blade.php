@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>

@php
    $progImgPath = public_path('background_images/' . $event_id . '_programme.png');
    $progImgUrl = file_exists($progImgPath) ? asset('background_images/' . $event_id . '_programme.png') : null;
    $paddingTop = $event->name_tag_padding_top ?? 283;
    $qrTop = $event->name_tag_qr_top ?? 120;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color:#006198;">{{ $participant->participant }}</h2>
        <div>
            <button onclick="downloadFormPDF()" id="downloadButton" class="btn" style="background-color:#006198;color:#fff;"><i class="fas fa-download"></i> Download PDF</button>
            <a href="{{ route('view_participants', $event_id) }}" class="btn btn-outline-secondary ms-2"><i class="bx bx-arrow-back"></i> Back</a>
        </div>
    </div>

    {{-- Name Tag --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center">
            <h5 style="color:#006198;">Name Tag</h5>
            <div id="nameTagForm" style="background-image:url('{{ asset($image) }}');background-size:cover;background-repeat:no-repeat;background-position:center;width:420px;height:620px;margin:0 auto;display:flex;flex-direction:column;align-items:center;padding-top:{{ $paddingTop }}px;">
                <div style="margin-top:{{ $qrTop }}px;">
                    {!! QrCode::format('svg')->size(140)->margin(1)->generate($participant->reference_code) !!}
                </div>
                <div style="font-size:22px;font-weight:bold;color:#000;text-align:center;margin-top:10px;">{{ $participant->participant }}</div>
                <div style="font-size:18px;color:#006198;font-weight:bold;text-align:center;">{{ $participant->company_name }}</div>
            </div>
        </div>
    </div>

    {{-- Programme --}}
    @if($progImgUrl)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center">
            <h5 style="color:#006198;">Programme</h5>
            <img src="{{ $progImgUrl }}" class="img-fluid" style="max-width:100%;border-radius:8px;">
        </div>
    </div>
    @endif

    {{-- Extra Meal Coupons --}}
    @if($mealCoupons && count($mealCoupons))
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 style="color:#006198;">Extra Meal Coupons</h5>
            <div class="row">
                @php $couponIdx = 0; @endphp
                @foreach($mealCoupons as $coupon)
                    @php $couponIdx++; @endphp
                    <div class="col-md-3 text-center mb-3">
                        <div id="coupon-{{ $coupon->unique_code }}" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;">
                            <strong style="color:#006198;">
                                @if($coupon->status === 'spouse')
                                    Spouse
                                @else
                                    Extra {{ $couponIdx }}
                                @endif
                            </strong>
                            <div class="mt-2">{!! QrCode::format('svg')->size(130)->margin(1)->generate($coupon->unique_code) !!}</div>
                            <div class="mt-1"><small><strong>{{ $participant->participant }}</strong></small></div>
                            <button onclick="downloadCoupon('coupon-{{ $coupon->unique_code }}', '{{ $participant->participant }}-{{ $coupon->status === 'spouse' ? 'Spouse' : 'Extra'.$couponIdx }}')" class="btn btn-sm mt-2" style="background-color:#006198;color:#fff;">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function downloadCoupon(elementId, filename) {
    html2canvas(document.getElementById(elementId), { useCORS: true, scale: 2 }).then(c => {
        const a = document.createElement("a"); a.href = c.toDataURL("image/png"); a.download = filename + ".png"; a.click();
    });
}

function downloadFormPDF() {
    const btn = $("#downloadButton");
    btn.html('<span class="spinner-border spinner-border-sm"></span>').prop("disabled", true);

    const pages = [document.getElementById("nameTagForm")];
    const pdfContent = [];
    let done = 0;

    pages.forEach((page, idx) => {
        setTimeout(() => {
            html2canvas(page, { useCORS: true, allowTaint: true, scale: 2 }).then(canvas => {
                pdfContent.push({ image: canvas.toDataURL("image/png"), width: 400, height: 550, marginBottom: 20 });
                done++;
                if (done === pages.length) {
                    pdfMake.createPdf({ pageSize: { width: 595, height: 842 }, content: pdfContent }).download("name_tag.pdf");
                    btn.html('<i class="fas fa-download"></i> Download PDF').prop("disabled", false);
                }
            });
        }, idx * 500);
    });
}

function convertAndDownloadImage(url, name, participant) {
    const c = document.createElement('canvas'), ctx = c.getContext('2d');
    const img = new Image();
    img.onload = () => { c.width = img.width; c.height = img.height + 20; ctx.drawImage(img, 0, 0); ctx.font = '14px Arial'; ctx.fillStyle = '#000'; ctx.fillText(participant, 10, img.height + 15); const a = document.createElement('a'); a.href = c.toDataURL('image/png'); a.download = name + '.png'; a.click(); };
    img.crossOrigin = "anonymous";
    img.src = url;
}
</script>
@endsection
