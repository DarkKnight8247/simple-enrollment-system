<?php
require_once 'db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNN Enrollment System</title>
    <!-- Updated path to your stylesheet -->
    <link rel="stylesheet" href="modules/styles/stylesheets/homepage_style.css">
    <style>
    /* ============================================================
       SUNN Homepage — Responsive Layer (mobile-first)
       Sits on top of homepage_style.css. Does NOT change colours,
       fonts, or design intent.
       Breakpoints:
         Mobile  : 320px–480px  (base)
         Tablet  : 481px–768px  (min-width: 481px)
         Laptop  : 769px–1024px (min-width: 769px)
         Desktop : 1025px+      (min-width: 1025px)
    ============================================================ */

    /* ── Safe global resets ── */
    *, *::before, *::after { box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; height: 100%; }
    body { overflow-x: hidden; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; }
    img  { max-width: 100%; height: auto; display: block; }

    /* ── NAVBAR ── */
    header { position: sticky; top: 0; z-index: 100; flex-shrink: 0; }

    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 clamp(14px, 4vw, 48px);
        min-height: clamp(52px, 8vw, 68px);
        gap: 12px;
        flex-wrap: nowrap;
    }

    .logo-container { display: flex; align-items: center; gap: clamp(8px, 2vw, 14px); flex-shrink: 0; }

    .logo { width: clamp(30px, 7vw, 48px); height: auto; aspect-ratio: 1; object-fit: contain; }

    .brand-name { font-size: clamp(1rem, 3.5vw, 1.45rem); white-space: nowrap; }

    .nav-links { display: flex; align-items: center; gap: clamp(8px, 2vw, 20px); flex-shrink: 0; }

    .admin-link {
        white-space: nowrap;
        font-size: clamp(0.72rem, 2vw, 0.85rem);
        padding: clamp(7px, 1.5vw, 10px) clamp(12px, 2.5vw, 22px);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
    }

    /* ── Hamburger button ── */
    .hamburger {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 40px;
        height: 40px;
        padding: 6px;
        background: none;
        border: none;
        cursor: pointer;
        flex-shrink: 0;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
    .hamburger span {
        display: block;
        width: 100%;
        height: 2.5px;
        background: currentColor;
        border-radius: 2px;
        transition: transform .25s ease, opacity .2s ease;
        transform-origin: center;
    }
    .hamburger.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
    .hamburger.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

    /* ── HERO ── */
    .hero-section {
        flex: 1 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(32px, 6vw, 80px) clamp(14px, 4vw, 48px);
        position: relative;
        background-attachment: scroll;
    }

    .overlay { position: absolute; inset: 0; pointer-events: none; z-index: 0; }

    /* ── Content wrapper ── */
    .content-wrapper {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1200px;
        display: flex;
        flex-direction: column;
        gap: clamp(24px, 4vw, 40px);
        align-items: stretch;
    }

    /* ── Intro ── */
    .intro { display: flex; flex-direction: column; gap: clamp(10px, 2vw, 16px); }

    .intro h1 { font-size: clamp(1.5rem, 5.5vw, 3.4rem); line-height: 1.15; word-break: break-word; margin: 0; }

    .motto    { font-size: clamp(0.68rem, 2.2vw, 0.82rem); margin: 0; }
    .subtitle { font-size: clamp(0.82rem, 2.5vw, 1.05rem); margin: 0; }

    .description { font-size: clamp(0.82rem, 2.2vw, 0.96rem); line-height: 1.75; margin: 0; max-width: 600px; }

    /* ── CTA buttons ── */
    .cta-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(10px, 2vw, 16px);
        margin-top: clamp(6px, 1.5vw, 12px);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: clamp(12px, 2vw, 15px) clamp(22px, 4vw, 38px);
        font-size: clamp(0.85rem, 2.2vw, 0.97rem);
        min-height: 48px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        white-space: nowrap;
    }

    /* ── Info card ── */
    .info-card { padding: clamp(20px, 4vw, 36px) clamp(18px, 4vw, 36px); }

    .info-card h3 { font-size: clamp(0.9rem, 2.5vw, 1.1rem); margin: 0 0 clamp(12px, 2.5vw, 20px); }

    .obj-list { padding: 0; margin: 0; display: flex; flex-direction: column; gap: clamp(8px, 2vw, 14px); }

    .obj-list li { font-size: clamp(0.80rem, 2.2vw, 0.92rem); line-height: 1.65; }

    /* ── Footer ── */
    footer { flex-shrink: 0; margin-top: auto; }
    footer p { font-size: clamp(0.65rem, 2vw, 0.78rem); margin: 0; }

    /* ============================================================
       MOBILE <= 480px — hamburger on, nav drawer
    ============================================================ */
    @media (max-width: 480px) {
        .hamburger { display: flex; }

        .nav-links {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            flex-direction: column;
            align-items: stretch;
            padding: 12px 16px 20px;
            gap: 10px;
            z-index: 99;
        }
        .nav-links.open { display: flex; }

        .admin-link { justify-content: center; padding: 13px 20px; font-size: 0.9rem; }

        .hero-section { padding: 36px 16px 40px; min-height: unset; }

        .cta-buttons { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
    }

    /* ============================================================
       TABLET 481px – 768px
    ============================================================ */
    @media (min-width: 481px) {
        .content-wrapper { flex-direction: row; align-items: flex-start; }
        .intro            { flex: 1 1 55%; min-width: 0; }
        .info-card        { flex: 1 1 40%; min-width: 0; align-self: flex-start; }
        .cta-buttons      { flex-direction: column; align-items: flex-start; }
        .btn              { width: auto; }
    }

    /* ============================================================
       LAPTOP 769px – 1024px
    ============================================================ */
    @media (min-width: 769px) {
        .hamburger   { display: none !important; }
        .nav-links   { display: flex !important; }
        .cta-buttons { flex-direction: row; }
    }

    /* ============================================================
       DESKTOP 1025px+
    ============================================================ */
    @media (min-width: 1025px) {
        .content-wrapper { gap: 56px; align-items: center; }
        .intro     { flex: 1 1 58%; }
        .info-card { flex: 1 1 38%; }
    }

    /* ============================================================
       VERY SMALL <= 359px
    ============================================================ */
    @media (max-width: 359px) {
        .navbar       { padding: 0 10px; }
        .intro h1     { font-size: 1.4rem; }
        .hero-section { padding: 28px 12px 32px; }
    }

    /* ============================================================
       LANDSCAPE PHONES
    ============================================================ */
    @media (max-height: 500px) and (orientation: landscape) {
        .hero-section    { padding: 20px 16px; min-height: unset; }
        .content-wrapper { flex-direction: row; align-items: flex-start; gap: 20px; }
        .intro           { flex: 1; }
        .info-card       { flex: 1; }
        .intro h1        { font-size: 1.5rem; }
    }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <div class="logo-container">
                <!-- Updated path to your logo -->
                <img src="modules/styles/graphics/logo.png" alt="SUNN Logo" class="logo">
                <span class="brand-name">SUNN</span>
            </div>
            
            <!-- Hamburger (visible on mobile only) -->
            <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-expanded="false" aria-controls="nav-links">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="nav-links">
                <a href="modules/admin/index.php" class="admin-link">Admin Dashboard</a>
            </div>
        </nav>
    </header>

    <main class="hero-section">
        <div class="overlay"></div>
        
        <div class="content-wrapper">
            <section class="intro">
                <p class="motto">"The Future Shines Brightest"</p>
                <h1>State University of Northern Negros</h1>
                <p class="subtitle">Official Enrollment Portal</p>
                <p class="description">
                    Welcome to the SUNN digital gateway. We are committed to fostering academic excellence 
                    through a streamlined, student-centered registration experience.
                </p>
                
                <div class="cta-buttons">
                    <a href="modules/register/index.php" class="btn btn-primary">Enroll Now!</a>
                    <a href="modules/view-status/index.php" class="btn btn-secondary">View Status</a>
                </div>
            </section>

            <section class="info-card">
                <h3>Our Objectives</h3>
                <ul class="obj-list">
                    <li><strong>Efficiency:</strong> Minimize waiting times with automated processing.</li>
                    <li><strong>Innovation:</strong> Utilize modern tech to simplify academic transitions.</li>
                    <li><strong>Reliability:</strong> Provide a secure platform for all student data.</li>
                </ul>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 State University of Northern Negros. All Rights Reserved.</p>
    </footer>


    <script>
    (function () {
        var btn = document.getElementById('hamburger');
        var nav = document.getElementById('nav-links');
        if (!btn || !nav) return;
        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            btn.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open);
        });
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                nav.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            });
        });
        document.addEventListener('click', function (e) {
            if (!btn.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    })();
    </script>
</body>
</html>