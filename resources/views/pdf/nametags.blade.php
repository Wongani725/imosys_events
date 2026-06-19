<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Name Tags</title>
    <style>
        @page { margin: 0; padding: 0; size: A4 portrait; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }

        .pdf-page {
            page-break-after: always;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
            padding: 0;
            justify-items: center;
            align-items: start;
        }

        .name-tag {
            width: 420px;
            height: 620px;
            border-radius: 10px;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 283px;
            align-items: center;
            padding-bottom: 40px;
        }

        .qrcode-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .qrcode-wrapper img {
            width: 140px;
            height: 140px;
        }

        .participant-name {
            font-size: 22px;
            color: #000;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .company-name {
            font-size: 18px;
            color: #e7ae57;
            font-weight: bold;
            text-align: center;
        }

        .tag-back {
            width: 420px;
            height: 620px;
            border-radius: 10px;
            background: #f8f9fa;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .tag-back .label {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .tag-back .qr-wrap img {
            width: 200px;
            height: 200px;
        }

        .tag-back .hint {
            font-size: 12px;
            color: #888;
            margin-top: 15px;
            text-align: center;
        }

        .tag-back .member-id {
            font-size: 10px;
            color: #aaa;
            align-self: flex-end;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $chunks = array_chunk($participants->all(), 4);
    @endphp
    @foreach($chunks as $chunk)
        {{-- FRONTS --}}
        <div class="pdf-page">
            @foreach($chunk as $p)
                <div class="name-tag" style="background-image: url('{{ $backgroundImage }}');">
                    <div class="qrcode-wrapper" style="margin-top: 120px;">
                        @php $qrPng = base64_encode(QrCode::format('png')->size(140)->margin(1)->generate($p->reference_code)); @endphp
                        <img src="data:image/png;base64,{{ $qrPng }}" width="140" height="140">
                    </div>
                    <div class="participant-name">{{ $p->participant }}</div>
                    <div class="company-name">{{ $p->company_name }}</div>
                </div>
            @endforeach
        </div>

        {{-- BACKS (mirrored for duplex: swap 0↔1, 2↔3) --}}
        <div class="pdf-page">
            @php $mirror = [1, 0, 3, 2]; @endphp
            @foreach($mirror as $i)
                @php $p = $chunk[$i] ?? null; @endphp
                @if($p)
                    @if($p->is_master_tag ?? false)
                        <div class="tag-back" style="background: #000;"></div>
                    @else
                        @php $url = route('member.event-resources', ['reference_code' => $p->reference_code], false); @endphp
                        @php $qrPng = base64_encode(QrCode::format('png')->size(200)->margin(1)->generate($url)); @endphp
                        <div class="tag-back">
                            <div class="label">Scan for Event Resources</div>
                            <div class="qr-wrap"><img src="data:image/png;base64,{{ $qrPng }}" width="200" height="200"></div>
                            <div class="hint">Programme, Brochure &amp; Documents</div>
                            <div class="member-id">{{ $p->reference_code }}</div>
                        </div>
                    @endif
                @else
                    <div style="width:420px;height:620px;"></div>
                @endif
            @endforeach
        </div>
    @endforeach
</body>
</html>
