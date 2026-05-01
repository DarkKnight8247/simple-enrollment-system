<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNN Enrollment System</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>

<div class="container">
    <!-- Header Section -->
    <div class="header">
        <img src="logo.png" alt="SUNN Logo" class="logo">
        <h1>STATE UNIVERSITY OF NORTHERN NEGROS</h1>
        <p class="tagline">The Future Shines Brightest</p>
        <div class="divider"></div>
        <h2>STUDENT REGISTRATION FORM</h2>
    </div>

    <!-- Form Section -->
    <div class="content">
        <form method="POST" action="process_form.php">

            <h3>Personal Information</h3>

            <label>First Name <span>*</span></label>
            <input type="text" name="first_name" placeholder="e.g. Maria" required>

            <label>Middle Name</label>
            <input type="text" name="middle_name" placeholder="Optional">

            <label>Last Name <span>*</span></label>
            <input type="text" name="last_name" placeholder="e.g. Santos" required>

            <label>Date of Birth <span>*</span></label>
            <input type="date" name="birthdate" required>

            <label>Sex <span>*</span></label>
            <select name="sex" required>
                <option value="" disabled selected hidden>— Select Sex —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label>Civil Status <span>*</span></label>
            <select name="civil_status" required>
                <option value="" disabled selected hidden>— Select Civil Status —</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
            </select>

            <div class="section-divider"></div>

            <h3>Contact Information</h3>

            <label>Email Address <span>*</span></label>
            <input type="email" name="email" placeholder="your@email.com" required>

            <label>Phone Number <span>*</span></label>
            <input type="tel" name="phone" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" required>
            <small style="display: block; color: #64748b; margin-top: 5px;">Format: 09XXXXXXXXX (11 digits)</small>

            <label>Complete Address <span>*</span></label>
            <textarea name="address" placeholder="Street, Barangay, City/Municipality, Province" required></textarea>

            <div class="section-divider"></div>

            <h3>Emergency Contact</h3>

            <label>Guardian Name <span>*</span></label>
            <input type="text" name="guardian_name" placeholder="e.g. Juan Santos" required>

            <label>Guardian Phone Number <span>*</span></label>
            <input type="tel" name="guardian_phone" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" required>

            <label>Guardian Address <span>*</span></label>
            <textarea name="guardian_address" placeholder="Street, Barangay, City/Municipality, Province" required></textarea>

            <div class="section-divider"></div>

            <h3>Academic Information</h3>

            <label>Preferred Course <span>*</span></label>
            <select name="course_id" required>
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

            <label>Year Level <span>*</span></label>
            <select name="year_level" required>
                <option value="" disabled selected hidden>— Select Year —</option>
                <option value="1st Year">1st Year (Freshmen)</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
                <option value="Transferee">Transferee</option>
                <option value="Shiftee">Shiftee</option>
            </select>

            <label>Previous School / Last School Attended</label>
            <input type="text" name="previous_school" placeholder="e.g. Sagay National High School">

            <label>General Weighted Average (GPA)</label>
            <input type="number" name="gpa" step="0.01" min="1" max="100" placeholder="e.g. 92 or 1.25">

            <p class="disclaimer"><small>By submitting, you confirm that all information provided is accurate and truthful.</small></p>
            
            <button type="submit">Submit Application →</button>

        </form>
    </div>
</div>

</body>
</html>