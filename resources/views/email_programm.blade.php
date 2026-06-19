<!DOCTYPE html>
<html>
<head>
    <title>ICAM PROGRAMME</title>
</head>
<body>

{{--<p>PUSH NOTIFICATION TRIAL</p>--}}
<h1>Hello {{ $data['participant'] }}</h1>
{{--<p>Your reference code: {{ $data['reference_code'] }}</p>--}}
<p>
{{--    <a href="{{ route('show_participant2', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download </a> <br><br>--}}
    {{--    <a href="{{ route('add_programme2', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download Event Programme</a>--}}
    <a href="{{ route('show_programme', ['id1' => $data['event_id']]) }}">View and Download Recent Event Programme</a>


</p>


<p>Thanks,</p>
<p><i>iMoSyS</i></p>
</body>
</html>
