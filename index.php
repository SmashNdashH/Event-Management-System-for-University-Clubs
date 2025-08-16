<?php
include 'connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Event Management System for University of Clubs - Welcome</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
    }

    h1 {
      color: #fff;
      font-size: 40px;
      margin-bottom: 50px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .role-select {
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .role-card {
      background-color: white;
      border-radius: 20px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      padding: 50px 40px;
      width: 220px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 20px;
      font-weight: bold;
      color: #333;
    }

    .role-card:hover {
      transform: scale(1.05);
    }

    .role-card.advisor:hover {
      box-shadow: 0 0 25px 5px rgba(72, 219, 251, 0.8);
      background-color: #ecfcff;
    }

    .role-card.member:hover {
      box-shadow: 0 0 25px 5px rgba(255, 107, 129, 0.8);
      background-color: #fff5f5;
    }

    @media (max-width: 600px) {
      .role-card {
        width: 80%;
        padding: 40px 20px;
      }
    }
  </style>
</head>
<body>

  <h1>Welcome to the Event Management System for University Clubs</h1>

  <div class="role-select">
    <div class="role-card advisor" onclick="window.location.href='login_advisor.php'">
      I'm an Advisor
    </div>
    <div class="role-card member" onclick="window.location.href='member_login.php'">
      I'm a Member
    </div>
  </div>

</body>
</html>
