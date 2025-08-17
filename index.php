<?php
include 'connection.php';
session_start();

// Fetch all clubs
$clubs_res = mysqli_query($conn, "SELECT club_id, club_name FROM clubs ORDER BY club_name ASC");
$clubs = [];
if ($clubs_res && mysqli_num_rows($clubs_res) > 0) {
    while ($row = mysqli_fetch_assoc($clubs_res)) {
        $clubs[] = $row;
    }
}
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
      text-align: center;
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

    .role-card.signup {
      background-color: #ffffff;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
      margin-bottom: 40px;
    }

    .role-card.signup:hover {
      box-shadow: 0 0 25px 5px rgba(245, 222, 182, 0.8);
      background-color: #f6eedeff;
    }

    .clubs-list {
      margin: 40px auto 0 auto;
      max-width: 700px;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      padding: 30px 24px;
    }
    .clubs-list h2 {
      text-align: center;
      color: #000000ff;
      margin-bottom: 18px;
      font-size: 1.5rem;
    }
    .club-link {
      display: block;
      padding: 12px 18px;
      margin-bottom: 10px;
      background: #f7fafc;
      border-radius: 8px;
      color: #333;
      text-decoration: none;
      font-size: 1.1rem;
      font-weight: 500;
      transition: background 0.2s;
      border: 1px solid #e0e7ef;
    }
    .club-link:hover {
      background: #e0f7fa;
      color: #0056b3;
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

  <!-- Sign Up Card -->
  <div class="role-card signup" onclick="window.location.href='member_signup.php'">
    Sign Up
  </div>

  <!-- Advisor and Member Cards -->
  <div class="role-select">
    <div class="role-card advisor" onclick="window.location.href='login_advisor.php'">
      I'm an Advisor
    </div>
    <div class="role-card member" onclick="window.location.href='member_login.php'">
      I'm a Member
    </div>
  </div>

  <!-- Clubs List Section -->
  <div class="clubs-list">
    <h2>Explore Clubs</h2>
    <?php if (!empty($clubs)): ?>
      <?php foreach ($clubs as $club): ?>
        <a class="club-link" href="club_info.php?club_id=<?php echo (int)$club['club_id']; ?>">
          <?php echo htmlspecialchars($club['club_name']); ?>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">No clubs found.</p>
    <?php endif; ?>
  </div>
</body>
</html>
