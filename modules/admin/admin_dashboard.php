<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../../cryptograph_process.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli('localhost', 'root', '', 'sunn_enrollment');

// --- 1. AUTOMATED NOTIFICATION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['enrollee_id'];
    $action = $_POST['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM enrollee WHERE enrollee_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?notified=deleted");
            exit;
        }
    } else {
        $status = ($action === 'accept') ? 'accepted' : 'rejected';
        $stmt = $conn->prepare("UPDATE enrollee SET status = ? WHERE enrollee_id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            $details = $conn->prepare("SELECT e.first_name, c.email FROM enrollee e JOIN contacts c ON e.enrollee_id = c.enrollee_id WHERE e.enrollee_id = ?");
            $details->bind_param("i", $id);
            $details->execute();
            $data = $details->get_result()->fetch_assoc();

            if ($data) {
                $fname = decryptData($data['first_name']);
                $email = decryptData($data['email']);

                if ($status === 'accepted') {
                    $subject = "Congratulations! Admission to SUNN";
                    $message = "<div style='font-family:Arial,sans-serif;color:#1e293b;line-height:1.6;'><h2 style='color:#16a34a;'>Admission Accepted</h2><p>Dear <strong>$fname</strong>,</p><p>We are thrilled to inform you that your enrollment application at the <strong>State University of Northern Negros</strong> has been <strong>ACCEPTED</strong>.</p><p>Please proceed to the Admissions Office with your original documents to finalize your enrollment.</p><hr style='border:0;border-top:1px solid #e2e8f0;'><small>SUNN Enrollment System | Automated Notification</small></div>";
                } else {
                    $subject = "Application Status Update";
                    $message = "<div style='font-family:Arial,sans-serif;color:#1e293b;line-height:1.6;'><h2 style='color:#dc2626;'>Application Update</h2><p>Dear <strong>$fname</strong>,</p><p>Thank you for your interest in joining State University of Northern Negros. After careful review, we regret to inform you that we are <strong>unable to offer you admission</strong> at this time.</p><p>We appreciate your interest and wish you the best in your academic pursuits.</p><hr style='border:0;border-top:1px solid #e2e8f0;'><small>SUNN Enrollment System | Automated Notification</small></div>";
                }

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sunnnotifier@gmail.com';
                    $mail->Password   = 'lvvg pymy ubfu xqvt';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->setFrom('sunnnotifier@gmail.com', 'SUNN Admissions');
                    $mail->addAddress($email, $fname);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;
                    $mail->send();
                    $conn->query("UPDATE enrollee SET notified = 1 WHERE enrollee_id = $id");
                    header("Location: admin_dashboard.php?notified=success");
                    exit;
                } catch (Exception $e) {
                    header("Location: admin_dashboard.php?notified=error");
                    exit;
                }
            }
        }
    }
}

// --- 2. STATS LOGIC ---
$stats_res = $conn->query("SELECT status, COUNT(*) as cnt FROM enrollee GROUP BY status");
$stats = ['Pending' => 0, 'Accepted' => 0, 'Rejected' => 0];
if ($stats_res) {
    while ($s = $stats_res->fetch_assoc()) { $stats[$s['status']] = (int)$s['cnt']; }
}
$total = array_sum($stats);

// --- 3. DATA FETCHING ---
$search   = $_GET['search'] ?? '';
$filter   = $_GET['status'] ?? 'all';
$notified = $_GET['notified'] ?? '';

$query = "SELECT e.*, ed.reference_no FROM enrollee e LEFT JOIN education ed ON e.enrollee_id = ed.enrollee_id WHERE 1=1";
if ($filter !== 'all') {
    $query .= " AND e.status = '" . $conn->real_escape_string($filter) . "'";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – SUNN Enrollment</title>
    <link rel="stylesheet" href="sunn-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>

        /* ─── NAVBAR ─── */
        nav {
            background: #0f2466;
            border-bottom: 2px solid var(--gold);
            box-shadow: 0 2px 16px rgba(15,36,102,0.22);
        }
        .nav-title { font-size: 1.05rem; font-weight: 700; color: white; }
        .nav-sub   { font-size: 0.73rem; color: rgba(255,255,255,0.58); }
        .admin-pill { background: rgba(232,160,32,0.15); border-color: rgba(232,160,32,0.4); color: #F5C842; }
        .btn-logout { background: rgba(220,38,38,0.12); border: 1px solid rgba(220,38,38,0.35); color: #fca5a5; }
        .btn-logout:hover { background: #DC2626; color: white; border-color: #DC2626; }

        /* ─── HERO ─── */
        .dash-hero { background: linear-gradient(135deg,#E8A020 0%,#F5C842 60%,#fbd76d 100%); position: relative; overflow: hidden; }
        .dash-hero::before { content:''; position:absolute; inset:0; background:url('bg.jpg') center/cover no-repeat; opacity:0.12; filter:grayscale(100%); }
        .dash-hero::after  { content:''; position:absolute; bottom:-2px; left:0; right:0; height:40px; background:var(--cream); clip-path:ellipse(55% 100% at 50% 100%); }
        .dash-hero-inner {
            position: relative; z-index: 1;
            max-width: 1200px; margin: 0 auto;
            padding: 40px 40px 60px;
            display: flex; align-items: center; justify-content: space-between; gap: 24px;
        }
        .dash-hero-text h1 { font-family:'Playfair Display',serif; font-size:2.2rem; color:white; margin-bottom:8px; line-height:1.2; }
        .dash-hero-text p  { color:rgba(255,255,255,0.80); font-size:0.92rem; max-width:480px; }
        .dash-hero-logo    { width:100px; height:100px; flex-shrink:0; filter:drop-shadow(0 4px 20px rgba(0,0,0,0.3)); opacity:0.95; }

        /* ─── BODY ─── */
        .dash-body { max-width:1200px; margin:0 auto; padding:36px 40px 60px; }

        /* ─── STATS ─── */
        .stats-grid { margin-bottom:36px; }
        .stat-card  { border-radius:18px; padding:26px 24px 22px; transition:transform 0.2s,box-shadow 0.2s; }
        .stat-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(0,0,0,0.10); }
        .stat-card.rejected::before { background:linear-gradient(90deg,#DC2626,#f87171); }

        /* ─── SECTION LABEL ─── */
        .section-label { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
        .section-label h2 { font-family:'Playfair Display',serif; font-size:1.35rem; font-weight:700; color:var(--blue); }
        .section-label .line { flex:1; height:1px; background:var(--gray-200); }

        /* ─── FILTER BAR ─── */
        .filter-bar { border-radius:16px; padding:18px 22px; }

        /* ─── TABLE ─── */
        .table-wrap { border-radius:18px; overflow:hidden; border:1px solid var(--gray-200); box-shadow:0 4px 24px rgba(26,58,143,0.07); }
        .table-header-bar { display:flex; align-items:center; justify-content:space-between; padding:18px 28px; border-bottom:1px solid var(--gray-100); background:white; }
        .table-header-bar h3 { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:var(--blue); }
        .result-count { font-size:0.8rem; color:var(--gray-500); background:var(--gray-100); padding:4px 12px; border-radius:100px; font-weight:500; }
        thead { background:linear-gradient(90deg,#0f2466 0%,#1A3A8F 60%,#2451C4 100%); }
        thead th { padding:15px 24px; }
        tbody tr:hover { background:#fffbf0; }

        /* ─── APPLICANT CELL ─── */
        .applicant-cell { display:flex; align-items:center; gap:12px; }
        .avatar { width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,var(--gold) 0%,var(--gold-light) 100%); display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700; color:white; flex-shrink:0; box-shadow:0 2px 8px rgba(232,160,32,0.3); }
        .applicant-name { font-weight:600; color:var(--gray-900); font-size:0.92rem; }

        /* ─── ACTIONS: fill the cell, buttons fill right ─── */
        td.actions-cell { width: 100%; padding-left: 16px; padding-right: 16px; }
        .actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            width: 100%;
        }
        .actions .btn {
            width: 100%;
            justify-content: center;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .applicant-id   { font-size:0.75rem; color:var(--gray-500); margin-top:2px; }

        /* ─── TABLE COLUMN WIDTHS ─── */
        table { table-layout: fixed; }
        thead th:nth-child(1) { width: 26%; }
        thead th:nth-child(2) { width: 12%; }
        thead th:nth-child(3) { width: 62%; }

        /* ─── ACTION BUTTONS ─── */
        .btn       { padding:8px 14px; font-size:0.82rem; border-radius:8px; cursor:pointer; border:none; font-weight:600; }
        .btn-view  { background:var(--blue-pale); color:var(--blue); border:1.5px solid #c7d4f8; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
        .btn-view:hover { background:var(--blue); color:white; border-color:var(--blue); }
        .btn-accept { background:linear-gradient(135deg,#16A34A,#22c55e); color:white; }
        .btn-reject { background:linear-gradient(135deg,#DC2626,#f87171); color:white; }
        .btn-delete { background:#4b5563; color:white; }
        .btn-delete:hover { background:#1f2937; }

        /* ─── TOAST ─── */
        .toast { position:fixed; top:80px; right:20px; z-index:999; padding:14px 22px; border-radius:12px; font-size:0.88rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,0.12); max-width:calc(100vw - 40px); }
        .toast.success { background:#dcfce7; color:#166534; }
        .toast.error   { background:#fee2e2; color:#991b1b; }
        .toast.deleted { background:#f3f4f6; color:#374151; }

        .empty-state { padding:72px 24px; text-align:center; }
        .empty-icon  { font-size:3.5rem; margin-bottom:16px; opacity:0.5; }

        /* ══════════════════════════════════════
           RESPONSIVE – Tablet ≤ 768px
        ══════════════════════════════════════ */
        @media (max-width: 768px) {

            /* Navbar */
            nav { height:auto; min-height:56px; padding:8px 14px; }
            .nav-title  { font-size:0.9rem; }
            .nav-sub    { display:none; }
            .admin-pill { display:none; }
            .nav-logo   { width:32px; height:32px; }
            .btn-logout { padding:7px 12px; font-size:0.76rem; }

            /* Hero */
            .dash-hero-inner { padding:22px 16px 42px; flex-direction:column; align-items:flex-start; gap:0; }
            .dash-hero-logo  { display:none; }
            .dash-hero-text h1 { font-size:1.45rem; }
            .dash-hero-text p  { font-size:0.82rem; }

            /* Body */
            .dash-body { padding:18px 14px 48px; }

            /* Stats: 2-column grid */
            .stats-grid { grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:20px; }
            .stat-card  { padding:14px 12px 12px; border-radius:14px; }
            .stat-value { font-size:1.7rem; }
            .stat-icon  { font-size:1.3rem; margin-bottom:8px; }

            /* Filter bar */
            .filter-bar { flex-direction:column; padding:14px; gap:10px; }
            .filter-bar input[type="text"],
            .filter-bar select,
            .filter-bar .btn-apply { width:100%; min-width:unset; }

            /* Table header bar */
            .table-header-bar { padding:12px 16px; }

            /* ── TABLE → CARD ROWS ──
               thead is hidden; each <tr> becomes a card.
               td[data-label] shows a label via CSS ::before. */
            .table-wrap  { overflow-x:unset; border-radius:14px; }
            table        { min-width:unset; }
            thead        { display:none; }

            tbody tr {
                display: block;
                padding: 14px 16px 12px;
                border-bottom: 1px solid var(--gray-100);
                background: white;
            }
            tbody tr:last-child { border-bottom:none; }
            tbody tr:hover      { background:#fffbf0; }

            td {
                display: flex;
                align-items: center;
                padding: 4px 0;
                font-size: 0.87rem;
                border: none;
                gap: 8px;
            }
            td::before {
                content: attr(data-label);
                font-size: 0.65rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.07em;
                color: var(--gray-500);
                min-width: 56px;
                flex-shrink: 0;
            }

            td[data-label="Applicant"]         { margin-bottom:6px; }
            td[data-label="Applicant"]::before { display:none; }

            td[data-label="Actions"]         { display:block; padding-top:10px; padding-left:0; padding-right:0; }
            td[data-label="Actions"]::before { display:none; }

            /* 2×2 button grid */
            .actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                width: 100%;
            }
            .btn {
                padding: 9px 6px;
                font-size: 0.75rem;
                justify-content: center;
                text-align: center;
                white-space: nowrap;
                width: 100%;
            }
        }

        /* ══════════════════════════════════════
           RESPONSIVE – Mobile ≤ 480px
        ══════════════════════════════════════ */
        @media (max-width: 480px) {

            nav { padding:8px 10px; }
            .nav-title { font-size:0.8rem; }
            .nav-logo  { width:28px; height:28px; }
            .btn-logout { padding:6px 9px; font-size:0.7rem; }

            .dash-hero-inner   { padding:18px 12px 38px; }
            .dash-hero-text h1 { font-size:1.2rem; }
            .dash-hero-text p  { font-size:0.78rem; }

            .dash-body { padding:14px 10px 40px; }

            /* Stats: single column horizontal cards */
            .stats-grid { grid-template-columns:1fr; gap:8px; }
            .stat-card  { padding:12px 14px; display:flex; align-items:center; gap:14px; border-radius:12px; }
            .stat-icon  { font-size:1.5rem; margin-bottom:0; flex-shrink:0; }
            .stat-value { font-size:1.4rem; margin-bottom:2px; }
            .stat-label { font-size:0.68rem; }

            .section-label h2 { font-size:1.05rem; }

            tbody tr   { padding:12px 12px 10px; }
            td         { font-size:0.83rem; }
            td::before { min-width:50px; }

            .actions { gap:6px; }
            .btn { padding:8px 4px; font-size:0.7rem; }
        }
    </style>
</head>
<body>

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

<!-- ── HERO ── -->
<div class="dash-hero">
    <div class="dash-hero-inner">
        <div class="dash-hero-text">
            <h1>Manage Enrollees</h1>
            <p>Review, accept, or reject applications. Email notifications are sent automatically upon decision.</p>
        </div>
        <img src="logo.png" alt="SUNN Logo" class="dash-hero-logo">
    </div>
</div>

<!-- ── TOASTS ── -->
<?php if ($notified === 'success'): ?>
    <div class="toast success">✅ Decision saved and email sent successfully.</div>
<?php elseif ($notified === 'error'): ?>
    <div class="toast error">❌ Decision saved but email failed to send.</div>
<?php elseif ($notified === 'deleted'): ?>
    <div class="toast deleted">🗑 Applicant record has been deleted.</div>
<?php endif; ?>

<!-- ── MAIN BODY ── -->
<div class="dash-body">

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <span class="stat-icon">📋</span>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total Applicants</div>
        </div>
        <div class="stat-card pending">
            <span class="stat-icon">⏳</span>
            <div class="stat-value"><?= $stats['Pending'] ?></div>
            <div class="stat-label">Pending Review</div>
        </div>
        <div class="stat-card accepted">
            <span class="stat-icon">✅</span>
            <div class="stat-value"><?= $stats['Accepted'] ?></div>
            <div class="stat-label">Accepted</div>
        </div>
        <div class="stat-card rejected">
            <span class="stat-icon">❌</span>
            <div class="stat-value"><?= $stats['Rejected'] ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <!-- Section Label -->
    <div class="section-label">
        <h2>Applicant Records</h2>
        <div class="line"></div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="🔍  Search applicant name..." value="<?= htmlspecialchars($search) ?>">
        <select name="status">
            <option value="all"      <?= $filter == 'all'      ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending"  <?= $filter == 'pending'  ? 'selected' : '' ?>>⏳ Pending</option>
            <option value="accepted" <?= $filter == 'accepted' ? 'selected' : '' ?>>✅ Accepted</option>
            <option value="rejected" <?= $filter == 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
        </select>
        <button type="submit" class="btn-apply">Apply Filter</button>
    </form>

    <!-- Applicants Table -->
    <div class="table-wrap">
        <div class="table-header-bar">
            <h3>Enrollment Applications</h3>
            <span class="result-count">
                <?php
                $count = 0;
                if ($result) { $count = $result->num_rows; $result->data_seek(0); }
                echo $count . ' record' . ($count !== 1 ? 's' : '');
                ?>
            </span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $fname = decryptData($row['first_name']);
                    $lname = decryptData($row['last_name']);
                    $current_status = $row['status'] ?? 'pending';
                    if ($search && stripos($fname . ' ' . $lname, $search) === false) continue;
                    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
            ?>
            <tr>
                <td data-label="Applicant">
                    <div class="applicant-cell">
                        <div class="avatar"><?= $initials ?></div>
                        <div>
                            <div class="applicant-name"><?= htmlspecialchars($fname . ' ' . $lname) ?></div>
                            <div class="applicant-id">Ref No. <?= htmlspecialchars($row['reference_no'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                </td>
                <td data-label="Status">
                    <span class="badge badge-<?= $current_status ?>"><?= ucfirst($current_status) ?></span>
                </td>
                <td data-label="Actions" class="actions-cell">
                    <div class="actions">
                        <a href="view_applicant.php?id=<?= $row['enrollee_id'] ?>" class="btn btn-view">👁 View</a>
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="enrollee_id" value="<?= $row['enrollee_id'] ?>">
                            <button type="submit" name="action" value="accept" class="btn btn-accept">✓ Accept</button>
                            <button type="submit" name="action" value="reject"  class="btn btn-reject">✕ Reject</button>
                            <button type="submit" name="action" value="delete"  class="btn btn-delete"
                                    onclick="return confirm('Permanently delete this record?');">🗑 Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="3">
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>No applicants found matching your criteria.</p>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>