<!DOCTYPE html>
<html>
<head>
    <title>Booking Approved</title>
</head>
<body style="font-family:Arial,sans-serif;padding:20px;">
    <h2 style="color:#006198;">IIA Malawi</h2>
    <hr>

    <h3>Hello {{ $booker->name }},</h3>
    <p>Your booking for <strong>{{ $eventNames ?? $booker->event_id }}</strong> has been <strong style="color:green;">approved and confirmed</strong>!</p>
    <p>Thank you for your participation.</p>

    @if($nameTagLink)
        <h4>Your Name Tag</h4>
        <p>
            <a href="{{ $nameTagLink }}" style="display:inline-block;padding:10px 20px;background-color:#006198;color:#fff;text-decoration:none;border-radius:6px;">
                View Your Name Tag
            </a>
        </p>
    @endif

    <hr>
    <p style="color:#999;font-size:12px;">For any inquiries, contact IIA Malawi.</p>
</body>
</html>
