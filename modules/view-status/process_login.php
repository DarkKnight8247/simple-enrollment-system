<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../cryptograph_process.php';

$refNum = strtoupper(trim($_POST['refNum'] ?? ''));

if ($refNum === '') {
    header("Location: login.php?error=" . urlencode("Please enter a valid reference number."));
    exit;
}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) {
    header("Location: login.php?error=" . urlencode("Database connection failed."));
    exit;
}

// ─── Look up by reference_no (string), not enrollee_id ─────
$stmt = $conn->prepare("
    SELECT e.enrollee_id, c.email
    FROM education ed
    INNER JOIN enrollee e  ON ed.enrollee_id = e.enrollee_id
    INNER JOIN contacts c  ON e.enrollee_id  = c.enrollee_id
    WHERE ed.reference_no = ?
");
$stmt->bind_param("s", $refNum);
$stmt->execute();
$stmt->bind_result($enrollee_id, $enc_email);
$found = $stmt->fetch();
$stmt->close();
$conn->close();

if (!$found) {
    header("Location: login.php?error=" . urlencode("Reference number not found. Please check and try again."));
    exit;
}

// Decrypt email
$email = decryptData($enc_email);

// Generate a 6-digit OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store in session
$_SESSION['otp_code']        = $otp;
$_SESSION['otp_expires']     = time() + 600;
$_SESSION['otp_enrollee_id'] = $enrollee_id;
$_SESSION['otp_email']       = $email;
$_SESSION['otp_ref']         = $refNum;

// Send OTP via PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sunnnotifier@gmail.com';
    $mail->Password   = 'lvvg pymy ubfu xqvt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('sunnnotifier@gmail.com', 'SUNN Enrollment System');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your SUNN Enrollment OTP';
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
                <p style='color:#6b7d96; font-size:13px;'>This OTP is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>
            </div>
            <div style='background:#0b1f3a; padding:16px; text-align:center;'>
                <p style='color:#6b7d96; font-size:12px; margin:0;'>
                    &copy; " . date('Y') . " SUNN Enrollment System
                </p>
            </div>
        </div>
    ";

    $mail->send();
    header("Location: otp.php");
    exit;

} catch (Exception $e) {
    header("Location: otp.php?dev=1");
    exit;
}
?>