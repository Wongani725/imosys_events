<!DOCTYPE html>
<html>
<head>
    <title>IIM Meal Redemption</title>
</head>
<body>
<h1>Hello {{ $data['participantName'] }}</h1>
<h3>you have redeemed a meal</h3>
<h3>Unique Code : {{ $data['unique_code'] }}</h3>
<h3>meals redeemed: {{ $data['redeemed'] }} </h3>
<h3>meals remaining: {{ $data['remainingMeals'] }} </h3>
{{--<p>Your reference code: {{ $data['referenceCode'] }}</p>--}}
{{--<p>--}}
{{--    <a href="{{ route('show_participant2', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download Name Tag</a> <br><br>--}}
{{--    --}}{{--    <a href="{{ route('add_programme2', ['id1' => $data['reference_code'], 'id2' => $data['event_id']]) }}">View and Download Event Programme</a>--}}
{{--    <a href="{{ route('show_programme', ['id1' => $data['event_id']]) }}">View and Download Event Programme</a>--}}
{{--</p>--}}
<p>Thanks,</p><br>
<p><i>Insurance events</i></p>
</body>
</html>
