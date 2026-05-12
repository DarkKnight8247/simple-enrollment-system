<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!--
        viewport: width=device-width prevents mobile browsers from using a
        virtual 980px viewport. initial-scale=1 sets 1:1 pixel ratio.
        maximum-scale=5 allows pinch-zoom for accessibility.
    -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <!-- Android Chrome toolbar colour -->
    <meta name="theme-color" content="#fbbf24">
    <title>SUNN Enrollment System</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>

<div class="container">

    <!-- ── Header ────────────────────────────────────────────── -->
    <div class="header">
        <img src="logo.png" alt="SUNN Logo" class="logo">
        <h1>STATE UNIVERSITY OF NORTHERN NEGROS</h1>
        <p class="tagline">The Future Shines Brightest</p>
        <div class="divider"></div>
        <h2>STUDENT REGISTRATION FORM</h2>
    </div>

    <!-- ── Form ──────────────────────────────────────────────── -->
    <div class="content">
        <form method="POST" action="process_form.php" novalidate>

            <!-- ═══ SECTION 1: Personal Information ══════════ -->
            <h3>Personal Information</h3>

            <!--
                .name-row becomes a 2-column CSS Grid on tablet+
                (defined in form.css via .name-row grid rules).
                On mobile it stays single-column automatically.
            -->
            <div class="name-row">
                <div class="field">
                    <label for="first_name">First Name <span>*</span></label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="e.g. Maria"
                        autocomplete="given-name"
                        inputmode="text"
                        required>
                </div>

                <div class="field">
                    <label for="middle_name">Middle Name</label>
                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        placeholder="Optional"
                        autocomplete="additional-name"
                        inputmode="text">
                </div>
            </div>

            <label for="last_name">Last Name <span>*</span></label>
            <input
                type="text"
                id="last_name"
                name="last_name"
                placeholder="e.g. Santos"
                autocomplete="family-name"
                inputmode="text"
                required>

            <!--
                .field-row-2 puts Date of Birth and Sex side-by-side
                on tablet+ screens; stacks on mobile.
            -->
            <div class="field-row-2">
                <div class="field">
                    <label for="birthdate">Date of Birth <span>*</span></label>
                    <input
                        type="date"
                        id="birthdate"
                        name="birthdate"
                        autocomplete="bdate"
                        required>
                </div>

                <div class="field">
                    <label for="sex">Sex <span>*</span></label>
                    <select id="sex" name="sex" required>
                        <option value="" disabled selected hidden>— Select Sex —</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <label for="civil_status">Civil Status <span>*</span></label>
            <select id="civil_status" name="civil_status" required>
                <option value="" disabled selected hidden>— Select Civil Status —</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
            </select>

            <div class="section-divider"></div>

            <!-- ═══ SECTION 2: Contact Information ═══════════ -->
            <h3>Contact Information</h3>

            <label for="email">Email Address <span>*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="your@email.com"
                autocomplete="email"
                inputmode="email"
                required>

            <label for="phone">Phone Number <span>*</span></label>
            <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="09XXXXXXXXX"
                pattern="^09\d{9}$"
                maxlength="11"
                autocomplete="tel-national"
                inputmode="numeric"
                required>
            <small>Format: 09XXXXXXXXX (11 digits)</small>

            <label for="address">Complete Address <span>*</span></label>
            <textarea
                id="address"
                name="address"
                placeholder="Street, Barangay, City/Municipality, Province"
                autocomplete="street-address"
                required></textarea>

            <div class="section-divider"></div>

            <!-- ═══ SECTION 3: Emergency Contact ════════════ -->
            <h3>Emergency Contact</h3>

            <label for="guardian_name">Guardian Name <span>*</span></label>
            <input
                type="text"
                id="guardian_name"
                name="guardian_name"
                placeholder="e.g. Juan Santos"
                inputmode="text"
                required>

            <label for="guardian_phone">Guardian Phone Number <span>*</span></label>
            <input
                type="tel"
                id="guardian_phone"
                name="guardian_phone"
                placeholder="09XXXXXXXXX"
                pattern="^09\d{9}$"
                maxlength="11"
                inputmode="numeric"
                required>
            <small>Format: 09XXXXXXXXX (11 digits)</small>

            <label for="guardian_address">Guardian Address <span>*</span></label>
            <textarea
                id="guardian_address"
                name="guardian_address"
                placeholder="Street, Barangay, City/Municipality, Province"
                required></textarea>

            <div class="section-divider"></div>

            <!-- ═══ SECTION 4: Academic Information ══════════ -->
            <h3>Academic Information</h3>

            <label for="course_id">Preferred Course <span>*</span></label>
            <select id="course_id" name="course_id" required>
                <option value="" disabled selected hidden>— Select Course —</option>
                <optgroup label="College of Computing">
                    <option value="1">BS Computer Science</option>
                    <option value="2">BS Information Technology</option>
                    <option value="3">BS Information Systems</option>
                </optgroup>
                <optgroup label="College of Engineering">
                    <option value="4">BS Civil Engineering</option>
                    <option value="5">BS Electrical Engineering</option>
                    <option value="6">BS Mechanical Engineering</option>
                </optgroup>
                <optgroup label="College of Health Sciences">
                    <option value="7">BS Nursing</option>
                    <option value="8">BS Pharmacy</option>
                    <option value="9">BS Physical Therapy</option>
                </optgroup>
                <optgroup label="College of Business">
                    <option value="10">BS Accountancy</option>
                    <option value="11">BS Business Administration</option>
                    <option value="12">BS Tourism Management</option>
                </optgroup>
                <optgroup label="College of Education">
                    <option value="13">Bachelor of Elementary Education</option>
                    <option value="14">Bachelor of Secondary Education</option>
                </optgroup>
            </select>

            <div class="field-row-2">
                <div class="field">
                    <label for="year_level">Year Level <span>*</span></label>
                    <select id="year_level" name="year_level" required>
                        <option value="" disabled selected hidden>— Select Year —</option>
                        <option value="1st Year">1st Year (Freshmen)</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="Transferee">Transferee</option>
                        <option value="Shiftee">Shiftee</option>
                    </select>
                </div>

                <div class="field">
                    <label for="gpa">General Weighted Average (GPA)</label>
                    <input
                        type="number"
                        id="gpa"
                        name="gpa"
                        step="0.01"
                        min="1"
                        max="100"
                        placeholder="e.g. 92 or 1.25"
                        inputmode="decimal">
                </div>
            </div>

            <label for="previous_school">Previous School / Last School Attended</label>
            <input
                type="text"
                id="previous_school"
                name="previous_school"
                placeholder="e.g. Sagay National High School"
                inputmode="text">

            <p class="disclaimer">
                <small>By submitting, you confirm that all information provided is accurate and truthful.</small>
            </p>

            <button type="submit">Submit Application →</button>

        </form>

        <!-- ── Dashboard return link (outside the form) ─────── -->
        <a href="../../index.php" class="btn-dashboard">
            ← Go Back
        </a>

    </div><!-- /.content -->

</div><!-- /.container -->

</body>
</html>
