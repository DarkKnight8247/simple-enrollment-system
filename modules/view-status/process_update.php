<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require __DIR__ . '/../../cryptograph_process.php';

    $reference_no    = htmlspecialchars(trim($_POST['reference_no']    ?? ''));
    $first_name      = htmlspecialchars(trim($_POST['first_name']      ?? ''));
    $middle_name     = htmlspecialchars(trim($_POST['middle_name']     ?? ''));
    $last_name       = htmlspecialchars(trim($_POST['last_name']       ?? ''));
    $sex             = htmlspecialchars(trim($_POST['sex']             ?? ''));
    $birthdate       = htmlspecialchars(trim($_POST['birthdate']       ?? ''));
    $civil_status    = htmlspecialchars(trim($_POST['civil_status']    ?? ''));
    $email           = htmlspecialchars(trim($_POST['email']           ?? ''));
    $phone           = htmlspecialchars(trim($_POST['phone']           ?? ''));
    $address         = htmlspecialchars(trim($_POST['address']         ?? ''));
    $year_level      = htmlspecialchars(trim($_POST['year_level']      ?? ''));
    $previous_school = htmlspecialchars(trim($_POST['previous_school'] ?? ''));
    $gpa             = htmlspecialchars(trim($_POST['gpa']             ?? ''));
    $course_id       = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

    // Validate
    if ($reference_no === '') die("<p style='color:red;'>Invalid request.</p>");
    if (!preg_match('/^09\d{9}$/', $phone)) die("<p style='color:red;'>Invalid phone format.</p>");
    if ($course_id === null || $course_id <= 0) die("<p style='color:red;'>Please select a course.</p>");

    $conn = new mysqli("localhost", "root", "", "sunn_enrollment");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Safety check — only allow update if still Pending
        $stmt = $conn->prepare("
            SELECT ed.enrollee_id, ed.status
            FROM education ed
            WHERE ed.reference_no = ?
        ");
        $stmt->bind_param("s", $reference_no);
        $stmt->execute();
        $stmt->bind_result($enrollee_id, $current_status);
        $stmt->fetch();
        $stmt->close();

        if (!$enrollee_id) {
            die("<p style='color:red;'>Reference number not found.</p>");
        }

        if ($current_status !== 'Pending') {
            die("<p style='color:red;'>Your application is already $current_status and can no longer be modified.</p>");
        }

        // Encrypt updated values
        $enc_first_name      = encryptData($first_name);
        $enc_middle_name     = encryptData($middle_name);
        $enc_last_name       = encryptData($last_name);
        $enc_sex             = encryptData($sex);
        $enc_birthdate       = encryptData($birthdate);
        $enc_civil_status    = encryptData($civil_status);
        $enc_email           = encryptData($email);
        $enc_phone           = encryptData($phone);
        $enc_address         = encryptData($address);
        $enc_year_level      = encryptData($year_level);
        $enc_previous_school = encryptData($previous_school);
        $enc_gpa             = encryptData($gpa);

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
        $stmt->bind_param("sssi",
            $enc_email, $enc_phone, $enc_address,
            $enrollee_id
        );
        $stmt->execute();
        $stmt->close();

        // Update education
        $stmt = $conn->prepare("
            UPDATE education
            SET course_id = ?, year_level = ?, previous_school = ?, gpa = ?
            WHERE enrollee_id = ? AND reference_no = ?
        ");
        $stmt->bind_param("issssi",
            $course_id, $enc_year_level, $enc_previous_school, $enc_gpa,
            $enrollee_id, $reference_no
        );
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        echo "
            <p style='color:green;'>Your application has been updated successfully.</p>
            <p>Reference No: <strong>$reference_no</strong></p>
            <br>
            <a href='index.php'>Check status again</a> |
            <a href='../../index.php'>Go back to Home</a>
        ";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "<p style='color:red;'>Update failed: " . $e->getMessage() . "</p>";
    }

    $conn->close();
}
?>