<?php
include 'connection.php';
session_start();

$error = '';
$student_id_input = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id_input = trim($_POST['student_id']);

    if (empty($student_id_input)) {
        $error = 'Please enter your Student ID.';
    } else {
        // Sanitize input
        $student_id = mysqli_real_escape_string($conn, $student_id_input);

        // Check if student_id exists in members table (optional but recommended)
        $member_check = mysqli_query($conn, "SELECT * FROM members WHERE student_id = '$student_id'");
        if (mysqli_num_rows($member_check) == 0) {
            $error = 'Student ID not found. Please check and try again.';
        } else {
            // Initialize roles array
            $roles = [];

            // Check President
            $res = mysqli_query($conn, "SELECT * FROM president WHERE student_id = '$student_id' AND (end_date IS NULL OR end_date > CURDATE())");
            if (mysqli_num_rows($res) > 0) {
                $roles[] = 'President';
            }

            // Check Executive
            $res = mysqli_query($conn, "SELECT * FROM executive WHERE student_id = '$student_id' AND (end_date IS NULL OR end_date > CURDATE())");
            if (mysqli_num_rows($res) > 0) {
                $roles[] = 'Executive';
            }

            // Check Volunteer
            $res = mysqli_query($conn, "SELECT * FROM volunteer WHERE student_id = '$student_id'");
            if (mysqli_num_rows($res) > 0) {
                $roles[] = 'Volunteer';
            }

            // Check Organizer
            $res = mysqli_query($conn, "SELECT * FROM organizer WHERE student_id = '$student_id'");
            if (mysqli_num_rows($res) > 0) {
                $roles[] = 'Organizer';
            }

            // Check Supervisor
            $res = mysqli_query($conn, "SELECT * FROM supervisor WHERE student_id = '$student_id' AND (end_date IS NULL OR end_date > CURDATE())");
            if (mysqli_num_rows($res) > 0) {
                $roles[] = 'Supervisor';
            }

            if (empty($roles)) {
                $error = 'No assigned role found for this Student ID.';
            } else {
                // Save session and redirect
                $_SESSION['student_id'] = $student_id;
                $_SESSION['roles'] = $roles; // array of roles
                header("Location: member_dashboard.php");  // Change as per your dashboard page
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Member Login</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    color: #333;
  }
  .container {
    background: white;
    border-radius: 10px;
    padding: 30px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  h1 {
    text-align: center;
    color: #0056b3;
    margin-bottom: 25px;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  input[type="text"] {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
  }
  button {
    background-color: #007BFF;
    border: none;
    color: white;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
  }
  button:hover {
    background-color: #0056b3;
  }
  .error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
  }

  .back {
    margin-top: 10px;
    display: block;
    text-align: center;
    color: #007BFF;
    cursor: pointer;
    text-decoration: underline;
}

</style>
</head>
<body>

<div class="container">
  <h1>Member Login</h1>
  <?php if ($error) { echo '<div class="error">'.htmlspecialchars($error).'</div>'; } ?>
  <form method="post" action="">
    <input type="text" name="student_id" placeholder="Enter your Student ID" required value="<?php echo htmlspecialchars($student_id_input); ?>" />
    <button type="submit">Login</button>
  </form>

    <div class="back" onclick="window.location.href='index.php'">← Back</div>
</div>

</body>
</html>
