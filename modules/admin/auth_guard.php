<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect to login if not authenticated[cite: 11]
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php?error=session');
    exit;
}

// Inactivity timeout of 2 hours[cite: 11, 19]
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time'] > 7200)) {
    session_destroy();
    header('Location: admin_login.php?error=session');
    exit;
}
$_SESSION['admin_login_time'] = time();