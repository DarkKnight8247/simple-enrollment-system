<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: otp.php");
    exit;
}

$inputOtp = trim($_POST['otp'] ?? '');
$refNum   = $_SESSION['temp_ref'] ?? '';

if ($refNum === '' || $inputOtp === '') {
    header("Location: login.php?error=" . urlencode("Session expired. Please login again."));
    exit;
}

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) {
    header("Location: otp.php?error=" . urlencode("Database connection failed."));
    exit;
}

$stmt = $conn->prepare("
    SELECT id, otp_expiry FROM otp_tokens
    WHERE reference_no = ?
      AND otp_code     = ?
      AND used         = 0
    LIMIT 1
");
$stmt->bind_param("ss", $refNum, $inputOtp);
$stmt->execute();
$result = $stmt->get_result();
$token  = $result->fetch_assoc();
$stmt->close();

if (!$token) {
    header("Location: otp.php?error=" . urlencode("Invalid OTP. Please try again."));
    $conn->close();
    exit;
}

if (time() > $token['otp_expiry']) {
    header("Location: otp.php?error=" . urlencode("OTP has expired. Please request a new one."));
    $conn->close();
    exit;
}

// Mark OTP as used
$stmt = $conn->prepare("UPDATE otp_tokens SET used = 1 WHERE id = ?");
$stmt->bind_param("i", $token['id']);
$stmt->execute();
$stmt->close();
$conn->close();

// Set session and redirect
unset($_SESSION['temp_ref']);
$_SESSION['ref_no'] = $refNum;
header("Location: view_dashboard.php");
exit;
?>