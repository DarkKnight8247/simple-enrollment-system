<?php
session_start();

if (empty($_SESSION['ref_no'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/../../cryptograph_process.php';

$ref_no = $_SESSION['ref_no'];

$conn = new mysqli("localhost", "root", "", "sunn_enrollment");
if ($conn->connect_error) die("Connection failed.");

$stmt = $conn->prepare("
    SELECT
        e.enrollee_id,
        e.first_name, e.last_name, e.middle_name,
        e.sex, e.birthdate, e.civil_status,
        c.email, c.phone_number, c.address,
        ec.guardian_name, ec.phone_number AS guardian_phone,
        ec.address AS guardian_address,
        ed.reference_no, ed.status, ed.submitted_at,
        ed.year_level, ed.previous_school, ed.gpa, ed.course_id,
        co.course_name
    FROM education ed
    INNER JOIN enrollee e             ON ed.enrollee_id = e.enrollee_id
    INNER JOIN contacts c             ON e.enrollee_id  = c.enrollee_id
    INNER JOIN emergency_contacts ec  ON e.enrollee_id  = ec.enrollee_id
    LEFT  JOIN course co              ON ed.course_id   = co.course_id
    WHERE ed.reference_no = ?
");
$stmt->bind_param("s", $ref_no);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    session_destroy();
    header("Location: login.php?error=" . urlencode("Record not found."));
    exit;
}

// Decrypt
$first_name       = decryptData($row['first_name']);
$middle_name      = decryptData($row['middle_name']);
$last_name        = decryptData($row['last_name']);
$sex              = decryptData($row['sex']);
$birthdate        = decryptData($row['birthdate']);
$civil_status     = decryptData($row['civil_status']);
$email            = decryptData($row['email']);
$phone            = decryptData($row['phone_number']);
$address          = decryptData($row['address']);
$guardian_name    = decryptData($row['guardian_name']);
$guardian_phone   = decryptData($row['guardian_phone']);
$guardian_address = decryptData($row['guardian_address']);
$year_level       = decryptData($row['year_level']);
$previous_school  = decryptData($row['previous_school']);
$gpa              = decryptData($row['gpa']);

$status     = $row['status'];
$is_pending = $status === 'Pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Enrollment Status – SUNN</title>
</head>
<body>

    <h1>SUNN Enrollment System</h1>
    <a href="../../index.php">Home</a> |
    <a href="logout.php">Logout</a>

    <hr>

    <h2>My Application Status</h2>
    <p>Reference No: <strong><?= htmlspecialchars($row['reference_no']) ?></strong></p>
    <p>Submitted: <strong><?= htmlspecialchars($row['submitted_at']) ?></strong></p>
    <p>Status: <strong><?= htmlspecialchars($status) ?></strong></p>

    <?php if ($status === 'Accepted'): ?>
        <p>Your application has been <strong>accepted</strong>. Please proceed to the registrar's office.</p>
    <?php elseif ($status === 'Rejected'): ?>
        <p>Your application was <strong>rejected</strong>. You may <a href="../register/form.php">submit a new application</a>.</p>
    <?php else: ?>
        <p>Your application is currently <strong>pending review</strong>. You may edit your details below while waiting.</p>
    <?php endif; ?>

    <hr>

    <?php if ($is_pending): ?>

    <!-- ═══════════════════════════════ -->
    <!-- EDITABLE FORM (Pending only)   -->
    <!-- ═══════════════════════════════ -->
    <h3>Edit Your Information</h3>
    <p><small>You can update your details while your application is still Pending.</small></p>

    <form method="POST" action="process_update.php">
        <input type="hidden" name="reference_no" value="<?= htmlspecialchars($ref_no) ?>">

        <h4>Personal Information</h4>

        <label>First Name *</label><br>
        <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required><br><br>

        <label>Middle Name</label><br>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($middle_name) ?>"><br><br>

        <label>Last Name *</label><br>
        <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required><br><br>

        <label>Date of Birth *</label><br>
        <input type="date" name="birthdate" value="<?= htmlspecialchars($birthdate) ?>" required><br><br>

        <label>Sex *</label><br>
        <select name="sex" required>
            <option value="">— Select —</option>
            <option value="Male"   <?= $sex === 'Male'   ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
        </select><br><br>

        <label>Civil Status *</label><br>
        <select name="civil_status" required>
            <option value="">— Select —</option>
            <option value="Single"    <?= $civil_status === 'Single'    ? 'selected' : '' ?>>Single</option>
            <option value="Married"   <?= $civil_status === 'Married'   ? 'selected' : '' ?>>Married</option>
            <option value="Widowed"   <?= $civil_status === 'Widowed'   ? 'selected' : '' ?>>Widowed</option>
            <option value="Separated" <?= $civil_status === 'Separated' ? 'selected' : '' ?>>Separated</option>
        </select><br><br>

        <h4>Contact Information</h4>

        <label>Email *</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br><br>

        <label>Phone Number *</label><br>
        <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>"
               pattern="^09\d{9}$" maxlength="11" required><br>
        <small>Format: 09XXXXXXXXX</small><br><br>

        <label>Address *</label><br>
        <textarea name="address" required><?= htmlspecialchars($address) ?></textarea><br><br>

        <h4>Emergency Contact</h4>

        <label>Guardian Name *</label><br>
        <input type="text" name="guardian_name" value="<?= htmlspecialchars($guardian_name) ?>" required><br><br>

        <label>Guardian Phone *</label><br>
        <input type="tel" name="guardian_phone" value="<?= htmlspecialchars($guardian_phone) ?>"
               pattern="^09\d{9}$" maxlength="11" required><br>
        <small>Format: 09XXXXXXXXX</small><br><br>

        <label>Guardian Address *</label><br>
        <textarea name="guardian_address" required><?= htmlspecialchars($guardian_address) ?></textarea><br><br>

        <h4>Academic Information</h4>

        <label>Preferred Course *</label><br>
        <select name="course_id" required>
            <option value="">— Select Course —</option>
            <optgroup label="College of Computing">
                <option value="1"  <?= $row['course_id'] == 1  ? 'selected' : '' ?>>BS Computer Science</option>
                <option value="2"  <?= $row['course_id'] == 2  ? 'selected' : '' ?>>BS Information Technology</option>
                <option value="3"  <?= $row['course_id'] == 3  ? 'selected' : '' ?>>BS Information Systems</option>
            </optgroup>
            <optgroup label="College of Engineering">
                <option value="4"  <?= $row['course_id'] == 4  ? 'selected' : '' ?>>BS Civil Engineering</option>
                <option value="5"  <?= $row['course_id'] == 5  ? 'selected' : '' ?>>BS Electrical Engineering</option>
                <option value="6"  <?= $row['course_id'] == 6  ? 'selected' : '' ?>>BS Mechanical Engineering</option>
            </optgroup>
            <optgroup label="College of Health Sciences">
                <option value="7"  <?= $row['course_id'] == 7  ? 'selected' : '' ?>>BS Nursing</option>
                <option value="8"  <?= $row['course_id'] == 8  ? 'selected' : '' ?>>BS Pharmacy</option>
                <option value="9"  <?= $row['course_id'] == 9  ? 'selected' : '' ?>>BS Physical Therapy</option>
            </optgroup>
            <optgroup label="College of Business">
                <option value="10" <?= $row['course_id'] == 10 ? 'selected' : '' ?>>BS Accountancy</option>
                <option value="11" <?= $row['course_id'] == 11 ? 'selected' : '' ?>>BS Business Administration</option>
                <option value="12" <?= $row['course_id'] == 12 ? 'selected' : '' ?>>BS Tourism Management</option>
            </optgroup>
            <optgroup label="College of Education">
                <option value="13" <?= $row['course_id'] == 13 ? 'selected' : '' ?>>Bachelor of Elementary Education</option>
                <option value="14" <?= $row['course_id'] == 14 ? 'selected' : '' ?>>Bachelor of Secondary Education</option>
            </optgroup>
        </select><br><br>

        <label>Year Level *</label><br>
        <select name="year_level" required>
            <option value="">— Select —</option>
            <option value="1st Year"   <?= $year_level === '1st Year'   ? 'selected' : '' ?>>1st Year (Freshmen)</option>
            <option value="2nd Year"   <?= $year_level === '2nd Year'   ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd Year"   <?= $year_level === '3rd Year'   ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th Year"   <?= $year_level === '4th Year'   ? 'selected' : '' ?>>4th Year</option>
            <option value="Transferee" <?= $year_level === 'Transferee' ? 'selected' : '' ?>>Transferee</option>
            <option value="Shiftee"    <?= $year_level === 'Shiftee'    ? 'selected' : '' ?>>Shiftee</option>
        </select><br><br>

        <label>Previous School</label><br>
        <input type="text" name="previous_school" value="<?= htmlspecialchars($previous_school) ?>"><br><br>

        <label>GPA</label><br>
        <input type="number" name="gpa" step="0.01" min="1" max="100"
               value="<?= htmlspecialchars($gpa) ?>"><br><br>

        <button type="submit">Save Changes</button>

    </form>

    <?php else: ?>

    <!-- ═══════════════════════════════════════ -->
    <!-- READ-ONLY VIEW (Accepted / Rejected)   -->
    <!-- ═══════════════════════════════════════ -->
    <h3>Personal Information</h3>
    <p>Name: <?= htmlspecialchars("$first_name $middle_name $last_name") ?></p>
    <p>Sex: <?= htmlspecialchars($sex) ?></p>
    <p>Birthdate: <?= htmlspecialchars($birthdate) ?></p>
    <p>Civil Status: <?= htmlspecialchars($civil_status) ?></p>

    <h3>Contact Information</h3>
    <p>Email: <?= htmlspecialchars($email) ?></p>
    <p>Phone: <?= htmlspecialchars($phone) ?></p>
    <p>Address: <?= htmlspecialchars($address) ?></p>

    <h3>Emergency Contact</h3>
    <p>Guardian: <?= htmlspecialchars($guardian_name) ?></p>
    <p>Guardian Phone: <?= htmlspecialchars($guardian_phone) ?></p>
    <p>Guardian Address: <?= htmlspecialchars($guardian_address) ?></p>

    <h3>Academic Information</h3>
    <p>Course: <?= htmlspecialchars($row['course_name'] ?? 'N/A') ?></p>
    <p>Year Level: <?= htmlspecialchars($year_level) ?></p>
    <p>Previous School: <?= htmlspecialchars($previous_school ?: 'N/A') ?></p>
    <p>GPA: <?= htmlspecialchars($gpa ?: 'N/A') ?></p>

    <?php endif; ?>

</body>
</html>