<?php
include 'connection.php';
session_start();

$error = '';
$show_buttons = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $advisor_id = intval($_POST['advisor_id']);
    $password = trim($_POST['password']);
    
    // Fetch advisor from advisor table
    $sql = "SELECT * FROM advisor WHERE advisor_id = $advisor_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $advisor = mysqli_fetch_assoc($result);
        
        // Verify password (plain text)
        if ($password === $advisor['password']) {
            // Check if this advisor is a super admin (SA_ID = 1)
            $sa_check = "SELECT * FROM superadmin WHERE advisor_id = $advisor_id AND SA_ID = 1";
            $sa_result = mysqli_query($conn, $sa_check);
            
            if (mysqli_num_rows($sa_result) === 1) {
                // Advisor is super admin, show buttons
                $_SESSION['temp_advisor_id'] = $advisor_id; // temporarily store id
                $show_buttons = true;
            } else {
                // Normal advisor login
                $_SESSION['advisor_id'] = $advisor['advisor_id'];
                $_SESSION['club_id'] = $advisor['club_id'];
                header("Location: advisor_club.php");
                exit;
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Advisor ID not found.";
    }
}

// Handle button selection for super admin
if (isset($_POST['role_choice'])) {
    $advisor_id = $_SESSION['temp_advisor_id'];
    if ($_POST['role_choice'] === 'advisor') {
        // Log in as club advisor
        $sql = "SELECT * FROM advisor WHERE advisor_id = $advisor_id";
        $result = mysqli_query($conn, $sql);
        $advisor = mysqli_fetch_assoc($result);
        $_SESSION['advisor_id'] = $advisor['advisor_id'];
        $_SESSION['club_id'] = $advisor['club_id'];
        unset($_SESSION['temp_advisor_id']);
        header("Location: advisor_club.php");
        exit;
    } elseif ($_POST['role_choice'] === 'superadmin') {
        // Log in as super admin
        $_SESSION['superadmin_id'] = $advisor_id;
        unset($_SESSION['temp_advisor_id']);
        header("Location: sa_dashboard.php");
        exit;
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
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      margin-bottom: 18px;
      border-radius: 6px;
      border: 1.8px solid #ccc;
      font-size: 1rem;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f7fafc;
      color: #333;
      box-sizing: border-box;
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
    .superadmin-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
    }
    .superadmin-buttons button {
      flex: 1;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h2>Advisor Login</h2>

  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <?php if ($show_buttons): ?>
      <form method="POST" class="superadmin-buttons">
          <button type="submit" name="role_choice" value="advisor">Log in as Club Advisor</button>
          <button type="submit" name="role_choice" value="superadmin">Log in as Super Admin</button>
      </form>
  <?php else: ?>
      <form method="POST">
          <input type="text" name="advisor_id" placeholder="Enter your Advisor ID" required />
          <input type="password" name="password" placeholder="Enter your Password" required />
          <button type="submit">Log In</button>
      </form>
  <?php endif; ?>

  <div class="back" onclick="window.location.href='index.php'">← Back</div>
</div>

</body>
</html>
