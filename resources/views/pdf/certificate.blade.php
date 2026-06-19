<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Attendance</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', 'Times New Roman', serif;
        }
        .certificate-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @if($participant->certificate_background)
        .certificate-wrapper {
            background-image: url('{{ public_path($participant->certificate_background) }}');
            background-size: cover;
            background-position: center;
        }
        @endif
        .certificate-content {
            text-align: center;
            padding: 60px;
            position: relative;
            z-index: 2;
        }
        .certificate-title {
            font-size: 28px;
            color: #1a3c6e;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .certificate-subtitle {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        .participant-name {
            font-size: 36px;
            color: #006198;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .event-name {
            font-size: 22px;
            color: #333;
            margin: 15px 0;
        }
        .event-theme {
            font-size: 16px;
            color: #666;
            font-style: italic;
            margin-bottom: 25px;
        }
        .certificate-text {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
        }
        .event-dates {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
        .qr-section {
            position: absolute;
            bottom: 30px;
            right: 30px;
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-content">
            <div class="certificate-title">Certificate of Attendance</div>
            <div class="certificate-subtitle">Presented to</div>
            <div class="participant-name">{{ $participant->participant }}</div>
            @if($participant->company_name)
                <div class="certificate-text">{{ $participant->company_name }}</div>
            @endif
            <div class="certificate-text">for attending the</div>
            <div class="event-name">{{ $participant->event_name }}</div>
            @if($participant->theme)
                <div class="event-theme">"{{ $participant->theme }}"</div>
            @endif
            <div class="event-dates">
                {{ \Carbon\Carbon::parse($participant->start_date)->format('d F Y') }} -
                {{ \Carbon\Carbon::parse($participant->end_date)->format('d F Y') }}
            </div>
            <div class="event-dates" style="margin-top:5px;">{{ $participant->event_venue }}</div>
        </div>
        @if($participant->reference_code)
        <div class="qr-section">
            <img src="{{ route('qrcode', $participant->reference_code) }}" width="80" height="80">
        </div>
        @endif
    </div>
</body>
</html>
