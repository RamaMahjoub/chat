<!DOCTYPE html>
<html lang="en-US">
<head>
    {{-- <meta charset="utf-8"> --}}
</head>
<body>

<div>
    Hi
    <br>
    Thank you for creating an account with us. Don't forget to complete your registration!
    <br>
    Your Verification code is: {{$verification_code}}
    <br>

    {{-- <a href="{{ url('api/verify', $verification_code)}}">Confirm my email address </a> --}}

    <br/>
</div>

</body>
</html>
