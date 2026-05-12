<?php session_start(); 
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – SUNN Enrollment System</title>
    <link rel="stylesheet" href="sunn-admin.css">
    <style>
        /* ── Ensure the body fills the full viewport on all devices ── */
        html, body { height: 100%; min-height: 100dvh; }

        body.login-body {
            min-height: 100dvh;
            padding: 20px;
            background-attachment: scroll; /* fixed bg can glitch on mobile */
        }

        /* ── Card: fluid on smaller screens ── */
        .page-wrapper.login-container {
            width: min(820px, 100%);
            max-width: 100%;
            min-height: 0;
        }

        /* ── Right panel comfortable on all widths ── */
        .login-panel { padding: 52px 48px; }

        /* ════════════════════════════════════════
           TABLET  ≤ 820px — stack panels vertically
        ════════════════════════════════════════ */
        @media (max-width: 820px) {
            body.login-body {
                align-items: flex-start;
                padding: 24px 16px 40px;
            }
            .page-wrapper.login-container {
                flex-direction: column;
                min-height: 0;
                border-radius: 20px;
            }
            /* Brand panel: horizontal strip at top */
            .brand-panel.login-left {
                width: 100%;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                gap: 18px;
                padding: 24px 28px;
                border-radius: 20px 20px 0 0;
            }
            .logo-container {
                width: 70px;
                margin-bottom: 0;
                flex-shrink: 0;
            }
        .brand-name    { font-size: 1.25rem; text-align: left; }
            .brand-tagline { font-size: 0.72rem; text-align: left; margin-top: 4px; }
            .brand-divider { display: none; }
            .brand-text-block { text-align: left; }

            .login-panel { padding: 36px 32px 44px; width: 100%; }
            .login-panel h1 { font-size: 1.65rem; }
            .subtitle       { font-size: 0.84rem; margin-bottom: 28px; }
        }

        /* ════════════════════════════════════════
           MOBILE  ≤ 520px
        ════════════════════════════════════════ */
        @media (max-width: 520px) {
            body.login-body { padding: 0; align-items: stretch; }

            .page-wrapper.login-container {
                border-radius: 0;
                min-height: 100dvh;
                width: 100%;
            }
            .brand-panel.login-left {
                border-radius: 0;
                padding: 18px 20px;
                gap: 14px;
            }
            .logo-container { width: 52px; }
            .brand-name     { font-size: 1.05rem; }
            .brand-tagline  { display: none; }

            .login-panel    { padding: 28px 22px 40px; }
            .login-panel h1 { font-size: 1.4rem; }
            .subtitle       { margin-bottom: 22px; }
        }

        /* ════════════════════════════════════════
           SMALL MOBILE  ≤ 360px
        ════════════════════════════════════════ */
        @media (max-width: 360px) {
            .brand-panel.login-left { padding: 14px 16px; }
            .logo-container { width: 42px; }
            .brand-name     { font-size: 0.92rem; }
            .login-panel    { padding: 22px 16px 32px; }
            .login-panel h1 { font-size: 1.2rem; }
        }

        /* ════════════════════════════════════════
           LANDSCAPE PHONE — keep it usable
        ════════════════════════════════════════ */
        @media (max-height: 500px) and (orientation: landscape) {
            body.login-body { align-items: flex-start; padding: 16px; }
            .page-wrapper.login-container {
                flex-direction: row;
                min-height: 0;
                border-radius: 16px;
            }
            .brand-panel.login-left {
                width: 34%;
                flex-direction: column;
                padding: 20px 18px;
                border-radius: 16px 0 0 16px;
            }
            .logo-container { width: 56px; margin-bottom: 10px; }
            .brand-name     { font-size: 0.95rem; text-align: center; }
            .brand-tagline  { display: none; }
            .login-panel    { padding: 24px 28px; }
            .login-panel h1 { font-size: 1.3rem; }
            .subtitle       { margin-bottom: 16px; font-size: 0.8rem; }
            .form-group     { margin-bottom: 12px; }
        }
    </style>
</head>
<body class="login-body page-centered">

<div class="page-wrapper login-container">
    <!-- Left Brand Panel -->
<div class="brand-panel login-left">
    <!-- Image first[cite: 5] -->
    <div class="logo-container">
        <img src="logo.png" alt="SUNN Logo" class="official-logo logo-circle">
    </div>
    
    <!-- Text follows -->
    <div class="brand-text-block">
        <div class="brand-name">State University of <br> Northern Negros</div>
        <div class="brand-tagline">Enrollment System</div>
    </div>
    <div class="brand-divider"></div>
</div>
    <!-- Right Login Panel -->
    <div class="login-panel login-right">
        
        <h1>Welcome Back</h1>
        <p class="subtitle">Restricted access — authorized personnel only</p>

        <?php if ($error === 'session'): ?>
            <div class="error-alert">⚠️ Your session has expired. Please log in again.</div>
        <?php endif; ?>

        <form action="process_admin_login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Login to Admin Panel</button>
        </form>
        <a class="back-link" href="../../index.php">Back to Home</a>
    </div>
</div>

</body>
</html>