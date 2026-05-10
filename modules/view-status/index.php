<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Enrollment Status – SUNN</title>
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
    </div>
</nav>

<div class="page-wrapper">

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Check Enrollment Status</h2>
            <p>Enter your reference number to view or manage your application</p>
        </div>
        <div class="card-body">

            <div class="ref-box">
                <div class="ref-label">Reference Number Format</div>
                <div class="ref-number" style="font-size:1.1rem; letter-spacing:0.15em;">e.g. &nbsp; A3F9B21C</div>
            </div>

            <form method="POST" action="process_login.php">
                <div class="form-group">
                    <label>Reference Number <span class="req">*</span></label>
                    <input type="text" name="reference_no" placeholder="Enter your reference number"
                           maxlength="30" required autocomplete="off" style="text-transform:uppercase;">
                    <div class="form-hint">Your reference number was sent to your email upon enrollment.</div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-full">Check Status →</button>
                </div>
            </form>

            <hr>
            <p style="text-align:center; font-size:0.85rem; color:var(--gray-500);">
                Haven't enrolled yet? <a href="../register/form.php">Apply here</a>
            </p>

        </div>
    </div>

</div>

</body>
</html>
