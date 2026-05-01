<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require __DIR__ . '/../../cryptograph_process.php';

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

    if ($first_name   === '') $errors[] = "First name is required.";
    if ($last_name    === '') $errors[] = "Last name is required.";
    if ($sex          === '') $errors[] = "Sex is required.";
    if ($birthdate    === '') $errors[] = "Date of birth is required.";
    if ($civil_status === '') $errors[] = "Civil status is required.";
    if ($email        === '') $errors[] = "Email is required.";
    if ($phone        === '') $errors[] = "Phone number is required.";
    if ($address      === '') $errors[] = "Address is required.";
    if ($guardian_name    === '') $errors[] = "Guardian name is required.";
    if ($guardian_phone   === '') $errors[] = "Guardian phone is required.";
    if ($guardian_address === '') $errors[] = "Guardian address is required.";
    if ($year_level   === '') $errors[] = "Year level is required.";

    // course_id — must be a valid positive integer
    if (empty($_POST['course_id']) || (int)$_POST['course_id'] <= 0) {
        $errors[] = "Please select a course.";
    } else {
        $course_id = (int) $_POST['course_id'];
    }

    // Phone format
    if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = "Invalid phone number. Use format: 09XXXXXXXXX";
    }

    // Guardian phone format
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

    // Throw exceptions on mysqli errors so try/catch works correctly
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // ─── 5. Verify course_id actually exists in course table ───
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
    // Full name (only among already-enrolled students)
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

    // Email
    $stmt = $conn->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
    $stmt->bind_param("s", $enc_email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    $stmt->close();

    // Phone
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

    // ─── 8. Insert (all-or-nothing transaction) ─────────────────
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

        // education — course_id is a verified INT at this point, safe to use "ii"
        $stmt = $conn->prepare("
            INSERT INTO education (enrollee_id, course_id, year_level, previous_school, gpa)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss",
            $enrollee_id, $course_id,
            $enc_year_level, $enc_previous_school, $enc_gpa
        );
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "Enrollment successful! <br><a href='.././index.php'>Go back to Home</a>";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "<p style='color:red;'>Something went wrong. Please try again.<br>Details: " . $e->getMessage() . "</p>";
    }

    $conn->close();
}
?>