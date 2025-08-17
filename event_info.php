<?php
include 'connection.php';
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Fetch event info
$event_res = mysqli_query($conn, "SELECT * FROM events WHERE event_id = $event_id");
$event = mysqli_fetch_assoc($event_res);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($event['title'] ?? 'Event Info'); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 32px; }
        h1 { color: #0056b3; margin-bottom: 18px; }
        .guest-form { margin-top: 32px; }
        label { font-weight: 600; margin-bottom: 6px; display: block; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 14px; border-radius: 6px; border: 1.5px solid #ccc; font-size: 1rem; }
        button { background: #007BFF; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #0056b3; }
        .success { color: green; margin-bottom: 12px; }
        .error { color: red; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo htmlspecialchars($event['title'] ?? 'Event Info'); ?></h1>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date'] ?? ''); ?></p>
    <p><strong>Type:</strong> <?php echo htmlspecialchars($event['type'] ?? ''); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
    <p><strong>Start Time:</strong> <?php echo htmlspecialchars($event['start_time'] ?? ''); ?></p>
    <p><strong>End Time:</strong> <?php echo htmlspecialchars($event['end_time'] ?? ''); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($event['status'] ?? ''); ?></p>
    <p><strong>Budget:</strong> <?php echo htmlspecialchars($event['budget'] ?? ''); ?></p>

    <div class="guest-form">
        <h2>Register as Guest to Attend</h2>
        <?php
        $success = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = trim($_POST['student_id']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $password = trim($_POST['password']);

            if (empty($student_id) || empty($first_name) || empty($last_name) || empty($password)) {
                $error = "All fields are required.";
            } else {
                // Check if member already exists
                $check_member = mysqli_query($conn, "SELECT * FROM members WHERE student_id = '$student_id'");
                if (mysqli_num_rows($check_member) > 0) {
                    $error = "Student ID already registered.";
                } else {
                    // Insert into members
                    $insert_member = "INSERT INTO members (student_id, first_name, last_name, password) VALUES ('$student_id', '$first_name', '$last_name', '$password')";
                    if (mysqli_query($conn, $insert_member)) {
                        // Insert into partakes
                        $insert_partake = "INSERT INTO partakes (student_id, event_id) VALUES ('$student_id', $event_id)";
                        mysqli_query($conn, $insert_partake);
                        $success = "Registration successful! You are now registered as a guest for this event.";
                    } else {
                        $error = "Error registering. Please try again.";
                    }
                }
            }
        }
        ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="student_id">Student ID</label>
            <input type="text" name="student_id" id="student_id" required>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" required>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Register as Guest</button>
        </form>
    </div>
</div>
</body>
</html>