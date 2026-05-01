<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['otp_code']) || empty($_SESSION['otp_expires'])) {
    header("Location: login.php?error=" . urlencode("Session expired. Please start again."));
    exit;
}

// Check expiry
if (time() > $_SESSION['otp_expires']) {
    unset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_enrollee_id'], $_SESSION['otp_email']);
    header("Location: login.php?error=" . urlencode("OTP has expired. Please request a new one."));
    exit;
}

$submitted_otp = trim($_POST['otp'] ?? '');

if ($submitted_otp !== $_SESSION['otp_code']) {
    header("Location: otp.php?error=" . urlencode("Incorrect OTP. Please try again."));
    exit;
}

// OTP is valid — mark as verified
$_SESSION['verified_enrollee_id'] = $_SESSION['otp_enrollee_id'];

// Clean up OTP session data
unset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_enrollee_id'], $_SESSION['otp_email']);

header("Location: view.php");
exit;
?>
