<?php
require_once __DIR__ . '/auth_guard.php'; // Protect the script
require_once __DIR__ . '/../../cryptograph_process.php'; // Essential for decryptData()
require '../../vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollee_id'])) {
    $id = (int)$_POST['enrollee_id'];
    
    // 1. Establish Database Connection[cite: 7]
    $conn = new mysqli('localhost', 'root', '', 'sunn_enrollment');
    if ($conn->connect_error) {
        header("Location: admin_dashboard.php?notified=db_error");
        exit;
    }
    
    // 2. Fetch applicant's name, status, and encrypted email
    $stmt = $conn->prepare("SELECT e.first_name, e.status, c.email FROM enrollee e JOIN contacts c ON e.enrollee_id = c.enrollee_id WHERE e.enrollee_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        // 3. CRITICAL: Decrypt the data so PHPMailer uses valid text[cite: 7]
        // This converts strings like 'Ix8ERaYh...' into actual names and emails.
        $fname  = decryptData($data['first_name']); 
        $email  = decryptData($data['email']); 
        $status = $data['status'];

        // 4. Custom Message Logic based on Decision
        if ($status === 'accepted') {
            $subject = "Congratulations! Admission to SUNN";
            $message = "
                <div style='font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6;'>
                    <h2 style='color: #16a34a;'>Admission Accepted</h2>
                    <p>Dear <strong>$fname</strong>,</p>
                    <p>We are thrilled to inform you that your enrollment application at the <strong>State University of Northern Negros</strong> has been <strong>ACCEPTED</strong>.</p>
                    <p>Please proceed to the Admissions Office with your original documents to finalize your enrollment.</p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0;'>
                    <small>SUNN Enrollment System | Automated Notification</small>
                </div>";
        } else {
            $subject = "Update: Your Application Status";
            $message = "
                <div style='font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6;'>
                    <h2 style='color: #dc2626;'>Application Update</h2>
                    <p>Dear <strong>$fname</strong>,</p>
                    <p>Thank you for your interest in joining State University of Northern Negros. After a careful review, we regret to inform you that we are unable to offer you admission at this time.</p>
                    <p>We appreciate your interest and wish you the best in your academic pursuits.</p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0;'>
                    <small>SUNN Enrollment System | Automated Notification</small>
                </div>";
        }

        // 5. Initialize PHPMailer with system credentials
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sunnnotifier@gmail.com'; // Your sender account
            $mail->Password   = 'lvvg pymy ubfu xqvt'; // Your 16-character App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Updated: Set 'From' to match the authenticated Username for better delivery
            $mail->setFrom('sunnnotifier@gmail.com', 'SUNN Admissions');
            $mail->addAddress($email, $fname); // Uses the decrypted email address

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();

            // 6. Finalization: Mark as notified in DB[cite: 1]
            $update = $conn->prepare("UPDATE enrollee SET notified = 1 WHERE enrollee_id = ?");
            $update->bind_param("i", $id);
            $update->execute();
            $update->close();

            header("Location: admin_dashboard.php?notified=success");
        } catch (Exception $e) {
            // Temporary debug line if it still fails:
            // die("Mailer Error: " . $mail->ErrorInfo); 
            header("Location: admin_dashboard.php?notified=error");
        }
    } else {
        header("Location: admin_dashboard.php?notified=not_found");
    }
    
    $stmt->close();
    $conn->close();
    exit;
}