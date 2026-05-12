<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Renders a branded SUNN error page and exits.
 * @param string[] $errors  Array of human-readable error messages.
 * @param string   $type    'validation' | 'duplicate'
 */
function showErrorPage(array $errors, string $type = 'validation'): void
{
    $isDuplicate = ($type === 'duplicate');
    $title    = $isDuplicate ? 'Submission Failed'      : 'Invalid Submission';
    $subtitle = $isDuplicate
        ? 'We found existing records that conflict with your information.<br>Please review the details below.'
        : 'Some required fields are missing or invalid. Please review the errors below.';
    $iconClass = $isDuplicate ? 'ti-alert-circle' : 'ti-forms';

    /* Map duplicate keys to icon + field label */
    $fieldMeta = [
        'Full name already exists.'          => ['ti-user-off',  'Full Name'],
        'Email already exists.'              => ['ti-mail-off',  'Email Address'],
        'Phone number already exists.'       => ['ti-phone-off', 'Phone Number'],
    ];

    $items = '';
    foreach ($errors as $err) {
        if ($isDuplicate && isset($fieldMeta[$err])) {
            [$ico, $field] = $fieldMeta[$err];
            $items .= "
            <li>
              <div class='err-bullet'><i class='ti {$ico}' aria-hidden='true'></i></div>
              <div class='err-text'>
                <span class='err-field'>" . htmlspecialchars($field) . "</span>
                <span class='err-msg'>"  . htmlspecialchars($err)   . "</span>
              </div>
            </li>";
        } else {
            $items .= "
            <li>
              <div class='err-bullet'><i class='ti ti-x' aria-hidden='true'></i></div>
              <div class='err-text'>
                <span class='err-msg'>" . htmlspecialchars($err) . "</span>
              </div>
            </li>";
        }
    }

    $tip = $isDuplicate
        ? '<p>If you\'ve already submitted an application, use your <strong>reference number</strong> on the <strong>View Status</strong> page to check or update your information.</p>'
        : '<p>Please go back and fill in all required fields correctly before resubmitting.</p>';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submission Error – SUNN Enrollment</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    html,body{min-height:100%;font-family:'Poppins',sans-serif;}
    body{
      display:flex;justify-content:center;align-items:center;
      min-height:100vh;padding:40px 20px;
      background:linear-gradient(rgba(48,45,45,.55),rgba(98,93,93,.55)),
                 url('../../modules/styles/graphics/bg.jpg') no-repeat center center fixed;
      background-size:cover;
    }
    .card{
      background:#fff;max-width:640px;width:100%;
      border-radius:20px;overflow:hidden;
      border:5px solid #fbbf24;
    }
    .card-header{
      background:linear-gradient(rgba(15,20,40,.92),rgba(20,30,55,.96));
      padding:24px 24px 18px;text-align:center;
      border-bottom:3px solid #fbbf24;
    }
    .logo-circle{
      width:68px;height:68px;border-radius:50%;background:#fbbf24;
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 10px;
    }
    .logo-circle img{width:54px;height:54px;object-fit:contain;}
    .card-header h1{color:#fff;font-size:.95rem;font-weight:700;letter-spacing:.04em;margin:0 0 4px;}
    .tagline{color:#fbbf24;font-size:.7rem;text-transform:uppercase;letter-spacing:2px;font-weight:600;}
    .gold-bar{height:2px;background:#fbbf24;width:48px;margin:14px auto 0;border-radius:2px;}
    .card-body{padding:36px 40px 40px;}

    .err-icon-wrap{
      width:72px;height:72px;border-radius:50%;
      background:#fef2f2;border:2px solid #fecaca;
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 18px;font-size:2rem;color:#dc2626;
    }
    .err-title{font-size:1.4rem;font-weight:700;color:#b91c1c;text-align:center;margin:0 0 6px;}
    .err-sub{font-size:.87rem;color:#64748b;text-align:center;margin:0 0 26px;line-height:1.6;}

    .err-list{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:12px;}
    .err-list li{
      display:flex;align-items:flex-start;gap:12px;
      background:#fff5f5;border:1.5px solid #fecaca;
      border-radius:12px;padding:14px 16px;
    }
    .err-bullet{
      width:28px;height:28px;border-radius:50%;background:#fee2e2;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
      font-size:.9rem;color:#dc2626;
    }
    .err-text{display:flex;flex-direction:column;}
    .err-field{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#ef4444;margin-bottom:2px;}
    .err-msg{font-size:.87rem;color:#1e293b;font-weight:500;}

    .tip{
      background:#fffbeb;border:1.5px solid #fde68a;
      border-radius:12px;padding:14px 18px;
      display:flex;gap:10px;align-items:flex-start;margin-bottom:26px;
      font-size:.83rem;color:#92400e;line-height:1.6;
    }
    .tip-icon{flex-shrink:0;font-size:1.1rem;margin-top:1px;}

    .actions{display:flex;flex-direction:column;gap:10px;}
    .btn-back{
      display:block;width:100%;padding:15px;text-align:center;text-decoration:none;
      background:#3f9142;color:#fff;border:none;border-radius:12px;
      font-size:.95rem;font-weight:700;cursor:pointer;
      box-shadow:0 4px 0 #276429;transition:all .2s;
      text-transform:uppercase;letter-spacing:.05em;font-family:'Poppins',sans-serif;
    }
    .btn-back:hover{background:#2d6b30;transform:translateY(-1px);box-shadow:0 5px 0 #1d4821;}
    .btn-back:active{transform:translateY(3px);box-shadow:none;}
    .btn-home{
      display:block;width:100%;padding:12px;text-align:center;text-decoration:none;
      background:transparent;color:#64748b;border:1.5px solid #e2e8f0;
      border-radius:12px;font-size:.87rem;transition:all .2s;font-family:'Poppins',sans-serif;
    }
    .btn-home:hover{background:#f8fafc;color:#1e293b;border-color:#cbd5e1;}

    @media(max-width:480px){
      .card-body{padding:28px 20px 32px;}
      .err-title{font-size:1.2rem;}
    }
  </style>
</head>
<body>
<div class="card">

  <div class="card-header">
    <div class="logo-circle">
      <img src="logo.png" alt="SUNN Logo">
    </div>
    <h1>STATE UNIVERSITY OF NORTHERN NEGROS</h1>
    <div class="tagline">The Future Shines Brightest</div>
    <div class="gold-bar"></div>
  </div>

  <div class="card-body">
    <div class="err-icon-wrap" aria-hidden="true">⚠</div>
    <h2 class="err-title">{$title}</h2>
    <p class="err-sub">{$subtitle}</p>

    <ul class="err-list">{$items}</ul>

    <div class="tip">
      <span class="tip-icon">💡</span>
      {$tip}
    </div>

    <div class="actions">
      <a href="javascript:history.back()" class="btn-back">← Go Back &amp; Correct</a>
      <a href="../../index.php" class="btn-home">Return to Home</a>
    </div>
  </div>

</div>
</body>
</html>
HTML;
    exit;
}

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
        showErrorPage($errors, 'validation');
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
        showErrorPage(['Could not connect to the database. Please try again later.'], 'validation');
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
        showErrorPage(['The selected course does not exist. Please go back and choose a valid course.'], 'validation');
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
        $conn->close();
        showErrorPage($dup_errors, 'duplicate');
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
        showErrorPage(['Something went wrong while saving your application. Please try again.'], 'validation');
    }

    $conn->close();
}
?>