<?php
include 'connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Collect and sanitize member data ---
    $first_name = trim(mysqli_real_escape_string($conn, $_POST['first_name']));
    $last_name = trim(mysqli_real_escape_string($conn, $_POST['last_name']));
    $house_no = trim(mysqli_real_escape_string($conn, $_POST['house_no']));
    $street_no = trim(mysqli_real_escape_string($conn, $_POST['street_no']));
    $city = trim(mysqli_real_escape_string($conn, $_POST['city']));
    $dob = trim(mysqli_real_escape_string($conn, $_POST['dob']));
    $position = $_POST['position'] ?? '';
    $password = trim(mysqli_real_escape_string($conn, $_POST['password'])); // <-- plain text

    $errors = [];

    // --- Validate inputs ---
    if (empty($first_name) || empty($last_name) || empty($city) || empty($password)) {
        $errors[] = "First name, last name, city, and password are required.";
    }

    if ($position === 'volunteer') {
        $start_session = trim(mysqli_real_escape_string($conn, $_POST['start_session'] ?? ''));
        if (empty($start_session)) $errors[] = "Start session is required for volunteers.";
    } elseif ($position === 'organizer') {
        $work_hours = trim(mysqli_real_escape_string($conn, $_POST['work_hours'] ?? ''));
        if (!is_numeric($work_hours) || $work_hours < 0) $errors[] = "Work hours must be a non-negative number.";
    } else {
        $errors[] = "Please select a position.";
    }

    // --- Check if member already exists ---
    $check_member = "SELECT * FROM Members WHERE first_name='$first_name' AND last_name='$last_name' AND date_of_birth='$dob'";
    $result = mysqli_query($conn, $check_member);
    if ($result && mysqli_num_rows($result) > 0) {
        $errors[] = "Member already exists.";
    }

    if (empty($errors)) {

        // --- Insert into Members table (plain text password) ---
        $insert_member = "INSERT INTO Members (first_name, last_name, house_no, street_no, city, date_of_birth, password) 
                          VALUES ('$first_name', '$last_name', '$house_no', '$street_no', '$city', '$dob', '$password')";

        if (mysqli_query($conn, $insert_member)) {
            $student_id = mysqli_insert_id($conn); // Get auto-incremented student_id
            $_SESSION['student_id'] = $student_id;

            // --- Insert position-specific data ---
            if ($position === 'volunteer') {
                $insert_volunteer = "INSERT INTO Volunteer (student_id, start_session) VALUES ($student_id, '$start_session')";
                mysqli_query($conn, $insert_volunteer);
            } elseif ($position === 'organizer') {
                $insert_organizer = "INSERT INTO Organizer (student_id, work_hours) VALUES ($student_id, '$work_hours')";
                mysqli_query($conn, $insert_organizer);
            }

            // --- Show student ID to the user ---
            echo "<script>alert('Signup successful! Your student ID is: $student_id'); window.location.href='member_dashboard.php';</script>";
            exit();

        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Sign Up</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%); margin: 0; min-height: 100vh; padding: 20px; display: flex; justify-content: center; align-items: flex-start; }
        .container { background: white; border-radius: 10px; padding: 32px 36px; max-width: 560px; margin: 40px auto; box-shadow: 0 8px 20px rgba(0,0,0,0.15); width: 100%; }
        h2 { text-align: center; font-weight: bold; font-size: 2rem; margin-bottom: 20px; }
        label { font-weight: 600; display: block; margin-bottom: 8px; color: #555; }
        input[type="text"], input[type="number"], input[type="date"], input[type="password"], input[type="datetime-local"] { width: 100%; padding: 12px 15px; margin-bottom: 18px; border-radius: 6px; border: 1.8px solid #ccc; font-size: 1rem; background: #f7fafc; }
        input:focus { border-color: #3b82f6; outline: none; background: #eef2ff; }
        .radio-group label { margin-right: 18px; }
        .position-specific { display: none; }
        button { width: 100%; padding: 12px 0; background-color: #3b82f6; color: #fff; font-size: 1.1rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; transition: background-color 0.3s ease; margin-top: 8px; }
        button:hover { background-color: #2563eb; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; border: 1.5px solid #dc2626; }
        .back { margin-top: 10px; text-align: center; color: #007BFF; cursor: pointer; text-decoration: underline; }
    </style>
    <script>
        function togglePositionFields() {
            const position = document.querySelector('input[name="position"]:checked')?.value;
            document.getElementById('volunteerFields').style.display = position === 'volunteer' ? 'block' : 'none';
            document.getElementById('organizerFields').style.display = position === 'organizer' ? 'block' : 'none';
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Member Sign Up</h2>
    <?php if (!empty($errors)) { echo '<div class="errors"><ul>'; foreach ($errors as $error) { echo "<li>$error</li>"; } echo '</ul></div>'; } ?>
    <form method="POST" action="">
        <label>First Name</label>
        <input type="text" name="first_name" required value="<?= $_POST['first_name'] ?? '' ?>">

        <label>Last Name</label>
        <input type="text" name="last_name" required value="<?= $_POST['last_name'] ?? '' ?>">

        <label>House No.</label>
        <input type="text" name="house_no" value="<?= $_POST['house_no'] ?? '' ?>">

        <label>Street No.</label>
        <input type="text" name="street_no" value="<?= $_POST['street_no'] ?? '' ?>">

        <label>City</label>
        <input type="text" name="city" required value="<?= $_POST['city'] ?? '' ?>">

        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?= $_POST['dob'] ?? '' ?>">

        <label>Password</label>
        <input type="text" name="password" required value="<?= $_POST['password'] ?? '' ?>"> <!-- show plain text -->

        <label>Select Position</label>
        <div class="radio-group">
            <label><input type="radio" name="position" value="volunteer" onclick="togglePositionFields()" <?= (isset($_POST['position']) && $_POST['position']=='volunteer')?'checked':'' ?>> Volunteer</label>
            <label><input type="radio" name="position" value="organizer" onclick="togglePositionFields()" <?= (isset($_POST['position']) && $_POST['position']=='organizer')?'checked':'' ?>> Organizer</label>
        </div>

        <div id="volunteerFields" class="position-specific">
            <label>Start Session</label>
            <input type="datetime-local" name="start_session" value="<?= $_POST['start_session'] ?? '' ?>">
        </div>

        <div id="organizerFields" class="position-specific">
            <label>Work Hours</label>
            <input type="number" name="work_hours" value="<?= $_POST['work_hours'] ?? '' ?>">
        </div>

        <button type="submit">Sign Up</button>
    </form>
    <div class="back" onclick="window.location.href='index.php'">← Back</div>
</div>
<script>togglePositionFields();</script>
</body>
</html>




