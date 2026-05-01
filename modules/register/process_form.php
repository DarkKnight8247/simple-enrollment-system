<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require __DIR__ . '/../../cryptograph_process.php';
    require __DIR__ . '/../../vendor/autoload.php';

    // ─── 1. Sanitize ───────────────────────────────────────────
    $first_name       = htmlspecialchars(trim($_POST['first_name']       ?? ''));
    $middle_name      = htmlspecialchars(trim($_POST['middle_name']      ?? ''));
    $last_name        = htmlspecialchars(trim($_POST['last_name']        ?? ''));
    $sex              = htmlspecialchars(trim($_POST['sex']              ?? ''));
    $birthdate        = htmlspecialchars(trim($_POST['birthdate']        ?? ''));
    $civil_status     = htmlspecialchars(trim($_POST['civil_status']     ?? ''));
    $email            = htmlspecialchars(trim($_POST['email']            ?? ''));
    $phone            = htmlspecialchars(trim($_POST['phone']            ?? ''));
    $address          = htmlspecialchars(trim($_POST['address']          ?? ''));
    $guardian_name    = htmlspecialchars(trim($_POST['guardian_name']    ?? ''));
    $guardian_phone   = htmlspecialchars(trim($_POST['guardian_phone']   ?? ''));
    $guardian_address = htmlspecialchars(trim($_POST['guardian_address'] ?? ''));
    $year_level       = htmlspecialchars(trim($_POST['year_level']       ?? ''));
    $previous_school  = htmlspecialchars(trim($_POST['previous_school']  ?? ''));
    $gpa              = htmlspecialchars(trim($_POST['gpa']              ?? ''));

    // ─── 2. Required field validation ──────────────────────────
    $errors = [];

    if ($first_name       === '') $errors[] = "First name is required.";
    if ($last_name        === '') $errors[] = "Last name is required.";
    if ($sex              === '') $errors[] = "Sex is required.";
    if ($birthdate        === '') $errors[] = "Date of birth is required.";
    if ($civil_status     === '') $errors[] = "Civil status is required.";
    if ($email            === '') $errors[] = "Email is required.";
    if ($phone            === '') $errors[] = "Phone number is required.";
    if ($address          === '') $errors[] = "Address is required.";
    if ($guardian_name    === '') $errors[] = "Guardian name is required.";
    if ($guardian_phone   === '') $errors[] = "Guardian phone is required.";
    if ($guardian_address === '') $errors[] = "Guardian address is required.";
    if ($year_level       === '') $errors[] = "Year level is required.";

    if (empty($_POST['course_id']) || (int)$_POST['course_id'] <= 0) {
        $errors[] = "Please select a course.";
    } else {
        $course_id = (int) $_POST['course_id'];
    }

    if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = "Invalid phone number. Use format: 09XXXXXXXXX";
    }

    if ($guardian_phone !== '' && !preg_match('/^09\d{9}$/', $guardian_phone)) {
        $errors[] = "Invalid guardian phone number. Use format: 09XXXXXXXXX";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        exit;
    }

    // ─── 3. Encrypt ────────────────────────────────────────────
    $enc_first_name       = encryptData($first_name);
    $enc_middle_name      = encryptData($middle_name);
    $enc_last_name        = encryptData($last_name);
    $enc_sex              = encryptData($sex);
    $enc_birthdate        = encryptData($birthdate);
    $enc_civil_status     = encryptData($civil_status);
    $enc_email            = encryptData($email);
    $enc_phone            = encryptData($phone);
    $enc_address          = encryptData($address);
    $enc_guardian_name    = encryptData($guardian_name);
    $enc_guardian_phone   = encryptData($guardian_phone);
    $enc_guardian_address = encryptData($guardian_address);
    $enc_year_level       = encryptData($year_level);
    $enc_previous_school  = encryptData($previous_school);
    $enc_gpa              = encryptData($gpa);

    // ─── 4. Connect ────────────────────────────────────────────
    $conn = new mysqli("localhost", "root", "", "sunn_enrollment");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // ─── 5. Verify course_id exists ────────────────────────────
    $stmt = $conn->prepare("SELECT COUNT(*) FROM course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($course_exists);
    $stmt->fetch();
    $stmt->close();

    if ($course_exists === 0) {
        $conn->close();
        die("<p style='color:red;'>Selected course does not exist. Please go back and try again.</p>");
    }

    // ─── 6. Duplicate checks ───────────────────────────────────
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM enrollee e
        INNER JOIN education ed ON e.enrollee_id = ed.enrollee_id
        WHERE e.first_name = ? AND e.middle_name = ? AND e.last_name = ?
    ");
    $stmt->bind_param("sss", $enc_first_name, $enc_middle_name, $enc_last_name);
    $stmt->execute();
    $stmt->bind_result($name_count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
    $stmt->bind_param("s", $enc_email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM contacts WHERE phone_number = ?");
    $stmt->bind_param("s", $enc_phone);
    $stmt->execute();
    $stmt->bind_result($phone_count);
    $stmt->fetch();
    $stmt->close();

    // ─── 7. Duplicate results ──────────────────────────────────
    $dup_errors = [];
    if ($name_count  > 0) $dup_errors[] = "Full name already exists.";
    if ($email_count > 0) $dup_errors[] = "Email already exists.";
    if ($phone_count > 0) $dup_errors[] = "Phone number already exists.";

    if (!empty($dup_errors)) {
        foreach ($dup_errors as $err) {
            echo "<p style='color:red;'>$err</p>";
        }
        $conn->close();
        exit;
    }

    // ─── 8. Insert (all-or-nothing transaction) ────────────────
    try {
        $conn->begin_transaction();

        // enrollee
        $stmt = $conn->prepare("
            INSERT INTO enrollee (first_name, last_name, middle_name, sex, birthdate, civil_status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss",
            $enc_first_name, $enc_last_name, $enc_middle_name,
            $enc_sex, $enc_birthdate, $enc_civil_status
        );
        $stmt->execute();
        $enrollee_id = $stmt->insert_id;
        $stmt->close();

        // contacts
        $stmt = $conn->prepare("
            INSERT INTO contacts (enrollee_id, email, phone_number, address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $enrollee_id, $enc_email, $enc_phone, $enc_address);
        $stmt->execute();
        $stmt->close();

        // emergency_contacts
        $stmt = $conn->prepare("
            INSERT INTO emergency_contacts (enrollee_id, guardian_name, phone_number, address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss",
            $enrollee_id, $enc_guardian_name, $enc_guardian_phone, $enc_guardian_address
        );
        $stmt->execute();
        $stmt->close();

        // Generate reference number
        $reference_no = strtoupper(substr(md5(uniqid()), 0, 8));

        // education
        $stmt = $conn->prepare("
            INSERT INTO education (enrollee_id, course_id, reference_no, year_level, previous_school, gpa)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissss",
            $enrollee_id, $course_id,
            $reference_no, $enc_year_level, $enc_previous_school, $enc_gpa
        );
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        // ─── 9. Send confirmation email ────────────────────────
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
            $mail->Subject = 'SUNN Enrollment – Application Received';
            $mail->Body    = "
                <div style='font-family:sans-serif; max-width:520px; margin:0 auto;'>
                    <div style='background:#0b1f3a; padding:28px 32px; text-align:center;'>
                        <h2 style='color:#c9923e; margin:0; font-size:22px; letter-spacing:0.04em;'>
                            SUNN Enrollment System
                        </h2>
                        <p style='color:#6b7d96; font-size:12px; margin:8px 0 0;'>
                            State University of Northern Negros
                        </p>
                    </div>
                    <div style='padding:36px 32px; background:#faf8f4;'>
                        <p style='color:#142d52; font-size:16px; font-weight:600; margin-top:0;'>
                            Hello, {$first_name}!
                        </p>
                        <p style='color:#4b5563; font-size:14px; line-height:1.7;'>
                            You have successfully filled out the SUNN enrollment form.
                            Please keep your reference number safe — you will need it to
                            track and manage your application.
                        </p>
                        <div style='background:#fff; border:2px solid #c9923e; border-radius:10px;
                                    text-align:center; padding:24px; margin:24px 0;'>
                            <p style='color:#6b7d96; font-size:12px; text-transform:uppercase;
                                       letter-spacing:0.1em; margin:0 0 8px;'>Your Reference Number</p>
                            <span style='font-size:28px; font-weight:700; color:#0b1f3a;
                                         letter-spacing:0.25em; font-family:monospace;'>
                                {$reference_no}
                            </span>
                        </div>
                        <p style='color:#4b5563; font-size:14px; line-height:1.7;'>
                            Please wait for further instructions from our admissions team.
                            We will notify you once your application has been reviewed.
                        </p>
                        <div style='background:#fffbeb; border-left:4px solid #c9923e;
                                    border-radius:6px; padding:16px 20px; margin:24px 0;'>
                            <p style='color:#92400e; font-size:13px; margin:0; line-height:1.6;'>
                                💡 <strong>In the meantime</strong>, you may edit your application
                                details while your status is still <strong>Pending</strong> by visiting
                                the <strong>View Status</strong> page and entering your reference number.
                            </p>
                        </div>
                        <p style='color:#9ca3af; font-size:12px; line-height:1.6; margin-bottom:0;'>
                            If you did not submit this application, please disregard this email
                            or contact us immediately.
                        </p>
                    </div>
                    <div style='background:#0b1f3a; padding:18px 32px; text-align:center;'>
                        <p style='color:#4b5a6e; font-size:12px; margin:0;'>
                            &copy; " . date('Y') . " SUNN Enrollment System &nbsp;·&nbsp; This is an automated message.
                        </p>
                    </div>
                </div>
            ";

            $mail->send();

        } catch (Exception $e) {
            // Email failure does NOT roll back the enrollment
            error_log("Enrollment email failed for {$email}: " . $e->getMessage());
        }

        // ─── 10. Success message ───────────────────────────────
        echo "
            <p>Enrollment submitted successfully!</p>
            <p>Your reference number is: <strong>{$reference_no}</strong></p>
            <p>A confirmation email has been sent to your registered email address.</p>
            <br>
            <a href='../../index.php'>Go back to Home</a>
        ";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "<p style='color:red;'>Something went wrong. Please try again.<br>Details: " . $e->getMessage() . "</p>";
    }

    $conn->close();
}
?>