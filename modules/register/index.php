<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#fbbf24">
    <title>Data Privacy Consent – SUNN Enrollment</title>

    <!-- External shared stylesheet (keep your existing link) -->
    <link rel="stylesheet" href="../styles/stylesheets/homepage_style.css">

    <style>
    /*
     * ============================================================
     *  SUNN — Consent Page Responsive Overrides
     *  These rules layer on top of homepage_style.css.
     *  Mobile-first approach; breakpoints mirror form.css.
     *  ============================================================
     *
     *  Breakpoints:
     *   Mobile  : 320px – 480px  (base — no query needed)
     *   Tablet  : 481px – 768px  (min-width: 481px)
     *   Laptop  : 769px – 1024px (min-width: 769px)
     *   Desktop : 1025px+        (min-width: 1025px)
     * ============================================================
     */

    /* ── Design tokens (match form.css) ─────────────────────── */
    :root {
        --sunn-gold:        #fbbf24;
        --sunn-gold-dark:   #d97706;
        --sunn-gold-light:  #fcd34d;
        --sunn-green:       #3f9142;
        --sunn-green-light: rgba(63, 145, 66, 0.12);
        --white:            #ffffff;
        --text-dark:        #1e293b;
        --text-mid:         #334155;
        --text-muted:       #64748b;
        --border-color:     #e2e8f0;
        --card-bg:          #ffffff;
        --body-bg-overlay:  rgba(15, 30, 55, 0.60);
    }

    /* ── Global resets (safe overrides) ─────────────────────── */
    *,
    *::before,
    *::after { box-sizing: border-box; }

    html {
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
        scroll-behavior: smooth;
        height: 100%;
    }

    /* ── Page layout ─────────────────────────────────────────── */
    body.consent-page {
        min-height: 100vh;
        min-height: 100dvh; /* dynamic viewport height — accounts for mobile browser chrome */
        width: 100%;
        overflow-x: hidden;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        font-size: 16px;
        display: flex;
        flex-direction: column;
        background:
            linear-gradient(var(--body-bg-overlay), var(--body-bg-overlay)),
            url('../styles/graphics/bg.jpg') no-repeat center center / cover scroll;
    }

    /* ── Sticky nav ──────────────────────────────────────────── */
    body.consent-page header {
        position: sticky;
        top: 0;
        z-index: 100;
        background: rgba(11, 31, 58, 0.96);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-bottom: 2px solid var(--sunn-gold);
        flex-shrink: 0;
    }

    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* Fluid padding: tight on mobile, generous on desktop */
        padding: 10px clamp(12px, 4vw, 40px);
        min-height: 56px;
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
    }

    .logo-container .logo {
        /* Responsive logo size */
        width: clamp(32px, 8vw, 52px);
        height: auto;
        object-fit: contain;
        display: block;
        /* Prevent layout shift */
        aspect-ratio: 1;
    }

    .brand-name {
        font-size: clamp(1rem, 4vw, 1.5rem);
        font-weight: 700;
        color: var(--sunn-gold);
        letter-spacing: 0.08em;
        white-space: nowrap;
    }

    /* ── Hero / main content area ────────────────────────────── */
    .consent-hero {
        flex: 1 0 auto;          /* grow to fill space, never shrink below content */
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: clamp(20px, 4vw, 56px) clamp(10px, 3vw, 24px);
        /* Let content determine height — no artificial min-height here */
        padding-bottom: clamp(28px, 5vw, 60px);
    }

    /* Decorative overlay (if used in homepage_style.css) */
    .consent-hero .overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }

    /* ── Consent card wrapper ─────────────────────────────────── */
    .consent-wrapper-inner {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 680px;
        /* Prevent card from touching viewport edges */
        padding: 0;
    }

    /* ── Card ────────────────────────────────────────────────── */
    .consent-card {
        background: var(--card-bg);
        border-radius: clamp(12px, 3vw, 20px);
        overflow: hidden;
        box-shadow:
            0 4px 24px rgba(0,0,0,0.18),
            0 0 0 3px var(--sunn-gold);
        width: 100%;
    }

    /* ── Card header ─────────────────────────────────────────── */
    .card-header {
        background: linear-gradient(135deg, #0b1f3a 0%, #142d52 100%);
        padding: clamp(16px, 4vw, 32px) clamp(16px, 4vw, 40px);
        text-align: center;
        border-bottom: 3px solid var(--sunn-gold);
    }

    .card-header .motto {
        color: var(--sunn-gold);
        font-size: clamp(0.65rem, 2.2vw, 0.82rem);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: clamp(1px, 0.3vw, 2px);
        margin-bottom: 6px;
    }

    .card-header h2 {
        color: var(--white);
        font-size: clamp(1rem, 4vw, 1.5rem);
        font-weight: 700;
        line-height: 1.3;
        /* Allow wrapping on tiny phones */
        word-break: break-word;
    }

    /* ── Card body ───────────────────────────────────────────── */
    .consent-body {
        padding: clamp(16px, 4vw, 32px) clamp(16px, 4vw, 40px);
        border-bottom: 1px solid var(--border-color);
    }

    .intro-text {
        font-size: clamp(0.82rem, 2.6vw, 0.95rem);
        color: var(--text-mid);
        line-height: 1.7;
        margin-bottom: clamp(12px, 3vw, 20px);
    }

    /* ── Privacy sections ────────────────────────────────────── */
    .privacy-section {
        margin-top: clamp(12px, 3vw, 20px);
        padding: clamp(10px, 2.5vw, 16px);
        background: #f8fafc;
        border-radius: 10px;
        border-left: 4px solid var(--sunn-gold);
    }

    .privacy-section h3 {
        color: var(--sunn-green);
        font-size: clamp(0.78rem, 2.5vw, 0.92rem);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 6px;
    }

    .privacy-section p {
        font-size: clamp(0.78rem, 2.4vw, 0.9rem);
        color: var(--text-mid);
        line-height: 1.65;
    }

    /* ── Rights list ─────────────────────────────────────────── */
    .obj-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .obj-list li {
        font-size: clamp(0.78rem, 2.4vw, 0.88rem);
        color: var(--text-mid);
        line-height: 1.6;
        padding-left: 20px;
        position: relative;
    }

    .obj-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: var(--sunn-green);
        font-weight: 700;
    }

    /* ── Card footer (form) ──────────────────────────────────── */
    .consent-footer {
        padding: clamp(16px, 4vw, 28px) clamp(16px, 4vw, 40px);
        /* Extra bottom padding so the fixed page footer never overlaps buttons */
        padding-bottom: clamp(24px, 5vw, 36px);
        background: #fafbfc;
        /* Ensure footer stays within card flow, not clipped */
        position: relative;
        z-index: 2;
    }

    /* ── Checkbox row ────────────────────────────────────────── */
    .checkbox-container {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: clamp(0.82rem, 2.6vw, 0.95rem);
        color: var(--text-mid);
        line-height: 1.6;
        cursor: pointer;
        /* Minimum touch target height */
        min-height: 44px;
    }

    .checkbox-container input[type="checkbox"] {
        /* Fixed size — never shrink */
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin-top: 2px;
        accent-color: var(--sunn-green);
        cursor: pointer;
        border-radius: 4px;
    }

    /* ── Action buttons row ──────────────────────────────────── */
    .form-actions {
        display: flex;
        flex-direction: column;   /* Stack on mobile */
        gap: 10px;
        margin-top: clamp(12px, 3vw, 20px);
    }

    /* Primary "I Agree" button */
    .btn.btn-primary,
    button.btn-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 52px;
        padding: 14px clamp(12px, 3vw, 24px);
        background: var(--sunn-gold);
        color: #000;
        font-size: clamp(0.88rem, 3vw, 1rem);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 0 var(--sunn-gold-dark);
        text-decoration: none;
        transition: background 0.2s, transform 0.15s, box-shadow 0.15s;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .btn.btn-primary:hover,
    button.btn-primary:hover {
        background: var(--sunn-gold-light);
        transform: translateY(-2px);
        box-shadow: 0 6px 0 var(--sunn-gold-dark);
    }

    .btn.btn-primary:active,
    button.btn-primary:active {
        transform: translateY(2px);
        box-shadow: none;
    }

    /* "Go Back" link styled as a secondary button */
    .btn-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        padding: 12px clamp(12px, 3vw, 24px);
        background: transparent;
        color: var(--text-muted);
        font-size: clamp(0.82rem, 2.6vw, 0.9rem);
        font-weight: 600;
        text-decoration: none;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        text-align: center;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .btn-link:hover {
        border-color: var(--sunn-green);
        color: var(--sunn-green);
        background: var(--sunn-green-light);
    }

    /* ── Footer ──────────────────────────────────────────────── */
    body.consent-page footer {
        /* flex-shrink: 0 keeps it in normal document flow at the
           very bottom of the flex column — never overlaps content */
        flex-shrink: 0;
        margin-top: auto; /* pushes footer to bottom when content is short */
        position: relative;
        z-index: 10;
        background: #0b1f3a;
        border-top: 2px solid var(--sunn-gold);
        padding: clamp(12px, 2.5vw, 18px) clamp(16px, 4vw, 40px);
        text-align: center;
    }

    body.consent-page footer p {
        color: #6b7d96;
        font-size: clamp(0.65rem, 2vw, 0.78rem);
        line-height: 1.5;
        margin: 0;
    }

    /* ══════════════════════════════════════════════════════════
       BREAKPOINT 1 — Tablet: 481px – 768px
       ══════════════════════════════════════════════════════════ */
    @media (min-width: 481px) {
        .form-actions {
            /* Side-by-side buttons on tablet+ */
            flex-direction: row;
            align-items: center;
        }

        .btn.btn-primary,
        button.btn-primary {
            flex: 1;
        }

        .btn-link {
            flex: 0 0 auto;
            width: auto;
            min-width: 130px;
        }

        .consent-hero {
            align-items: center;
        }
    }

    /* ══════════════════════════════════════════════════════════
       BREAKPOINT 2 — Small Laptop: 769px – 1024px
       ══════════════════════════════════════════════════════════ */
    @media (min-width: 769px) {
        .navbar {
            min-height: 64px;
        }

        .consent-card {
            box-shadow:
                0 12px 48px rgba(0,0,0,0.22),
                0 0 0 4px var(--sunn-gold);
        }

        .obj-list {
            gap: 8px;
        }
    }

    /* ══════════════════════════════════════════════════════════
       BREAKPOINT 3 — Desktop: 1025px+
       ══════════════════════════════════════════════════════════ */
    @media (min-width: 1025px) {
        .consent-hero {
            align-items: center;
        }

        .consent-wrapper-inner {
            max-width: 700px;
        }

        .consent-card {
            box-shadow:
                0 24px 80px rgba(0,0,0,0.28),
                0 0 0 5px var(--sunn-gold);
        }

        .card-header h2 {
            font-size: 1.55rem;
        }
    }

    /* ══════════════════════════════════════════════════════════
       UTILITY — Very small phones (320px)
       ══════════════════════════════════════════════════════════ */
    @media (max-width: 359px) {
        .consent-hero {
            padding: 10px 6px;
        }

        .card-header h2 {
            font-size: 0.95rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-link {
            width: 100%;
        }
    }

    /* ══════════════════════════════════════════════════════════
       UTILITY — Landscape phones
       ══════════════════════════════════════════════════════════ */
    @media (max-height: 500px) and (orientation: landscape) {
        .consent-hero {
            padding: 10px clamp(10px, 3vw, 20px);
            align-items: flex-start;
        }

        .navbar {
            min-height: 48px;
        }
    }

    </style>
</head>
<body class="consent-page">

    <!-- ── Navigation ──────────────────────────────────────── -->
    <header>
        <nav class="navbar">
            <div class="logo-container">
                <img src="../styles/graphics/logo.png" alt="SUNN Logo" class="logo">
                <span class="brand-name">SUNN</span>
            </div>
        </nav>
    </header>

    <!-- ── Main Content ────────────────────────────────────── -->
    <main class="consent-hero">
        <div class="overlay" aria-hidden="true"></div>

        <div class="consent-wrapper-inner">
            <div class="consent-card" role="main">

                <!-- Card header -->
                <div class="card-header">
                    <p class="motto">"The Future Shines Brightest"</p>
                    <h2>Data Privacy Consent Form</h2>
                </div>

                <!-- Card body: privacy notice content -->
                <div class="consent-body">
                    <p class="intro-text">
                        In compliance with <strong>Republic Act No. 10173</strong>
                        (Data Privacy Act of 2012), SUNN is committed to protecting
                        your personal data.
                    </p>

                    <section class="privacy-section">
                        <h3>1. Purpose of Data Collection</h3>
                        <p>
                            Information collected (name, contact details, academic
                            records) is used solely for the enrollment process.
                        </p>
                    </section>

                    <section class="privacy-section">
                        <h3>2. Data Security</h3>
                        <p>
                            All data is <strong>encrypted</strong> and accessible
                            only to authorized university personnel.
                        </p>
                    </section>

                    <section class="privacy-section">
                        <h3>3. Your Rights</h3>
                        <ul class="obj-list" role="list">
                            <li><strong>Informed:</strong> Know how your data is used.</li>
                            <li><strong>Access:</strong> Request updates to your data.</li>
                            <li><strong>Erasure:</strong> Request deletion under specific conditions.</li>
                        </ul>
                    </section>
                </div><!-- /.consent-body -->

                <!-- Card footer: checkbox + actions -->
                <form method="GET" action="form.php" class="consent-footer">

                    <label class="checkbox-container">
                        <input
                            type="checkbox"
                            name="consent"
                            value="1"
                            required
                            aria-required="true">
                        <span>I have read and understood the Data Privacy Consent Form.</span>
                    </label>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            I Agree — Proceed
                        </button>
                        <a href="../../index.php" class="btn-link">
                            Go Back
                        </a>
                    </div>

                </form><!-- /.consent-footer -->

            </div><!-- /.consent-card -->
        </div><!-- /.consent-wrapper-inner -->

    </main>

    <!-- ── Footer ──────────────────────────────────────────── -->
    <footer>
        <p>&copy; 2026 State University of Northern Negros. All Rights Reserved.</p>
    </footer>

</body>
</html>
