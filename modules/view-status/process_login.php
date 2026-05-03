<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../cryptograph_process.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$refNum = strtoupper(trim($_POST['reference_no'] ?? ''));

if ($refNum === '') {
    header("Location: login.php?error=" . urlencode("Please enter a valid reference number."));
    exit;
}

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) {
    header("Location: login.php?error=" . urlencode("Database connection failed."));
    exit;
}

// Fetch enrollee by reference_no
$stmt = $conn->prepare("
    SELECT ed.reference_no, e.enrollee_id, c.email
    FROM education ed
    INNER JOIN enrollee e ON ed.enrollee_id = e.enrollee_id
    INNER JOIN contacts c ON e.enrollee_id  = c.enrollee_id
    WHERE ed.reference_no = ?
");
$stmt->bind_param("s", $refNum);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: login.php?error=" . urlencode("Reference number not found."));
    $conn->close();
    exit;
}

// Generate OTP
$otp        = rand(100000, 999999);
$otp_expiry = time() + 300; // 5 minutes

// Delete any previous unused OTPs for this ref number
$stmt = $conn->prepare("DELETE FROM otp_tokens WHERE reference_no = ?");
$stmt->bind_param("s", $refNum);
$stmt->execute();
$stmt->close();

// Save new OTP
$stmt = $conn->prepare("
    INSERT INTO otp_tokens (reference_no, otp_code, otp_expiry)
    VALUES (?, ?, ?)
");
$stmt->bind_param("ssi", $refNum, $otp, $otp_expiry);
$stmt->execute();
$stmt->close();
$conn->close();

$_SESSION['temp_ref'] = $refNum;

// Send OTP email
$email = decryptData($user['email']);

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'sunnnotifier@gmail.com';
$mail->Password   = 'lvvg pymy ubfu xqvt';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;

$mail->setFrom('sunnnotifier@gmail.com', 'SUNN Enrollment System');
$mail->addAddress($email);

$mail->Subject = "Your SUNN OTP Code";
$mail->Body    = "Your OTP code is: $otp. It expires in 5 minutes.";

try {
    $mail->send();
    header("Location: otp.php");
    exit;
} catch (Exception $e) {
    echo "Email could not be sent. Error: " . $mail->ErrorInfo;
    exit;
}
?>