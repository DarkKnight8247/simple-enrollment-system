<?php
session_start();

if (empty($_SESSION['ref_no'])) {
    header("Location: index.php");
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
        ed.reference_no, e.status AS status, ed.submitted_at,
        ed.year_level, ed.previous_school, ed.gpa, ed.course_id,
        co.course_name
    FROM education ed
    INNER JOIN enrollee e            ON ed.enrollee_id = e.enrollee_id
    INNER JOIN contacts c            ON e.enrollee_id  = c.enrollee_id
    INNER JOIN emergency_contacts ec ON e.enrollee_id  = ec.enrollee_id
    LEFT  JOIN course co             ON ed.course_id   = co.course_id
    WHERE ed.reference_no = ?
");
$stmt->bind_param("s", $ref_no);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    session_destroy();
    header("Location: index.php?error=" . urlencode("Record not found."));
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
$is_pending = strtolower($status) === 'pending';

$status_class = match($status) {
    'accepted' => 'status-accepted',
    'rejected' => 'status-rejected',
    default    => 'status-pending',
};
$status_icon = match($status) {
    'accepted' => '✅',
    'rejected' => '❌',
    default    => '⏳',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Enrollment Status – SUNN</title>
    <link rel="stylesheet" href="view-status.css">
</head>
<body>

<nav>
    <div class="nav-brand">
        <div class="nav-logo">
            <img src="../styles/graphics/logo.png" alt="SUNN Logo">
        </div>
        <div>
            <div class="nav-title">SUNN Enrollment System</div>
            <div class="nav-sub">State University of Northern Negros</div>
        </div>
    </div>
    <div class="nav-links">
        <a href="../../index.php">Home</a>
        <a href="logout.php" class="btn-logout">⏻ Logout</a>
    </div>
</nav>

<div class="page-wrapper-wide">

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">✓ Your information has been updated successfully.</div>
    <?php endif; ?>

    <!-- ── STATUS CARD ── -->
    <div class="card">
        <div class="card-header">
            <h2>My Application Status</h2>
            <p>Logged in as reference number: <?= htmlspecialchars($ref_no) ?></p>
        </div>
        <div class="card-body">

            <div class="ref-box">
                <div class="ref-label">Reference Number</div>
                <div class="ref-number"><?= htmlspecialchars($row['reference_no']) ?></div>
            </div>

            <div class="data-row">
                <div class="data-label">Status</div>
                <div class="data-value">
                    <span class="status-badge <?= $status_class ?>">
                        <?= $status_icon ?> <?= htmlspecialchars($status) ?>
                    </span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Submitted</div>
                <div class="data-value"><?= htmlspecialchars($row['submitted_at']) ?></div>
            </div>

            <?php if ($status === 'Accepted'): ?>
                <div class="alert alert-success" style="margin-top:16px;">
                    ✅ Your application has been <strong>accepted</strong>. Please proceed to the registrar's office for next steps.
                </div>
            <?php elseif ($status === 'Rejected'): ?>
                <div class="alert alert-error" style="margin-top:16px;">
                    ❌ Your application was <strong>rejected</strong>. You may <a href="../register/form.php">submit a new application</a>.
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="margin-top:16px;">
                    ⏳ Your application is <strong>pending review</strong>. You may edit your details below while waiting.
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if ($is_pending): ?>
    <!-- ════════════════════════════════════════ -->
    <!-- EDITABLE FORM — Pending only            -->
    <!-- ════════════════════════════════════════ -->

    <form method="POST" action="process_update.php">
        <input type="hidden" name="reference_no" value="<?= htmlspecialchars($ref_no) ?>">

        <!-- Personal Information -->
        <div class="card-section">
            <div class="card-section-header">
                <h3>👤 Personal Information</h3>
            </div>
            <div class="card-section-body">
                <div class="form-row-3">
                    <div class="form-group">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($middle_name) ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
                    </div>
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Date of Birth <span class="req">*</span></label>
                        <input type="date" name="birthdate" value="<?= htmlspecialchars($birthdate) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sex <span class="req">*</span></label>
                        <select name="sex" required>
                            <option value="">— Select —</option>
                            <option value="Male"   <?= $sex === 'Male'   ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status <span class="req">*</span></label>
                        <select name="civil_status" required>
                            <option value="">— Select —</option>
                            <option value="Single"    <?= $civil_status === 'Single'    ? 'selected' : '' ?>>Single</option>
                            <option value="Married"   <?= $civil_status === 'Married'   ? 'selected' : '' ?>>Married</option>
                            <option value="Widowed"   <?= $civil_status === 'Widowed'   ? 'selected' : '' ?>>Widowed</option>
                            <option value="Separated" <?= $civil_status === 'Separated' ? 'selected' : '' ?>>Separated</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card-section">
            <div class="card-section-header">
                <h3>📞 Contact Information</h3>
            </div>
            <div class="card-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>"
                               pattern="^09\d{9}$" maxlength="11" required>
                        <div class="form-hint">Format: 09XXXXXXXXX</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address <span class="req">*</span></label>
                    <textarea name="address" required><?= htmlspecialchars($address) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card-section">
            <div class="card-section-header">
                <h3>🚨 Emergency Contact</h3>
            </div>
            <div class="card-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Guardian Name <span class="req">*</span></label>
                        <input type="text" name="guardian_name" value="<?= htmlspecialchars($guardian_name) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Guardian Phone <span class="req">*</span></label>
                        <input type="tel" name="guardian_phone" value="<?= htmlspecialchars($guardian_phone) ?>"
                               pattern="^09\d{9}$" maxlength="11" required>
                        <div class="form-hint">Format: 09XXXXXXXXX</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Guardian Address <span class="req">*</span></label>
                    <textarea name="guardian_address" required><?= htmlspecialchars($guardian_address) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="card-section">
            <div class="card-section-header">
                <h3>🎓 Academic Information</h3>
            </div>
            <div class="card-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Course <span class="req">*</span></label>
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
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year Level <span class="req">*</span></label>
                        <select name="year_level" required>
                            <option value="">— Select —</option>
                            <option value="1st Year"   <?= $year_level === '1st Year'   ? 'selected' : '' ?>>1st Year (Freshmen)</option>
                            <option value="2nd Year"   <?= $year_level === '2nd Year'   ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3rd Year"   <?= $year_level === '3rd Year'   ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4th Year"   <?= $year_level === '4th Year'   ? 'selected' : '' ?>>4th Year</option>
                            <option value="Transferee" <?= $year_level === 'Transferee' ? 'selected' : '' ?>>Transferee</option>
                            <option value="Shiftee"    <?= $year_level === 'Shiftee'    ? 'selected' : '' ?>>Shiftee</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Previous School</label>
                        <input type="text" name="previous_school" value="<?= htmlspecialchars($previous_school) ?>">
                    </div>
                    <div class="form-group">
                        <label>GPA</label>
                        <input type="number" name="gpa" step="0.01" min="1" max="100"
                               value="<?= htmlspecialchars($gpa) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="view_dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

    <?php else: ?>
    <!-- ════════════════════════════════════════ -->
    <!-- READ-ONLY VIEW — Accepted / Rejected    -->
    <!-- ════════════════════════════════════════ -->

        <!-- Personal Information -->
        <div class="card-section">
            <div class="card-section-header"><h3>👤 Personal Information</h3></div>
            <div class="card-section-body">
                <div class="data-row"><div class="data-label">Full Name</div><div class="data-value"><?= htmlspecialchars("$first_name $middle_name $last_name") ?></div></div>
                <div class="data-row"><div class="data-label">Sex</div><div class="data-value"><?= htmlspecialchars($sex) ?></div></div>
                <div class="data-row"><div class="data-label">Birthdate</div><div class="data-value"><?= htmlspecialchars($birthdate) ?></div></div>
                <div class="data-row"><div class="data-label">Civil Status</div><div class="data-value"><?= htmlspecialchars($civil_status) ?></div></div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card-section">
            <div class="card-section-header"><h3>📞 Contact Information</h3></div>
            <div class="card-section-body">
                <div class="data-row"><div class="data-label">Email</div><div class="data-value"><?= htmlspecialchars($email) ?></div></div>
                <div class="data-row"><div class="data-label">Phone</div><div class="data-value"><?= htmlspecialchars($phone) ?></div></div>
                <div class="data-row"><div class="data-label">Address</div><div class="data-value"><?= htmlspecialchars($address) ?></div></div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card-section">
            <div class="card-section-header"><h3>🚨 Emergency Contact</h3></div>
            <div class="card-section-body">
                <div class="data-row"><div class="data-label">Guardian</div><div class="data-value"><?= htmlspecialchars($guardian_name) ?></div></div>
                <div class="data-row"><div class="data-label">Guardian Phone</div><div class="data-value"><?= htmlspecialchars($guardian_phone) ?></div></div>
                <div class="data-row"><div class="data-label">Guardian Address</div><div class="data-value"><?= htmlspecialchars($guardian_address) ?></div></div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="card-section">
            <div class="card-section-header"><h3>🎓 Academic Information</h3></div>
            <div class="card-section-body">
                <div class="data-row"><div class="data-label">Course</div><div class="data-value"><?= htmlspecialchars($row['course_name'] ?? 'N/A') ?></div></div>
                <div class="data-row"><div class="data-label">Year Level</div><div class="data-value"><?= htmlspecialchars($year_level) ?></div></div>
                <div class="data-row"><div class="data-label">Previous School</div><div class="data-value"><?= htmlspecialchars($previous_school ?: 'N/A') ?></div></div>
                <div class="data-row"><div class="data-label">GPA</div><div class="data-value"><?= htmlspecialchars($gpa ?: 'N/A') ?></div></div>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
