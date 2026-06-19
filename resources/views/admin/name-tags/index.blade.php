@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <title>IIA Name Tags</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.0/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body { margin: 0; padding: 0; background-color: #f5f7fb; }

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
            background-image: url('{{ asset($image) }}'); margin-right: 10px;
            padding-top: {{ $event->name_tag_padding_top ?? 350 }}px; }

        .qrcode-wrapper { display: flex; justify-content: center; margin-bottom: 10px; }
        .qrcode-wrapper svg { width: 100px; height: 100px; }

        .participant-name { font-size: 22px; color: #000; font-weight: bold; text-align: center; }
        .company-name { font-size: 18px; color: #006198; font-weight: bold; text-align: center; }

        .pdf-page {
            display: grid; grid-template-columns: repeat(2, 1fr);
            grid-gap: 10px; padding: 10px;
            page-break-after: always; justify-items: center;
        }

        .tag-back {
            width: 420px; height: 620px;
            border-radius: 10px;
            background-size: cover; background-repeat: no-repeat; background-position: center;
            box-sizing: border-box;
            display: flex; flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 300px 40px 40px;
        }
        .tag-back .overlay {
            background: rgba(255,255,255,0.85);
            border-radius: 10px;
            padding: 20px 30px;
            display: flex; flex-direction: column;
            align-items: center;
        }
        .tag-back .label { font-size: 14px; color: #006198; font-weight: bold; margin-bottom: 10px; text-align: center; }
        .tag-back svg { width: 120px; height: 120px; }
        .tag-back .hint { font-size: 11px; color: #555; margin-top: 10px; text-align: center; }
        .tag-back .member-id { font-size: 9px; color: #999; align-self: flex-end; margin-top: 8px; }

        @media print { .hide-on-print { display: none !important; } }

        .btn-iia { background-color: #006198; color: #fff; border: none; }
        .btn-iia:hover { background-color: #004d7a; color: #fff; }
        .btn-iia-download { background-color: #006198; color: #fff; border: none; }
        .btn-iia-download:hover { background-color: #004d7a; color: #fff; }
        .btn-outline-iia { border: 1px solid #006198; color: #006198; background: transparent; }
        .btn-outline-iia:hover { background: #006198; color: #fff; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: #006198;">Name Tags — {{ $event->event_name }}</h2>
        <a href="{{ route('view_participants', $event->event_id) }}" class="btn btn-outline-iia btn-sm hide-on-print"><i class="bx bx-arrow-back"></i> Back</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Batch selector --}}
    <div class="card border-0 shadow-sm mb-4 hide-on-print">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong style="color: #006198;">{{ $totalParticipants }}</strong> <span class="text-muted">participants with QR codes</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted">Show latest:</span>
                <a href="{{ route('admin.name-tags.index', ['event_id' => $event_id, 'starting_id' => max(0, $totalParticipants - 20)]) }}" class="btn btn-sm {{ request('starting_id') == max(0, $totalParticipants - 20) ? 'btn-iia' : 'btn-outline-iia' }}">20</a>
                <a href="{{ route('admin.name-tags.index', ['event_id' => $event_id, 'starting_id' => max(0, $totalParticipants - 40)]) }}" class="btn btn-sm {{ request('starting_id') == max(0, $totalParticipants - 40) ? 'btn-iia' : 'btn-outline-iia' }}">40</a>
                <a href="{{ route('admin.name-tags.index', ['event_id' => $event_id, 'starting_id' => max(0, $totalParticipants - 80)]) }}" class="btn btn-sm {{ request('starting_id') == max(0, $totalParticipants - 80) ? 'btn-iia' : 'btn-outline-iia' }}">80</a>
                <a href="{{ route('admin.name-tags.index', ['event_id' => $event_id, 'starting_id' => 0]) }}" class="btn btn-sm {{ !request('starting_id') || request('starting_id') == 0 ? 'btn-iia' : 'btn-outline-iia' }}">All</a>
            </div>
        </div>
    </div>

    {{-- Display & Download --}}
    @if(count($participants))
    <div class="d-flex justify-content-between align-items-center mb-3 hide-on-print">
        <span class="text-muted">Showing {{ count($participants) }} tags (newest first)</span>
        <button class="btn btn-iia-download" id="downloadButton" onclick="downloadFormPDF()">
            <i class="fas fa-download"></i> Download PDF
        </button>
    </div>

    <div id="pdfContent">
        @foreach($participants as $index => $participant)
            @if($index % 4 === 0)
                <div class="pdf-page">
            @endif

            <div class="name-tag">
                <div class="qrcode-wrapper" style="margin-top: {{ $event->name_tag_qr_top ?? 10 }}px;">
                    {!! QrCode::format('svg')->size(140)->margin(1)->generate($participant->reference_code) !!}
                </div>
                <div class="participant-name text-capitalize">{{ $participant->participant }}</div>
                <div class="company-name">{{ $participant->company_name }}</div>
            </div>

            @if($index % 4 === 3 || $loop->last)
                </div>
                {{-- Backs page with program QR codes --}}
                @if(isset($participants[$index]) || !$loop->last)
                <div class="pdf-page">
                    @php $progUrl = $programPdf ? asset($programPdf) : asset($image); @endphp
                    @php $chunkStart = $index - ($index % 4); @endphp
                    @php $mirror = [1, 0, 3, 2]; @endphp
                    @foreach($mirror as $b)
                        @php $bp = $participants[$chunkStart + $b] ?? null; @endphp
                        @if($bp)
                            @php $url = route('member.event-resources', ['reference_code' => $bp->reference_code], false); @endphp
                            <div class="tag-back" style="background-image: url('{{ $progUrl }}');">
                                <div class="overlay">
                                    <div ></div>
                                    {!! QrCode::format('svg')->size(180)->margin(1)->generate($url) !!}
                                    <div class="hint">{{ $bp->participant }}</div>
                                </div>
                                <!-- <div class="member-id">{{ $bp->reference_code }}</div> -->
                            </div>
                        @else
                            <div class="tag-back" style="opacity:0;"></div>
                        @endif
                    @endforeach
                </div>
                @endif
            @endif
        @endforeach
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">No participants found for this event.</div>
    </div>
    @endif
</div>

@if(count($participants))
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function downloadFormPDF() {
        let btn = $("#downloadButton");
        btn.html(`<span class="spinner-border spinner-border-sm" role="status"></span> Generating...`).prop("disabled", true);

        let pages = document.querySelectorAll(".pdf-page");
        let pdfContent = [];
        let done = 0;

        pages.forEach((page, index) => {
            setTimeout(() => {
                html2canvas(page, {
                    useCORS: true, allowTaint: true, scale: 2,
                    scrollX: 0, scrollY: 0,
                    windowWidth: page.offsetWidth, windowHeight: page.offsetHeight
                }).then(canvas => {
                    pdfContent.push({
                        image: canvas.toDataURL("image/png"),
                        fit: [540, 742], marginBottom: 30
                    });
                    done++;
                    if (done === pages.length) {
                        pdfMake.createPdf({
                            pageSize: { width: 595, height: 842 },
                            content: pdfContent
                        }).download("name_tags.pdf");
                        btn.html(`<i class="fas fa-download"></i> Download PDF`).prop("disabled", false);
                    }
                });
            }, index * 300);
        });
    }
</script>
@endif
</body>
</html>
@endsection
