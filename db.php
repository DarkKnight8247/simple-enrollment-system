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

    // =========================
    // ENROLLEE TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS enrollee (
        enrollee_id  INT AUTO_INCREMENT PRIMARY KEY,
        first_name   VARCHAR(100) NOT NULL,
        last_name    VARCHAR(100) NOT NULL,
        middle_name  VARCHAR(100),
        sex          ENUM('Male', 'Female') NOT NULL,
        birthdate    DATE NOT NULL,
        civil_status VARCHAR(20)
    ) ENGINE=InnoDB;
    ");

    // =========================
    // CONTACTS TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS contacts (
        contact_id   INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id  INT,
        email        VARCHAR(150) UNIQUE NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        address      TEXT NOT NULL,
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ");

    // =========================
    // EMERGENCY CONTACTS TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS emergency_contacts (
        emergency_id  INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id   INT,
        guardian_name VARCHAR(150) NOT NULL,
        phone_number  VARCHAR(20) NOT NULL,
        address       TEXT NOT NULL,
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ");

    // =========================
    // COURSE TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS course (
        course_id   INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(150) NOT NULL UNIQUE
    ) ENGINE=InnoDB;
    ");

    // =========================
    // EDUCATION TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS education (
        education_id    INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id     INT,
        course_id       INT,
        year_level      VARCHAR(20),
        previous_school VARCHAR(200),
        gpa             VARCHAR(10),
        FOREIGN KEY (enrollee_id) REFERENCES enrollee(enrollee_id)
            ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES course(course_id)
            ON DELETE SET NULL
    ) ENGINE=InnoDB;
    ");

    // =========================
    // ADMINS TABLE
    // =========================
    $conn->query("
    CREATE TABLE IF NOT EXISTS admins (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        username      VARCHAR(50)  NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ");

    echo "Database and tables created successfully.";

} else {
    die("ERROR creating database: " . $conn->error);
}

$conn->close();
?>
