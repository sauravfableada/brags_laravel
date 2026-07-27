<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
</head>
<body>
    <h2>Password Reset Request</h2>
    <p>We received a request to reset your password. Use the following OTP to proceed:</p>
    <h3 style="background-color: #f4f4f4; padding: 10px; display: inline-block; letter-spacing: 2px;">{{ $otp }}</h3>
    <p>This OTP is valid for 15 minutes. If you did not request a password reset, please ignore this email.</p>
    <br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
