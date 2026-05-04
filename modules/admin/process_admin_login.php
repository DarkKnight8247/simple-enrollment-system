<?php
session_start();
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', password_hash('admin123', PASSWORD_BCRYPT));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    // Check for lockout[cite: 12]
    if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
        header("Location: admin_rejected.php?reason=locked");
        exit;
    }

    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['login_attempts'] = 0;
        header('Location: admin_dashboard.php');
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 2) {
            $_SESSION['lockout_until'] = time() + 900; // 15-minute lock
        }
        header('Location: admin_rejected.php?reason=invalid'); // "Reject wrong pass"[cite: 18, 20]
    }
    exit;
}