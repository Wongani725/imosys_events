<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Extras Name Tags</title>
    <style>
        @page { margin: 15px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; }
        .page { width: 210mm; height: 297mm; page-break-after: always; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; width: 100%; height: 100%; }
        .cell {
            border: 1mm dashed #ccc;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 15px;
            position: relative;
        }
        .cell .header {
            position: absolute;
            top: 8px;
            font-size: 8pt;
            color: #006198;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cell .label {
            font-size: 16pt;
            color: #e74c3c;
            font-weight: bold;
            background: #fff;
            padding: 4px 14px;
            border: 2px solid #e74c3c;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .cell .name {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }
        .cell .event {
            font-size: 10pt;
            color: #006198;
            margin-bottom: 10px;
        }
        .cell .qrcode img { width: 90px; height: 90px; }
        .cell .ref {
            font-size: 7pt;
            color: #999;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @foreach($extras->chunk(4) as $chunk)
    <div class="page">
        <div class="grid">
            @foreach($chunk as $extra)
            <div class="cell">
                <div class="header">{{ $eventName }}</div>
                @if($extra->is_spouse)<div class="label">SPOUSE</div>@else<div class="label">EXTRA {{ $extra->index }}</div>@endif
                <div class="name">{{ $participantName }}</div>
                <div class="event">{{ $eventName }}</div>
                <div class="qrcode"><img src="{{ route('qrcode', $extra->unique_code) }}" alt="QR"></div>
                <div class="ref">{{ $extra->unique_code }}</div>
            </div>
            @endforeach
            @for($i = $chunk->count(); $i < 4; $i++)
            <div class="cell"></div>
            @endfor
        </div>
    </div>
    @endforeach
</body>
</html>
