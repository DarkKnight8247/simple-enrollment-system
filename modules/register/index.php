<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Consent – SUNN Enrollment</title>
    <!-- Path to your homepage stylesheet -->
    <link rel="stylesheet" href="modules/styles/stylesheets/homepage_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="consent-page">

    <header>
        <nav class="navbar">
            <div class="logo-container">
                <img src="modules/styles/graphics/logo.png" alt="SUNN Logo" class="logo">
                <span class="brand-name">SUNN</span>
            </div>
        </nav>
    </header>

    <main class="hero-section">
        <div class="overlay"></div>
        
        <div class="container relative-z">
            <div class="consent-card">
                <div class="card-header">
                    <p class="motto">Compliance with RA 10173</p>
                    <h2>Data Privacy Consent Form</h2>
                    <p class="subtitle">State University of Northern Negros</p>
                </div>

                <div class="consent-body">
                    <p>In compliance with the <strong>Data Privacy Act of 2012</strong>, SUNN is committed to protecting your personal data. Please read the following carefully before proceeding.</p>

                    <div class="privacy-grid">
                        <div class="privacy-item">
                            <h3>1. Purpose of Data Collection</h3>
                            <p>We collect information such as your full name, contact details, emergency contacts, and academic background solely for processing your enrollment application.</p>
                        </div>

                        <div class="privacy-item">
                            <h3>2. Data Security</h3>
                            <p>All sensitive information is <strong>encrypted</strong>. Access is strictly limited to authorized university personnel only.</p>
                        </div>
                    </div>

                    <div class="rights-section">
                        <h3>Your Rights under the Law</h3>
                        <ul class="obj-list">
                            <li><strong>Be Informed:</strong> Know how your data is used.</li>
                            <li><strong>Access & Correct:</strong> Request updates to your information.</li>
                            <li><strong>Object & Erase:</strong> Object to processing or request deletion.</li>
                        </ul>
                    </div>

                    <div class="contact-box">
                        <p><strong>Contact our Data Protection Officer:</strong> <a href="mailto:dpo@sunn.edu.ph">dpo@sunn.edu.ph</a></p>
                    </div>
                </div>

                <form method="GET" action="form.php" class="consent-footer">
                    <label class="checkbox-container">
                        <input type="checkbox" name="consent" value="1" required>
                        <span class="checkmark"></span>
                        I have read and understood the Data Privacy Consent Form. I voluntarily give my consent to SUNN to process my personal data.
                    </label>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">I Agree — Proceed</button>
                        <a href="../../index.php" class="btn-link">I Do Not Agree — Go Back</a>
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