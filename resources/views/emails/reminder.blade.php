<!DOCTYPE html>
<html>
<head>
    <title>Payment Reminder</title>
</head>
<body>
<p>Dear {{ $booker->name }},</p>

<p>This is a friendly reminder that your booking (ID: {{ $booker->booking_reference ?? $booker->bookingID }}) is still marked as <strong>Pending</strong>.</p>

<p>Please ensure that payment is completed to secure your place.</p>

<p>If you have already made the payment, kindly upload the proof of payment.</p>

<p>Thank you,<br>{{ config('app.name') }} Team</p>
</body>
</html>
