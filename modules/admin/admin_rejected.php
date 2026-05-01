<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied – SUNN Admin</title>
    <!-- Linked external stylesheet for centered branding and card styles[cite: 5] -->
    <link rel="stylesheet" href="sunn-admin.css">
</head>
<body class="page-centered rejected-page">

<div class="card">
    <!-- 1. Replaced SVG with logo.png[cite: 5] -->
    <div class="logo-mini" style="width: 100px; margin: 0 auto 20px;">
        <img src="logo.png" alt="SUNN Logo" style="width: 100%; height: auto; object-fit: contain;">
    </div>  
    <?php
    $reason = $_GET['reason'] ?? 'invalid';
    if ($reason === 'locked'):
    ?>
        <div class="reason-badge">Account Locked</div>
        <h1>Access Locked</h1>
        <p>Too many failed login attempts detected. Please wait 15 minutes before trying again.</p>
    <?php else: ?>
        <!-- 2. Updated Description for Incorrect Credentials[cite: 6] -->

        <h1>Access Denied</h1>
        <p><strong>Incorrect username or password.</strong> Please check your credentials and try again. This portal is for authorized SUNN personnel only.</p>
        
        <!-- 3. Updated Button: Back to Login[cite: 6] -->
        <a class="btn-try" href="admin_login.php">Back to Login</a>
    <?php endif; ?>

    <div class="footer-note">SUNN Enrollment System &nbsp;·&nbsp; Admin Portal</div>
</div>

</body>
</html>