<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../../cryptograph_process.php'; 

$conn = new mysqli('localhost', 'root', '', 'sunn_enrollment');

$id = (int)($_GET['id'] ?? 0);

$query = "SELECT e.*, ed.*, c.course_name 
          FROM enrollee e 
          JOIN education ed ON e.enrollee_id = ed.enrollee_id 
          JOIN course c ON ed.course_id = c.course_id
          WHERE e.enrollee_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$year_level      = decryptData($data['year_level']);
$previous_school = decryptData($data['previous_school']);
$gpa             = decryptData($data['gpa']);
$fname           = decryptData($data['first_name']);
$lname           = decryptData($data['last_name']);
$current_status  = $data['status'] ?? 'pending';
$initials        = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Profile – SUNN</title>
    <link rel="stylesheet" href="sunn-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        /* ── NAV ── */
        nav {
           background: #ffffff;
            border-bottom: 3px solid #E8A020;
            box-shadow: 0 2px 16px rgba(0,0,0,0.05);
        }
      .nav-title { font-size: 1.05rem; font-weight: 700; color: #0f2466; }
        .nav-sub   { font-size: 0.73rem; color: #64748b; }
        .admin-pill {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e; 
        }
        .btn-logout {
            background: rgba(220,38,38,0.12);
            border: 1px solid rgba(220,38,38,0.35);
            color: #fca5a5;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: #DC2626; color: white; border-color: #DC2626; }

        /* ── PAGE HERO ── */
        .profile-hero {
            background: linear-gradient(135deg, #0f2466 0%, #1A3A8F 60%, #2451C4 100%);
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .profile-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('bg.jpg') center/cover no-repeat;
            opacity: 0.07;
        }
        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0; right: 0;
            height: 36px;
            background: #f5f5f0;
            clip-path: ellipse(55% 100% at 50% 100%);
        }
        .profile-hero-inner {
            position: relative;
            z-index: 1;
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 40px 60px;
            display: flex;
            align-items: center;
            gap: 28px;
        }

        /* ── AVATAR ── */
        .profile-avatar-lg {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            border: 4px solid rgba(255,255,255,0.25);
            box-shadow: 0 6px 24px rgba(0,0,0,0.25);
        }
        .profile-hero-info { flex: 1; }
        .profile-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(232,160,32,0.18);
            border: 1px solid rgba(232,160,32,0.4);
            color: #F5C842;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 10px;
        }
        .profile-hero-name {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: white;
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 8px;
        }
        .profile-hero-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .profile-meta-chip {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.80);
            font-size: 0.78rem;
            padding: 4px 12px;
            border-radius: 100px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        /* Status chip colors in hero */
        .profile-meta-chip.status-accepted { background: rgba(22,163,74,0.20); border-color: rgba(22,163,74,0.4); color: #86efac; }
        .profile-meta-chip.status-rejected { background: rgba(220,38,38,0.20); border-color: rgba(220,38,38,0.4); color: #fca5a5; }
        .profile-meta-chip.status-pending  { background: rgba(217,119,6,0.20);  border-color: rgba(217,119,6,0.4);  color: #fde68a; }

        /* ── BODY ── */
        .profile-body {
            max-width: 860px;
            margin: 0 auto;
            padding: 36px 40px 60px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--blue);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1.5px solid #c7d4f8;
            background: var(--blue-pale);
            margin-bottom: 28px;
            transition: all 0.2s;
        }
        .back-btn:hover { background: var(--blue); color: white; border-color: var(--blue); }

        /* ── GPA HIGHLIGHT ── */
        .gpa-highlight-card {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-light) 100%);
            border-radius: 18px;
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 6px 28px rgba(26,58,143,0.22);
        }
        .gpa-label {
            color: rgba(255,255,255,0.75);
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
        }
        .gpa-value {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            line-height: 1;
        }
        .gpa-icon { font-size: 3rem; opacity: 0.55; }
        .gpa-sub  { color: rgba(255,255,255,0.60); font-size: 0.78rem; margin-top: 4px; }

        /* ── PROFILE CARD ── */
        .profile-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(26,58,143,0.07);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .profile-card-header {
            padding: 18px 28px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--blue);
        }
        .profile-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--blue-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* ── DATA ROWS ── */
        .data-row {
            display: flex;
            align-items: center;
            padding: 18px 28px;
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.15s;
        }
        .data-row:last-child { border-bottom: none; }
        .data-row:hover { background: #fffbf0; }

        .data-label {
            width: 190px;
            flex-shrink: 0;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .data-label-icon { opacity: 0.6; font-size: 0.9rem; }
        .data-value {
            font-size: 0.94rem;
            font-weight: 500;
            color: var(--gray-900);
        }
        .data-value strong { font-weight: 700; color: var(--blue); }

        /* ── FOOTER NOTE ── */
        .profile-footer {
            text-align: center;
            margin-top: 36px;
            font-size: 0.78rem;
            color: #9CA3AF;
        }

        @media (max-width: 700px) {
            .profile-hero-inner { padding: 28px 20px 48px; flex-direction: column; }
            .profile-body { padding: 24px 16px 48px; }
            .data-row { flex-direction: column; align-items: flex-start; gap: 4px; }
            .data-label { width: auto; }
            .gpa-highlight-card { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body style="background: #f5f5f0;">

<!-- ── NAVIGATION ── -->
<nav>
    <div class="nav-brand">
        <div class="nav-logo">
            <img src="logo.png" alt="SUNN Logo" style="width:100%;height:100%;object-fit:contain;">
        </div>
        <div>
            <div class="nav-title">SUNN Admin Dashboard</div>
            <span class="nav-sub">State University of Northern Negros · Enrollment System</span>
        </div>
    </div>
    <div class="nav-right">
        <span class="admin-pill">👤 Administrator</span>
        <a href="admin_logout.php" class="btn-logout">⏻ Logout</a>
    </div>
</nav>

<!-- ── PROFILE HERO ── -->
<div class="profile-hero">
    <div class="profile-hero-inner">
        <div class="profile-avatar-lg"><?= $initials ?></div>
        <div class="profile-hero-info">
            <div class="profile-hero-badge">🎓 Applicant Profile</div>
            <div class="profile-hero-name"><?= htmlspecialchars($fname . ' ' . $lname) ?></div>
            <div class="profile-hero-meta">
                <div class="profile-meta-chip">🪪 ID #<?= $data['enrollee_id'] ?></div>
                <div class="profile-meta-chip status-<?= $current_status ?>">
                    <?= $current_status === 'accepted' ? '✅' : ($current_status === 'rejected' ? '❌' : '⏳') ?>
                    <?= ucfirst($current_status) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── PROFILE BODY ── -->
<div class="profile-body">
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>

    <!-- GPA Highlight -->
    <div class="gpa-highlight-card">
        <div>
            <div class="gpa-label">General Point Average</div>
            <div class="gpa-value"><?= htmlspecialchars($gpa) ?></div>
            <div class="gpa-sub">Academic performance indicator</div>
        </div>
        <div class="gpa-icon">🏅</div>
    </div>

    <!-- Educational Details Card -->
    <div class="profile-card">
        <div class="profile-card-header">
            <div class="profile-card-icon">📚</div>
            <h2>Educational Background</h2>
        </div>

        <div class="data-row">
            <div class="data-label"><span class="data-label-icon">🆔</span> Enrollee ID</div>
            <div class="data-value">#<?= $data['enrollee_id'] ?></div>
        </div>

        <div class="data-row">
            <div class="data-label"><span class="data-label-icon">🎓</span> Course Choice</div>
            <div class="data-value"><strong><?= htmlspecialchars($data['course_name']) ?></strong></div>
        </div>

        <div class="data-row">
            <div class="data-label"><span class="data-label-icon">📅</span> Year Level</div>
            <div class="data-value"><?= htmlspecialchars($year_level) ?></div>
        </div>

        <div class="data-row">
            <div class="data-label"><span class="data-label-icon">🏫</span> Previous School</div>
            <div class="data-value"><?= htmlspecialchars($previous_school) ?></div>
        </div>

        <div class="data-row">
            <div class="data-label"><span class="data-label-icon">⭐</span> GPA</div>
            <div class="data-value"><strong><?= htmlspecialchars($gpa) ?></strong></div>
        </div>
    </div>

    <div class="profile-footer">SUNN Enrollment System &nbsp;·&nbsp; Admin Portal &nbsp;·&nbsp; Applicant Record</div>
</div>

</body>
</html>