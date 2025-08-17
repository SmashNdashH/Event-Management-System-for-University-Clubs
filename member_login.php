<?php
include 'connection.php';
session_start();

$error = '';
$student_id_input = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id_input = trim($_POST['student_id']);
    $password_input = trim($_POST['password']);

    if (!ctype_digit($student_id_input)) {
        $error = 'Student ID must be numeric.';
    } elseif (empty($student_id_input) || empty($password_input)) {
        $error = 'Please enter both Student ID and Password.';
    } else {
        // Sanitize input
        $student_id = mysqli_real_escape_string($conn, $student_id_input);

        // Fetch member by student_id
        $member_check = mysqli_query($conn, "SELECT * FROM members WHERE student_id = '$student_id'");
        if (mysqli_num_rows($member_check) == 0) {
            $error = 'Student ID not found. Please check and try again.';
        } else {
            $member = mysqli_fetch_assoc($member_check);

            // **Compare plain-text password**
            if ($password_input !== $member['password']) {
                $error = 'Incorrect password. Please try again.';
            } else {
                // Login successful
                $_SESSION['student_id'] = $student_id;
                header("Location: member_dashboard.php");  
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    color: #333;
    font-weight: bold;
    font-size: 2rem;
    margin-bottom: 20px;
    padding-bottom: 0;
    letter-spacing: 0.5px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border-bottom: none;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  input[type="text"], input[type="password"] {
    padding: 12px 15px;
    border: 1.8px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f7fafc;
    color: #333;
    box-sizing: border-box;
    margin-bottom: 18px;
    transition: border-color 0.3s ease;
  }
  input:focus {
    border-color: #3b82f6;
    outline: none;
    background: #eef2ff;
  }
  input::placeholder {
    color: #888;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 1rem;
    opacity: 1;
  }
  button {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    border: none;
    width: 100%;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 600;
    transition: background-color 0.3s;
  }
  button:hover {
    background-color: #0056b3;
  }
  .error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .back {
    margin-top: 10px;
    display: block;
    text-align: center;
    color: #007BFF;
    cursor: pointer;
    text-decoration: underline;
    font-size: 1rem;
    font-weight: 500;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
</style>
</head>
<body>

<div class="container">
  <h1>Member Login</h1>
  <?php if ($error) { echo '<div class="error">'.htmlspecialchars($error).'</div>'; } ?>
  <form method="post" action="">
    <input type="text" name="student_id" placeholder="Enter your Student ID" required value="<?= htmlspecialchars($student_id_input); ?>" />
    <input type="password" name="password" placeholder="Enter your Password" required />
    <button type="submit">Log In</button>
  </form>
  <div class="back" onclick="window.location.href='index.php'">← Back</div>
</div>

</body>
</html>

