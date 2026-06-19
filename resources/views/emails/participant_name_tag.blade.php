<!DOCTYPE html>
<html>
<head>
    <title>2026 MLS Annual Conference & AGM -Name Tag with Meal Coupons</title>
</head>
<body>

{{--<p>PUSH NOTIFICATION TRIAL</p>--}}
<h1>Dear {{ $data['participant'] }}</h1>
<!--<p>Your reference code: {{ $data['reference_code'] }}</p>-->
<p>Please find the links for;</p>
<p>1. Digital name tag with a QR code that contains meal tickets (coupons)</p>
<p>2. QR code that contains meal tickets (coupons) for your companion if that option was selected during registration</p>
<p>3. 2026 MLS Annual Conference & AGM Program</p>
<p>Click on the link below to download</p>
<p>
    <a href="{{ route('show-participant', ['reference_code' => $data['reference_code']]) }}">View and Download Name Tag</a> <br><br>
    {{--    <a href="{{ route('add_programme2', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download Event Programme</a>--}}
{{--    <a href="{{ route('show_programme', ['id1' => $data['event_id']]) }}">View and Download Event Programme</a>--}}


</p>

<p>Kind regards,</p>
{{--<span><img src="{{ url('/images/ICAM_logoo.png') }}" style="margin-top: 2%;width: 150px; height:40px;"></span>--}}
<p style="color: #e7ae57; font-size: 20px"><b>MLS EVENTS</b></p>
</body>
</html>
