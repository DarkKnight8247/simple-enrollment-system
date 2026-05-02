<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Consent – SUNN Enrollment</title>
    <link rel="stylesheet" href="../styles/stylesheets/homepage_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="consent-page">

    <header>
        <nav class="navbar">
            <div class="logo-container">
                <img src="../styles/graphics/logo.png" alt="SUNN Logo" class="logo">
                <span class="brand-name">SUNN</span>
            </div>
        </nav>
    </header>

    <!-- Changed hero-section class to consent-hero for better height control -->
    <main class="consent-hero">
        <div class="overlay"></div>
        
        <div class="container consent-wrapper-inner">
            <div class="consent-card">
                <div class="card-header">
                    <p class="motto">"The Future Shines Brightest"</p>
                    <h2>Data Privacy Consent Form</h2>
                </div>

                <div class="consent-body">
                    <p class="intro-text">
                        In compliance with the <strong>Republic Act No. 10173</strong> (Data Privacy Act of 2012), 
                        SUNN is committed to protecting your personal data.
                    </p>

                    <section class="privacy-section">
                        <h3>1. Purpose of Data Collection</h3>
                        <p>Information collected (Name, contact details, academic records) is used solely for the enrollment process.</p>
                    </section>

                    <section class="privacy-section">
                        <h3>2. Data Security</h3>
                        <p>All data is <strong>encrypted</strong> and accessible only to authorized university personnel.</p>
                    </section>

                    <section class="privacy-section">
                        <h3>3. Your Rights</h3>
                        <ul class="obj-list">
                            <li><strong>Informed:</strong> Know how your data is used.</li>
                            <li><strong>Access:</strong> Request updates to your data.</li>
                            <li><strong>Erasure:</strong> Request deletion under specific conditions.</li>
                        </ul>
                    </section>
                </div>

                <form method="GET" action="form.php" class="consent-footer">
                    <label class="checkbox-container">
                        <input type="checkbox" name="consent" value="1" required>
                        I have read and understood the Data Privacy Consent Form.
                    </label>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">I Agree — Proceed</button>
                        <a href="../../index.php" class="btn-link">Go Back</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 State University of Northern Negros. All Rights Reserved.</p>
    </footer>

</body>
</html>