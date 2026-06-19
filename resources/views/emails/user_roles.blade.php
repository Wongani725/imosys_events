<!DOCTYPE html>
<html>
<head>
    <title>ICAM login</title>
</head>
<body>


<h1>Hello {{ $data['name'] }}</h1>
<p>
    Use this link to login into the system
    <a href="{{ route('login') }}">Link</a> <br>

    <h>LOGIN CREDENTIALS</h>
<p>Email address: {{$data['email']}}</p>

<p>password: <b>12345678</b></p>

</p>


<p>Thanks,</p>
<p><i>iMoSyS</i></p>
</body>
</html>
