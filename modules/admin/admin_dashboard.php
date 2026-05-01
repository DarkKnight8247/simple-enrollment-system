<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../../cryptograph_process.php';

$conn = new mysqli('localhost', 'root', '', 'sunn_enrollment');

// 1. Decision Making Logic: Handle Accept/Reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['enrollee_id'];
    $action = $_POST['action'];
    
    if ($action === 'accept') {
        $status = 'accepted';
    } else {
        $status = 'rejected';
    }
    
    // Update the enrollee status in the database
    $stmt = $conn->prepare("UPDATE enrollee SET status = ? WHERE enrollee_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// 2. Fetching Logic with Search and Filter
$search = $_GET['search'] ?? '';
$filter = $_GET['status'] ?? 'all';

$query = "SELECT * FROM enrollee WHERE 1=1";
if ($filter !== 'all') { 
    $query .= " AND status = '" . $conn->real_escape_string($filter) . "'"; 
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Enrollees</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; }
        .container { padding: 40px; }
        .status-accepted { color: #4ade80; font-weight: bold; }
        .status-rejected { color: #f87171; font-weight: bold; }
        .status-pending { color: #fbbf24; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; }
        th, td { padding: 15px; border-bottom: 1px solid #334155; text-align: left; }
        button { cursor: pointer; border: none; padding: 8px 12px; border-radius: 4px; font-weight: 600; }
        .btn-accept { background: #16a34a; color: white; margin-right: 5px; }
        .btn-reject { background: #dc2626; color: white; }
        .btn-notify { background: #2563eb; color: white; margin-left: 10px; }
    </style>
</head>
<body>
<nav style="display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: #1e293b; border-bottom: 1px solid #334155;">
    <div style="font-weight: bold; font-size: 1.2rem;">SUNN Admin Dashboard</div>
    <div>
        <!-- Link to the logout script[cite: 3] -->
        <a href="admin_logout.php" style="background: #dc2626; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">
            Logout ⏻
        </a>
    </div>
</nav>
<div class="container">
    <h2>Manage Enrollees</h2>

    <!-- Filter and Search Bar[cite: 15] -->
    <form method="GET" style="margin-bottom: 30px;">
        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">
        <select name="status">
            <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Statuses</option>
            <option value="pending" <?= $filter == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= $filter == 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="rejected" <?= $filter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit" style="background: #334155; color: white;">Apply</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Applicant Name</th>
                <th>Current Status</th>
                <th>Action / Finalization</th>
            </tr>
        </thead>
        <tbody>
          <?php 
if ($result):
    while($row = $result->fetch_assoc()): 
        // 1. Decrypt Names for display[cite: 7, 15]
        $fname = decryptData($row['first_name']);
        $lname = decryptData($row['last_name']);
        
        // 2. DEFINE THE KEY: Use ?? 'pending' as a safety backup
        $current_status = $row['status'] ?? 'pending'; 

        // 3. Search Filter[cite: 15]
        if ($search && stripos($fname . " " . $lname, $search) === false) continue;
?>
<tr>
    <td><?= htmlspecialchars($fname . " " . $lname) ?></td>
    
    <!-- Fix for line 94: Use the safe variable we defined above[cite: 15] -->
    <td class="status-<?= $current_status ?>"><?= ucfirst($current_status) ?></td>
    
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="enrollee_id" value="<?= $row['enrollee_id'] ?>">
            <button type="submit" name="action" value="accept" class="btn-accept">Accept</button>
            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
        </form>

        <!-- Fix for line 104: Button only shows if decision is made[cite: 15] -->
        <?php if ($current_status !== 'pending'): ?>
    <form action="process_notify.php" method="POST" style="display:inline;">
        <input type="hidden" name="enrollee_id" value="<?= $row['enrollee_id'] ?>">
        <button type="submit" class="btn-notify">Send Notification Email</button>
    </form>
<?php endif; ?>
    </td>
</tr>
<?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>