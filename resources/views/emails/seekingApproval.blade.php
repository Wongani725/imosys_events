<!DOCTYPE html>
<html>
<head>
    <title>Request for approval</title>
</head>
<body>


<h1>Hello {{ $data['participant'] }}</h1>
<p>
Use this link to login into the system for your action
    <a href="{{ route('auth.pending') }}">Link</a> <br><br>

</p>


<p>Thanks,</p>
<p><i>iMoSyS</i></p>
</body>
</html>
