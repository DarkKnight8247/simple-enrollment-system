<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification – SUNN</title>
    <link rel="stylesheet" href="view-status.css">
</head>
<body>

<nav>
    <div class="nav-brand">
        <div class="nav-logo">
            <img src="../../sunn_logo.png" alt="SUNN Logo">
        </div>
        <div>
            <div class="nav-title">SUNN Enrollment System</div>
            <div class="nav-sub">State University of Northern Negros</div>
        </div>
    </div>
    <div class="nav-links">
        <a href="index.php">← Back to Login</a>
    </div>
</nav>

<div class="page-wrapper">

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>OTP Verification</h2>
            <p>A 6-digit code has been sent to your registered email address</p>
        </div>
        <div class="card-body">

            <div class="alert alert-info">
                📧 Check your email inbox for the OTP code. It expires in <strong>5 minutes</strong>.
            </div>

            <form method="POST" action="process_otp.php">
                <div class="form-group">
                    <label>Enter OTP Code <span class="req">*</span></label>
                    <input type="text" name="otp" class="otp-input"
                           placeholder="_ _ _ _ _ _"
                           maxlength="6" pattern="\d{6}"
                           required autocomplete="off" inputmode="numeric">
                    <div class="form-hint">Enter the 6-digit code sent to your email.</div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-full">Verify OTP →</button>
                </div>
            </form>

            <hr>
            <p style="text-align:center; font-size:0.85rem; color:var(--gray-500);">
                Didn't receive a code? <a href="index.php">Go back and try again</a>
            </p>

        </div>
    </div>

</div>

</body>
</html>
