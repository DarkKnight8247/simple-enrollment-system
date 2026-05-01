<?php
$servername = "localhost";
$username   = "root";
$password   = "";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS sunn_enrollment";
if ($conn->query($sql) === TRUE) {

    $conn->select_db("sunn_enrollment");

    // =============================================
    // 1. ENROLLEE
    // TEXT columns to accommodate encrypted values
    // =============================================
    $conn->query("
    CREATE TABLE IF NOT EXISTS enrollee (
        enrollee_id  INT AUTO_INCREMENT PRIMARY KEY,
        first_name   TEXT NOT NULL,
        last_name    TEXT NOT NULL,
        middle_name  TEXT,
        sex          TEXT NOT NULL,
        birthdate    TEXT NOT NULL,
        civil_status TEXT
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 2. CONTACTS
    // VARCHAR(500) for encrypted email (UNIQUE index
    // requires a fixed-length column, not TEXT)
    // =============================================
    $conn->query("
    CREATE TABLE IF NOT EXISTS contacts (
        contact_id   INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id  INT,
        email        VARCHAR(500) NOT NULL UNIQUE,
        phone_number TEXT NOT NULL,
        address      TEXT NOT NULL,
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 3. EMERGENCY CONTACTS
    // =============================================
    $conn->query("
    CREATE TABLE IF NOT EXISTS emergency_contacts (
        emergency_id  INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id   INT,
        guardian_name TEXT NOT NULL,
        phone_number  TEXT NOT NULL,
        address       TEXT NOT NULL,
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 4. COURSE
    // Not encrypted — stores plain course names
    // used as FK reference from education
    // =============================================
    $conn->query("
    CREATE TABLE IF NOT EXISTS course (
        course_id   INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(150) NOT NULL UNIQUE
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 5. EDUCATION
    // course_id allows NULL (ON DELETE SET NULL)
    // =============================================
    $conn->query("
        CREATE TABLE IF NOT EXISTS education (
        education_id    INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id     INT,
        course_id       INT NULL,
        reference_no    VARCHAR(30) NOT NULL UNIQUE,
        year_level      TEXT,
        previous_school TEXT,
        gpa             TEXT,
        status          ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
        submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES course(course_id)
            ON DELETE SET NULL
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 6. ADMINS
    // Standalone — no FK to enrollee tables
    // Passwords stored as bcrypt hashes
    // =============================================
    $conn->query("
    CREATE TABLE IF NOT EXISTS admins (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        username      VARCHAR(50)  NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ");

    // =============================================
    // 7. SEED COURSES
    // INSERT IGNORE skips duplicates safely on
    // repeated runs — won't error if already seeded
    // =============================================
    $conn->query("
    INSERT IGNORE INTO course (course_id, course_name) VALUES
    (1,  'Bachelor of Science in Computer Science'),
    (2,  'Bachelor of Science in Information Technology'),
    (3,  'Bachelor of Science in Information Systems'),
    (4,  'Bachelor of Science in Civil Engineering'),
    (5,  'Bachelor of Science in Electrical Engineering'),
    (6,  'Bachelor of Science in Mechanical Engineering'),
    (7,  'Bachelor of Science in Nursing'),
    (8,  'Bachelor of Science in Pharmacy'),
    (9,  'Bachelor of Science in Physical Therapy'),
    (10, 'Bachelor of Science in Accountancy'),
    (11, 'Bachelor of Science in Business Administration'),
    (12, 'Bachelor of Science in Tourism Management'),
    (13, 'Bachelor of Elementary Education'),
    (14, 'Bachelor of Secondary Education')
    ");

    echo "Database, tables, and seed courses created successfully.";

} else {
    die("ERROR creating database: " . $conn->error);
}

$conn->close();
?>