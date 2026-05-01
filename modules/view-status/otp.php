<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNN Enrollment System</title>
</head>
<body>
    <form action="process_otp.php" method="POST">
        <label for="otp">Enter OTP:</label><br>
        <input type="text" name="otp" required><br><br>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>