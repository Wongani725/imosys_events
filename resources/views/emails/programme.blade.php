<!DOCTYPE html>
<html>
<head>
    <title>Updated Programme</title>
</head>
<body>
<h1>Hello {{ $data['participantName'] }}</h1>
{{ $data['reference_code'] }}

<h3>Find the Updated Programme below:</h3>
<br>
<a href="{{ route('show_programme', ['id1' => $data['event_id']]) }}"> Click here to view the Updated Programme </a>





<p>Thanks,</p>
<p><i>iMoSyS</i></p>
</body>
</html>
