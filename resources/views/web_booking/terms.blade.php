<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions{{ $event ? ' - ' . $event->event_name : '' }} | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .terms-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .terms-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 860px;
            padding: 40px;
        }
        .terms-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 8px;
        }
        .terms-header img {
            height: 60px;
            width: auto;
        }
        .terms-header h2 {
            color: #006198;
            font-weight: 700;
            font-size: 24px;
            margin: 0;
        }
        .terms-event {
            color: #6c757d;
            font-size: 14px;
            margin-left: 76px;
            margin-bottom: 16px;
        }
        .terms-divider {
            border: none;
            border-top: 2px solid #97D700;
            margin: 0 0 24px;
        }
        .terms-content {
            line-height: 1.8;
            white-space: pre-line;
            color: #333;
            font-size: 15px;
            min-height: 200px;
        }
        .terms-content p:last-child {
            margin-bottom: 0;
        }
        .terms-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
        }
        .terms-footer a {
            color: #006198;
            text-decoration: none;
        }
        .terms-footer a:hover {
            text-decoration: underline;
        }
        .btn-close-terms {
            background: #006198;
            color: #fff;
            border: none;
            padding: 8px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-close-terms:hover {
            background: #004a73;
            color: #fff;
        }
        @media (max-width: 576px) {
            .terms-card { padding: 24px; }
            .terms-header { flex-direction: column; text-align: center; }
            .terms-event { margin-left: 0; text-align: center; }
        }
    </style>
</head>
<body>

    <div class="terms-wrapper">

        <div class="terms-card">

            <div>
                <div class="terms-header">
                    <img src="{{ asset('images/alogo2.png') }}" alt="Logo">
                    <h2>Terms and Conditions</h2>
                </div>
                @if($event)
                    <div class="terms-event">{{ $event->event_name }}</div>
                @endif
            </div>

            <hr class="terms-divider">

            @if($terms)
                <div class="terms-content">{!! nl2br($terms) !!}</div>
            @else
                <p class="text-muted">No terms have been set for this event.</p>
            @endif

            <div class="terms-footer">
                &copy; {{ date('Y') }}
                <a href="#">{{ config('app.name') }}</a>.
                {{ env('POWERED_BY') }} <a href="{{ env('DEVELOPER_WEBSITE') }}" target="_blank">{{ env('DEVELOPER') }}</a>
            </div>

        </div>

    </div>

</body>
</html>
