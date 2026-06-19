<!DOCTYPE html>
<html>
<head>
    <title>Engineers Participant Certificate</title>
</head>
<body>
<h1>Hello {{ $data['name'] }}</h1>
{{--<p>Your reference code: {{ $data['reference_code'] }}</p>--}}
<p>
    <a href="{{ route('show_certificate', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download Engineers Certificate</a> <br><br>
</p>
<p>Thanks,</p>
<p><i>Engineers Events</i></p>
</body>
</html>
