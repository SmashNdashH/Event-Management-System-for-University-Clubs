<?php
include 'connection.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $advisor_id = intval($_POST['advisor_id']);
    $password_input = trim($_POST['password']);

    // Fetch advisor from DB
    $sql = "SELECT * FROM advisor WHERE advisor_id = $advisor_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $advisor = mysqli_fetch_assoc($result);

        // Check plain text password
        if ($advisor['password'] === $password_input) {
            $_SESSION['advisor_id'] = $advisor['advisor_id'];
            $_SESSION['club_id'] = $advisor['club_id'];

            header("Location: advisor_club.php");
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Invalid Advisor ID";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Advisor Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      width: 400px;
      text-align: center;
    }
    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background-color: #007BFF;
      color: white;
      padding: 10px;
      border: none;
      width: 100%;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    .back {
      margin-top: 10px;
      display: inline-block;
      color: #007BFF;
      cursor: pointer;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h2>Advisor Login</h2>

  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form method="POST">
    <input type="text" name="advisor_id" placeholder="Enter your Advisor ID" required />
    <input type="password" name="password" placeholder="Enter your Password" required />
    <button type="submit">Log In</button>
  </form>

  <div class="back" onclick="window.location.href='index.php'">← Back</div>
</div>

</body>
</html>
