<?php
session_start();

if (empty($_SESSION['ref_no'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: status.php");
    exit;
}

require __DIR__ . '/../../cryptograph_process.php';

$ref_no          = $_SESSION['ref_no'];
$first_name      = htmlspecialchars(trim($_POST['first_name']       ?? ''));
$middle_name     = htmlspecialchars(trim($_POST['middle_name']      ?? ''));
$last_name       = htmlspecialchars(trim($_POST['last_name']        ?? ''));
$sex             = htmlspecialchars(trim($_POST['sex']              ?? ''));
$birthdate       = htmlspecialchars(trim($_POST['birthdate']        ?? ''));
$civil_status    = htmlspecialchars(trim($_POST['civil_status']     ?? ''));
$email           = htmlspecialchars(trim($_POST['email']            ?? ''));
$phone           = htmlspecialchars(trim($_POST['phone']            ?? ''));
$address         = htmlspecialchars(trim($_POST['address']          ?? ''));
$guardian_name   = htmlspecialchars(trim($_POST['guardian_name']    ?? ''));
$guardian_phone  = htmlspecialchars(trim($_POST['guardian_phone']   ?? ''));
$guardian_address= htmlspecialchars(trim($_POST['guardian_address'] ?? ''));
$year_level      = htmlspecialchars(trim($_POST['year_level']       ?? ''));
$previous_school = htmlspecialchars(trim($_POST['previous_school']  ?? ''));
$gpa             = htmlspecialchars(trim($_POST['gpa']              ?? ''));
$course_id       = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

// Validate
$errors = [];
if ($first_name      === '') $errors[] = "First name is required.";
if ($last_name       === '') $errors[] = "Last name is required.";
if ($sex             === '') $errors[] = "Sex is required.";
if ($birthdate       === '') $errors[] = "Date of birth is required.";
if ($civil_status    === '') $errors[] = "Civil status is required.";
if ($email           === '') $errors[] = "Email is required.";
if ($phone           === '') $errors[] = "Phone is required.";
if ($address         === '') $errors[] = "Address is required.";
if ($guardian_name   === '') $errors[] = "Guardian name is required.";
if ($guardian_phone  === '') $errors[] = "Guardian phone is required.";
if ($guardian_address=== '') $errors[] = "Guardian address is required.";
if ($year_level      === '') $errors[] = "Year level is required.";
if (!$course_id || $course_id <= 0) $errors[] = "Please select a course.";
if (!preg_match('/^09\d{9}$/', $phone))         $errors[] = "Invalid phone format.";
if (!preg_match('/^09\d{9}$/', $guardian_phone)) $errors[] = "Invalid guardian phone format.";

if (!empty($errors)) {
    foreach ($errors as $e) echo "<p style='color:red;'>$e</p>";
    echo "<a href='status.php'>Go back</a>";
    exit;
}

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) die("Connection failed.");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // ── Safety check — must still be Pending ──────────────────
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
        die("<p style='color:red;'>Record not found.</p>");
    }

    if ($current_status !== 'Pending') {
        die("<p style='color:red;'>Your application is $current_status and can no longer be modified.</p>");
    }

    // ── Encrypt ───────────────────────────────────────────────
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

    header("Location: status.php?updated=1");
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    echo "<p style='color:red;'>Update failed: " . $e->getMessage() . "</p>";
    echo "<a href='status.php'>Go back</a>";
}

$conn->close();
?>