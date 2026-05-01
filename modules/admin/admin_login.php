<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login – SUNN Enrollment System</title>
    <!-- Use the background and card styles from your source[cite: 16] -->
</head>
<body>
<div class="login-card">
    <h1>SUNN Admin Portal</h1>
    <p class="subtitle">Restricted access — authorized personnel only</p>

    <form action="process_admin_login.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login to Admin Panel →</button>
    </form>

    <!-- This acts as the exit/logout button for the login screen[cite: 16] -->
    <a class="back-link" href="../../index.php" style="display: block; text-align: center; margin-top: 18px; color: #64748b; text-decoration: none;">
        ← Back to Home
    </a>
</div>
</body>
</html>