<!DOCTYPE html>
<html>
<head>
    <title>Proof of Payment Updated</title>
</head>
<body>
<p>Dear {{ $booking->name }},</p>

<p>We have received your proof of payment for booking ID: <strong>{{ $booking->booking_reference ?? $booking->bookingID }}</strong>.</p>

{{--<p>Your receipt number is: <strong>{{ $booking->receipt_number }}</strong><br>--}}
    Date Paid: <strong>{{ $booking->date_paid }}</strong></p>

<p>Your payment status is now: <strong>{{ $booking->booking_status }}</strong>.</p>

<p>We will process your payment shortly. Thank you!</p>

<p>Regards,<br>MEI EVENTS</p>
</body>
</html>
