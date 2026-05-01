<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'cryptograph_process.php';

    // sanitize natin yan
    $first_name      = htmlspecialchars(trim($_POST['first_name']      ?? ''));
    $middle_name     = htmlspecialchars(trim($_POST['middle_name']     ?? ''));
    $last_name       = htmlspecialchars(trim($_POST['last_name']       ?? ''));
    $sex             = htmlspecialchars(trim($_POST['sex']             ?? ''));
    $birthdate       = htmlspecialchars(trim($_POST['birthdate']       ?? ''));
    $civil_status    = htmlspecialchars(trim($_POST['civil_status']    ?? ''));
    $email           = htmlspecialchars(trim($_POST['email']           ?? ''));
    $phone           = htmlspecialchars(trim($_POST['phone']           ?? ''));
    $address         = htmlspecialchars(trim($_POST['address']         ?? ''));
    $guardian_name   = htmlspecialchars(trim($_POST['guardian_name']   ?? ''));
    $guardian_phone  = htmlspecialchars(trim($_POST['guardian_phone']  ?? ''));
    $guardian_address= htmlspecialchars(trim($_POST['guardian_address']?? ''));
    $course_id       = (int) ($_POST['course_id'] ?? 0);
    $year_level      = htmlspecialchars(trim($_POST['year_level']      ?? ''));
    $previous_school = htmlspecialchars(trim($_POST['previous_school'] ?? ''));
    $gpa             = htmlspecialchars(trim($_POST['gpa']             ?? ''));

    // valdate ang phone ulolz
    if (!preg_match('/^09\d{9}$/', $phone)) {
        die("Invalid phone number! Use format: 09XXXXXXXXX");
    }

    // Encrypt danay
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

    // connect to DB goiz
    $conn = new mysqli("localhost", "root", "", "sunn_enrollment");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // checking area

    // For full name checking
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

    // para sa email checking
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM contacts c
        INNER JOIN enrollee e ON c.enrollee_id = e.enrollee_id
        WHERE c.email = ?
    ");
    $stmt->bind_param("s", $enc_email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    $stmt->close();

    // for phone number checker
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM contacts c
        INNER JOIN enrollee e ON c.enrollee_id = e.enrollee_id
        WHERE c.phone_number = ?
    ");
    $stmt->bind_param("s", $enc_phone);
    $stmt->execute();
    $stmt->bind_result($phone_count);
    $stmt->fetch();
    $stmt->close();

    // validation res
    if ($name_count > 0) {
        echo "Full name already exists. Please use a different name.";
    } elseif ($email_count > 0) {
        echo "Email already exists. Please use a different email.";
    } elseif ($phone_count > 0) {
        echo "Phone number already exists. Please use a different phone number.";
    } else {

        // insert to tables

        // 1. enrollee
        $stmt = $conn->prepare("
            INSERT INTO enrollee (first_name, last_name, middle_name, sex, birthdate, civil_status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss",
            $enc_first_name, $enc_last_name, $enc_middle_name,
            $enc_sex, $enc_birthdate, $enc_civil_status
        );
        $stmt->execute();
        $enrollee_id = $stmt->insert_id;   // ← used by all child tables
        $stmt->close();

        // 2. contacts
        $stmt = $conn->prepare("
            INSERT INTO contacts (enrollee_id, email, phone_number, address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss",
            $enrollee_id, $enc_email, $enc_phone, $enc_address
        );
        $stmt->execute();
        $stmt->close();

        // 3. emergency_contacts
        $stmt = $conn->prepare("
            INSERT INTO emergency_contacts (enrollee_id, guardian_name, phone_number, address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss",
            $enrollee_id, $enc_guardian_name, $enc_guardian_phone, $enc_guardian_address
        );
        $stmt->execute();
        $stmt->close();

        // 4. education
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

        echo "You have submitted your enrollment form!<br><a href='index.php'>Go back to Home</a>";
    }

    $conn->close();
}
?>