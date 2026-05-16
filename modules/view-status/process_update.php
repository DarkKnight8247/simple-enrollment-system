<?php
session_start();

if (empty($_SESSION['ref_no'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: view_dashboard.php");
    exit;
}

require __DIR__ . '/../../cryptograph_process.php';

/**
 * Renders a branded SUNN error page for update failures and exits.
 * @param string $message  HTML-safe message to display.
 * @param string $type     'not_found' | 'locked' | 'db_error'
 */
function showUpdateErrorPage(string $message, string $type = 'db_error'): void
{
    $icons = [
        'not_found' => '🔍',
        'locked'    => '🔒',
        'db_error'  => '⚠',
    ];
    $icon = $icons[$type] ?? '⚠';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="theme-color" content="#fbbf24">
  <title>Update Error – SUNN Enrollment</title>
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
    .card{background:#fff;max-width:600px;width:100%;border-radius:20px;overflow:hidden;border:5px solid #fbbf24;box-shadow:0 28px 70px rgba(74,63,63,.55);}
    .card-header{
      background:linear-gradient(rgba(54,49,49,.82),rgba(112,104,104,.95)),
                 url('sunn_bg.jpg') no-repeat center 30% / cover;
      padding:22px 20px 18px;text-align:center;border-bottom:3px solid #fbbf24;
    }
    .logo{display:block;width:clamp(60px,18vw,90px);height:auto;margin:0 auto 10px;object-fit:contain;}
    .card-header h1{color:#fff;font-size:clamp(.72rem,3.2vw,1rem);font-weight:700;letter-spacing:.04em;margin:0 0 4px;word-break:break-word;}
    .tagline{color:#fbbf24;font-size:clamp(.6rem,2.4vw,.75rem);text-transform:uppercase;letter-spacing:2px;font-weight:600;}
    .gold-bar{height:3px;background:#fbbf24;width:60px;margin:12px auto 0;border-radius:2px;}
    .card-body{padding:36px 32px 40px;text-align:center;}
    .err-icon-wrap{width:72px;height:72px;border-radius:50%;background:#fef2f2;border:2px solid #fecaca;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:2rem;color:#dc2626;}
    .err-title{font-size:1.4rem;font-weight:700;color:#b91c1c;margin:0 0 12px;}
    .err-msg{font-size:.9rem;color:#334155;line-height:1.7;margin:0 0 28px;}
    .tip{background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 18px;display:flex;gap:10px;align-items:flex-start;margin-bottom:28px;text-align:left;font-size:.83rem;color:#92400e;line-height:1.6;}
    .tip-icon{flex-shrink:0;font-size:1.1rem;}
    .actions{display:flex;flex-direction:column;gap:10px;}
    .btn-back{display:block;width:100%;padding:15px;text-align:center;text-decoration:none;background:#3f9142;color:#fff;border:none;border-radius:12px;font-size:.95rem;font-weight:700;cursor:pointer;box-shadow:0 4px 0 #276429;transition:all .2s;text-transform:uppercase;letter-spacing:.05em;font-family:'Poppins',sans-serif;}
    .btn-back:hover{background:#2d6b30;transform:translateY(-1px);box-shadow:0 5px 0 #1d4821;}
    .btn-back:active{transform:translateY(3px);box-shadow:none;}
    .btn-home{display:block;width:100%;padding:12px;text-align:center;text-decoration:none;background:transparent;color:#64748b;border:1.5px solid #e2e8f0;border-radius:12px;font-size:.87rem;transition:all .2s;font-family:'Poppins',sans-serif;}
    .btn-home:hover{background:#f8fafc;color:#1e293b;border-color:#cbd5e1;}
    @media(min-width:481px){.card-body{padding:40px 48px 44px;}}
    @media(max-width:359px){.card-body{padding:28px 18px 32px;}}
  </style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <img src="logo.png" alt="SUNN Logo" class="logo">
    <h1>STATE UNIVERSITY OF NORTHERN NEGROS</h1>
    <div class="tagline">The Future Shines Brightest</div>
    <div class="gold-bar"></div>
  </div>
  <div class="card-body">
    <div class="err-icon-wrap" aria-hidden="true">{$icon}</div>
    <h2 class="err-title">Update Failed</h2>
    <p class="err-msg">{$message}</p>
    <div class="tip">
      <span class="tip-icon">💡</span>
      <span>If you believe this is an error, try logging in again with your reference number or contact the admissions office.</span>
    </div>
    <div class="actions">
      <a href="javascript:history.back()" class="btn-back">← Go Back</a>
      <a href="../../index.php" class="btn-home">Return to Home</a>
    </div>
  </div>
</div>
</body>
</html>
HTML;
    exit;
}



$ref_no           = $_SESSION['ref_no'];
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
$course_id        = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

// Validate
$errors = [];
if ($first_name       === '') $errors[] = "First name is required.";
if ($last_name        === '') $errors[] = "Last name is required.";
if ($sex              === '') $errors[] = "Sex is required.";
if ($birthdate        === '') $errors[] = "Date of birth is required.";
if ($civil_status     === '') $errors[] = "Civil status is required.";
if ($email            === '') $errors[] = "Email is required.";
if ($phone            === '') $errors[] = "Phone is required.";
if ($address          === '') $errors[] = "Address is required.";
if ($guardian_name    === '') $errors[] = "Guardian name is required.";
if ($guardian_phone   === '') $errors[] = "Guardian phone is required.";
if ($guardian_address === '') $errors[] = "Guardian address is required.";
if ($year_level       === '') $errors[] = "Year level is required.";
if (!$course_id || $course_id <= 0)          $errors[] = "Please select a course.";
if (!preg_match('/^09\d{9}$/', $phone))      $errors[] = "Invalid phone format. Use 09XXXXXXXXX.";
if (!preg_match('/^09\d{9}$/', $guardian_phone)) $errors[] = "Invalid guardian phone format.";

if (!empty($errors)) {
    $items = '';
    foreach ($errors as $e) {
        $items .= "<li>
          <div class='err-bullet'><i>✕</i></div>
          <div class='err-text'><span class='err-msg'>" . htmlspecialchars($e) . "</span></div>
        </li>";
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="theme-color" content="#fbbf24">
  <title>Update Error – SUNN Enrollment</title>
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
    .card{background:#fff;max-width:640px;width:100%;border-radius:20px;overflow:hidden;border:5px solid #fbbf24;}
    .card-header{
      background:linear-gradient(rgba(54,49,49,.82),rgba(112,104,104,.95)),
                 url('sunn_bg.jpg') no-repeat center 30% / cover;
      padding:22px 20px 18px;text-align:center;border-bottom:3px solid #fbbf24;
    }
    .logo{display:block;width:clamp(60px,18vw,90px);height:auto;margin:0 auto 10px;object-fit:contain;}
    .card-header h1{color:#fff;font-size:clamp(.72rem,3.2vw,1rem);font-weight:700;letter-spacing:.04em;margin:0 0 4px;word-break:break-word;}
    .tagline{color:#fbbf24;font-size:clamp(.6rem,2.4vw,.75rem);text-transform:uppercase;letter-spacing:2px;font-weight:600;}
    .gold-bar{height:3px;background:#fbbf24;width:60px;margin:12px auto 0;border-radius:2px;}
    .card-body{padding:36px 32px 40px;}
    .err-icon-wrap{width:72px;height:72px;border-radius:50%;background:#fef2f2;border:2px solid #fecaca;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:2rem;color:#dc2626;}
    .err-title{font-size:1.4rem;font-weight:700;color:#b91c1c;text-align:center;margin:0 0 6px;}
    .err-sub{font-size:.87rem;color:#64748b;text-align:center;margin:0 0 26px;line-height:1.6;}
    .err-list{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:12px;}
    .err-list li{display:flex;align-items:flex-start;gap:12px;background:#fff5f5;border:1.5px solid #fecaca;border-radius:12px;padding:14px 16px;}
    .err-bullet{width:28px;height:28px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;color:#dc2626;font-weight:700;}
    .err-text{display:flex;flex-direction:column;}
    .err-msg{font-size:.87rem;color:#1e293b;font-weight:500;}
    .tip{background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 18px;display:flex;gap:10px;align-items:flex-start;margin-bottom:26px;font-size:.83rem;color:#92400e;line-height:1.6;}
    .tip-icon{flex-shrink:0;font-size:1.1rem;margin-top:1px;}
    .actions{display:flex;flex-direction:column;gap:10px;}
    .btn-back{display:block;width:100%;padding:15px;text-align:center;text-decoration:none;background:#3f9142;color:#fff;border:none;border-radius:12px;font-size:.95rem;font-weight:700;cursor:pointer;box-shadow:0 4px 0 #276429;transition:all .2s;text-transform:uppercase;letter-spacing:.05em;font-family:'Poppins',sans-serif;}
    .btn-back:hover{background:#2d6b30;transform:translateY(-1px);box-shadow:0 5px 0 #1d4821;}
    .btn-back:active{transform:translateY(3px);box-shadow:none;}
    .btn-home{display:block;width:100%;padding:12px;text-align:center;text-decoration:none;background:transparent;color:#64748b;border:1.5px solid #e2e8f0;border-radius:12px;font-size:.87rem;transition:all .2s;font-family:'Poppins',sans-serif;}
    .btn-home:hover{background:#f8fafc;color:#1e293b;border-color:#cbd5e1;}
    @media(min-width:481px){.card-body{padding:40px 48px 44px;}}
    @media(max-width:359px){.card-body{padding:28px 18px 32px;}.err-title{font-size:1.2rem;}}
  </style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <img src="logo.png" alt="SUNN Logo" class="logo">
    <h1>STATE UNIVERSITY OF NORTHERN NEGROS</h1>
    <div class="tagline">The Future Shines Brightest</div>
    <div class="gold-bar"></div>
  </div>
  <div class="card-body">
    <div class="err-icon-wrap" aria-hidden="true">⚠</div>
    <h2 class="err-title">Update Failed</h2>
    <p class="err-sub">Some required fields are missing or invalid. Please review the errors below.</p>
    <ul class="err-list">{$items}</ul>
    <div class="tip">
      <span class="tip-icon">💡</span>
      <p>Please go back and correct the highlighted fields before resubmitting.</p>
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

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) die("Connection failed.");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Safety check — must still be Pending
    $stmt = $conn->prepare("
        SELECT ed.enrollee_id, ed.status
        FROM education ed
        WHERE ed.reference_no = ?
    ");
    $stmt->bind_param("s", $ref_no);
    $stmt->execute();
    $stmt->bind_result($enrollee_id, $current_status);
    $stmt->fetch();
    $stmt->close();

    if (!$enrollee_id) {
        showUpdateErrorPage("Record not found. Your session may have expired.", "not_found");
    }

    if (strtolower($current_status) !== 'pending') {
        showUpdateErrorPage("Your application is <strong>" . htmlspecialchars($current_status) . "</strong> and can no longer be modified.", "locked");
    }

    // Encrypt
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

    $conn->begin_transaction();

    // Update enrollee
    $stmt = $conn->prepare("
        UPDATE enrollee
        SET first_name = ?, last_name = ?, middle_name = ?,
            sex = ?, birthdate = ?, civil_status = ?
        WHERE enrollee_id = ?
    ");
    $stmt->bind_param("ssssssi",
        $enc_first_name, $enc_last_name, $enc_middle_name,
        $enc_sex, $enc_birthdate, $enc_civil_status,
        $enrollee_id
    );
    $stmt->execute();
    $stmt->close();

    // Update contacts
    $stmt = $conn->prepare("
        UPDATE contacts
        SET email = ?, phone_number = ?, address = ?
        WHERE enrollee_id = ?
    ");
    $stmt->bind_param("sssi", $enc_email, $enc_phone, $enc_address, $enrollee_id);
    $stmt->execute();
    $stmt->close();

    // Update emergency_contacts
    $stmt = $conn->prepare("
        UPDATE emergency_contacts
        SET guardian_name = ?, phone_number = ?, address = ?
        WHERE enrollee_id = ?
    ");
    $stmt->bind_param("sssi",
        $enc_guardian_name, $enc_guardian_phone, $enc_guardian_address,
        $enrollee_id
    );
    $stmt->execute();
    $stmt->close();

    // Update education
    $stmt = $conn->prepare("
        UPDATE education
        SET course_id = ?, year_level = ?, previous_school = ?, gpa = ?
        WHERE reference_no = ?
    ");
    $stmt->bind_param("issss",
        $course_id, $enc_year_level, $enc_previous_school, $enc_gpa,
        $ref_no
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header("Location: view_dashboard.php?updated=1");
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    showUpdateErrorPage("Something went wrong while saving your changes. Please try again.", "db_error");
}

$conn->close();
?>
