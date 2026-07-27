<!DOCTYPE html>
<html>
<head>
    <title>Login OTP</title>
</head>
<body>
    <h2>Secure Login</h2>
    <p>Use the following OTP to log into your account:</p>
    <h3 style="background-color: #f4f4f4; padding: 10px; display: inline-block; letter-spacing: 2px;">{{ $otp }}</h3>
    <p>This OTP is valid for 15 minutes. If you did not attempt to log in, please secure your account.</p>
    <br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
