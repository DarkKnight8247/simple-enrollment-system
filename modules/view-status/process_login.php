<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../cryptograph_process.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$refNum = strtoupper(trim($_POST['reference_no'] ?? ''));

if ($refNum === '') {
    header("Location: index.php?error=" . urlencode("Please enter a valid reference number."));
    exit;
}

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) {
    header("Location: index.php?error=" . urlencode("Database connection failed."));
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
    header("Location: index.php?error=" . urlencode("Reference number not found. Please check and try again."));
    $conn->close();
    exit;
}

// Generate OTP
$otp        = rand(100000, 999999);
$otp_expiry = time() + 300; // 5 minutes

// Delete any previous OTPs for this ref number
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

// Decrypt email and send OTP
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
$mail->isHTML(true);
$mail->Subject = 'Your SUNN OTP Code';
$mail->Body    = "
    <div style='font-family:sans-serif; max-width:480px; margin:0 auto;'>
        <div style='background:#0b1f3a; padding:24px; text-align:center;'>
            <h2 style='color:#c9923e; margin:0; font-size:20px;'>SUNN Enrollment System</h2>
        </div>
        <div style='padding:32px; background:#faf8f4;'>
            <p style='color:#142d52; font-size:15px;'>Hello!</p>
            <p style='color:#6b7d96; font-size:14px;'>Your one-time password (OTP) to view your enrollment status is:</p>
            <div style='background:#fff; border:2px solid #c9923e; border-radius:8px;
                        text-align:center; padding:20px; margin:20px 0;'>
                <span style='font-size:36px; font-weight:700; color:#0b1f3a;
                             letter-spacing:0.3em; font-family:monospace;'>{$otp}</span>
            </div>
            <p style='color:#6b7d96; font-size:13px;'>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
        </div>
        <div style='background:#0b1f3a; padding:16px; text-align:center;'>
            <p style='color:#6b7d96; font-size:12px; margin:0;'>&copy; " . date('Y') . " SUNN Enrollment System</p>
        </div>
    </div>
";

try {
    $mail->send();
    header("Location: otp.php");
    exit;
} catch (Exception $e) {
    echo "Email could not be sent. Error: " . $mail->ErrorInfo;
    exit;
}
?>
