<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Enrollment Status</title>
</head>
<body>
    <h2>Check Your Enrollment Status</h2>
    <form method="POST" action="check_status_process.php">
        <label>Reference Number <span>*</span></label><br>
        <input type="text" name="reference_no" placeholder="e.g. R9T45E87" required><br><br>
        <button type="submit">Check Status</button>
    </form>
</body>
</html>