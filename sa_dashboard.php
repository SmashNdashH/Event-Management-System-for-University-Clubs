<?php
include 'connection.php';
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: login_advisor.php");
    exit;
}

$errors = [];
$success_club = '';
$success_advisor = '';

// --- Handle new club creation and advisor assignment ---
if (isset($_POST['create_club'])) {
    $club_name = trim(mysqli_real_escape_string($conn, $_POST['club_name']));
    $status = trim(mysqli_real_escape_string($conn, $_POST['status']));
    $status = ucfirst(strtolower($status)); // Ensure first letter uppercase
    $cell_number = trim(mysqli_real_escape_string($conn, $_POST['cell_number']));

    $first_name = trim(mysqli_real_escape_string($conn, $_POST['first_name']));
    $last_name = trim(mysqli_real_escape_string($conn, $_POST['last_name']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $phone = trim(mysqli_real_escape_string($conn, $_POST['phone']));

    // Validate club fields
    if (empty($club_name) || empty($status) || empty($cell_number)) {
        $errors[] = "Club name, status, and cell number are required.";
    }
    // Validate advisor fields
    if (empty($first_name) || empty($last_name) || empty($password) || empty($email) || empty($phone)) {
        $errors[] = "All advisor fields are required.";
    }

    // Only proceed if no errors
    if (empty($errors)) {
        // --- Check if club name is already taken ---
        $check_club = "SELECT 1 FROM Clubs WHERE club_name='$club_name' LIMIT 1";
        $result_club = mysqli_query($conn, $check_club);
        if ($result_club && mysqli_num_rows($result_club) > 0) {
            $errors[] = "The club name you entered is already taken. Please choose a different club name.";
        } else {
            // --- Check if advisor password is already taken ---
            $check_password = "SELECT 1 FROM Advisor WHERE password='$password' LIMIT 1";
            $result_pass = mysqli_query($conn, $check_password);
            if ($result_pass && mysqli_num_rows($result_pass) > 0) {
                $errors[] = "The password you entered for the advisor is already taken. Please choose a different password.";
            } else {
                // --- Insert club ---
                $registration_date = date('Y-m-d');
                $sql = "INSERT INTO Clubs (club_name, registration_date, status) 
                        VALUES ('$club_name', '$registration_date', '$status')";
                if (mysqli_query($conn, $sql)) {
                    $last_club_id = mysqli_insert_id($conn);
                    // Store cell number in Clubs_cell_number table
                    $sql_cell = "INSERT INTO Clubs_cell_number (club_id, cell_number) VALUES ($last_club_id, '$cell_number')";
                    mysqli_query($conn, $sql_cell);

                    $success_club = "Club '$club_name' created successfully!";

                    // --- Insert advisor ---
                    $sql_adv = "INSERT INTO Advisor (club_id, first_name, last_name, password) 
                                VALUES ($last_club_id, '$first_name', '$last_name', '$password')";
                    if (mysqli_query($conn, $sql_adv)) {
                        $advisor_id = mysqli_insert_id($conn);
                        // Store email and phone in respective tables
                        $sql_email = "INSERT INTO Advisor_email (advisor_id, email) VALUES ($advisor_id, '$email')";
                        $sql_phone = "INSERT INTO Advisor_phone (advisor_id, phone_number) VALUES ($advisor_id, '$phone')";
                        mysqli_query($conn, $sql_email);
                        mysqli_query($conn, $sql_phone);

                        $success_advisor = "Advisor '$first_name $last_name' assigned to the newly created club.";
                    } else {
                        $errors[] = "Error creating advisor: " . mysqli_error($conn);
                    }
                } else {
                    $errors[] = "Error creating club: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Super Admin Dashboard</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
        color: #333;
    }
    .container {
        background: white;
        border-radius: 10px;
        padding: 32px 36px;
        max-width: 560px; /* Match member_signup.php width */
        margin: 40px auto;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        position: relative;
        width: 100%;
        display: block;
    }
    h1, h3 {
        text-align: center;
        color: #0056b3;
        margin-bottom: 20px;
    }
    h2 {
        color: #007BFF;
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
        margin-top: 30px;
        margin-bottom: 20px;
        text-align: left;
    }
    .form-section {
        margin-bottom: 40px;
        margin-top: 30px;
        text-align: left;
    }
    .form-section form {
        text-align: left;
        max-width: 840px; /* Increased width for double input size */
        margin-left: 0;
    }
    .form-section label {
        display: block;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
        text-align: left;
    }
    .form-section input[type="text"],
    .form-section input[type="email"],
    .form-section input[type="password"],
    .form-section input[type="number"],
    .form-section select {
        width: 100%; /* Twice the previous width */
        min-width: 360px;
        padding: 12px 15px;
        margin-bottom: 18px;
        border-radius: 6px;
        border: 1.8px solid #ccc;
        font-size: 1rem;
        transition: border-color 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f7fafc;
        color: #333;
        box-sizing: border-box;
        display: block;
        margin-left: 0;
    }
    .form-section button {
        width: auto;
        min-width: 180px;
        margin-left: 0;
        margin-top: 18px;
        display: block;
    }
    input:focus, select:focus {
        border-color: #3b82f6;
        outline: none;
        background: #eef2ff;
    }
    button {
        background-color: #007BFF;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 700;
        transition: background-color 0.3s;
        margin-top: 8px;
        width: 100%;
        text-align: center;
    }
    button:hover {
        background-color: #0056b3;
    }
    .success {
        color: green;
        margin-bottom: 15px;
    }
    .errors {
        color: #721c24;
        background-color: #f8d7da;
        border: 1.5px solid #dc2626;
        border-radius: 8px;
        padding: 12px 18px;
        margin-bottom: 18px;
        font-size: 1rem;
    }
    .logout-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        display: block;
        text-align: center;
        z-index: 2;
    }
    .logout-btn a {
        display: inline-block;
        padding: 8px 14px;
        background-color: #b52b38;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
        font-family: inherit;
        font-weight: 500;
        border: none;
        cursor: pointer;
    }
    .logout-btn a:hover { background-color: #cc0000; }
    .info-block p {
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<!-- Logout and Go to Advisor Dashboard buttons outside the container -->
<div class="logout-btn">
    <a href="logout.php">Logout</a>
    <form method="get" action="advisor_club.php" style="margin-top:10px;">
        <button type="submit" style="
            display: inline-block;
            padding: 8px 14px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            text-align: center;
        ">Go to Advisor Dashboard</button>
    </form>
</div>

<div class="container" style="margin-top: 40px;">
    <h1 style="text-align:center;">Advisor Admin Dashboard</h1>
    <?php 
    if (!empty($errors)) { 
        echo '<div class="errors"><ul>';
        foreach ($errors as $err) echo "<li>$err</li>";
        echo '</ul></div>';
    }
    if ($success_club) echo "<div class='success'>$success_club</div>";
    if ($success_advisor) echo "<div class='success'>$success_advisor</div>";
    ?>

    <!-- Create New Club and Assign Advisor -->
    <div class="form-section" style="margin-left:0; margin-right:0;">
        <h2 style="text-align:center;">Register New Club & Assign Advisor</h2>
        <form method="POST" style="margin-left:0; margin-right:0;">
            <label for="club_name">Club Name</label>
            <input type="text" name="club_name" id="club_name" placeholder="Club Name" required>
            <label for="cell_number">Club Cell Number</label>
            <input type="text" name="cell_number" id="cell_number" placeholder="Club Cell Number" required>
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <hr style="margin: 30px 0;">
            <label for="first_name">Advisor First Name</label>
            <input type="text" name="first_name" id="first_name" placeholder="Advisor First Name" required>
            <label for="last_name">Advisor Last Name</label>
            <input type="text" name="last_name" id="last_name" placeholder="Advisor Last Name" required>
            <label for="password">Password</label>
            <input type="text" name="password" id="password" placeholder="Password" required>
            <label for="email">Advisor Email</label>
            <input type="email" name="email" id="email" placeholder="Advisor Email" required>
            <label for="phone">Advisor Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="Advisor Phone Number" required>
            <button type="submit" name="create_club" style="width:auto; min-width:180px; margin-left:0; margin-top:18px; display:block;">Create Club & Assign Advisor</button>
        </form>
    </div>
</div>

</body>
</html>


