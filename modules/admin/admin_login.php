<?php session_start(); 
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – SUNN Enrollment System</title>
    <link rel="stylesheet" href="sunn-admin.css">
</head>
<body class="login-body page-centered">

<div class="page-wrapper login-container">
    <!-- Left Brand Panel -->
<div class="brand-panel login-left">
    <!-- Image first[cite: 5] -->
    <div class="logo-container">
        <img src="logo.png" alt="SUNN Logo" class="official-logo logo-circle">
    </div>
    
    <!-- Text follows[cite: 5] -->
    <div class="brand-name">State University of <br> Northern Negros</div>
    <div class="brand-tagline">Enrollment System</div>
    <div class="brand-divider"></div>
</div>
    <!-- Right Login Panel -->
    <div class="login-panel login-right">
        
        <h1>Welcome Back</h1>
        <p class="subtitle">Restricted access — authorized personnel only</p>

        <?php if ($error === 'session'): ?>
            <div class="error-alert">⚠️ Your session has expired. Please log in again.</div>
        <?php endif; ?>

        <form action="process_admin_login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Login to Admin Panel</button>
        </form>
        <a class="back-link" href="../../index.php">Back to Home</a>
    </div>
</div>

</body>
</html>