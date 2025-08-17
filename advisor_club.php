<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['advisor_id']) || !isset($_SESSION['club_id'])) {
    header("Location: login_advisor.php");
    exit;
}

$advisor_id = $_SESSION['advisor_id'];
$club_id = $_SESSION['club_id'];

// Fetch advisor's name
$advisor_res = mysqli_query($conn, "SELECT first_name, last_name FROM advisor WHERE advisor_id = $advisor_id");
$advisor = mysqli_fetch_assoc($advisor_res);
$advisor_name = $advisor ? ($advisor['first_name'] . ' ' . $advisor['last_name']) : "Advisor";

// Fetch advisor emails (multi-valued)
$email_res = mysqli_query($conn, "SELECT email FROM Advisor_email WHERE advisor_id = $advisor_id");
$advisor_emails = [];
if ($email_res && mysqli_num_rows($email_res) > 0) {
    while ($row = mysqli_fetch_assoc($email_res)) {
        $advisor_emails[] = $row['email'];
    }
}

// Fetch advisor phone numbers (multi-valued)
$phone_res = mysqli_query($conn, "SELECT phone_number FROM Advisor_phone WHERE advisor_id = $advisor_id");
$advisor_phones = [];
if ($phone_res && mysqli_num_rows($phone_res) > 0) {
    while ($row = mysqli_fetch_assoc($phone_res)) {
        $advisor_phones[] = $row['phone_number'];
    }
}

// Fetch club info
$club_res = mysqli_query($conn, "SELECT * FROM clubs WHERE club_id = $club_id");
$club = mysqli_fetch_assoc($club_res);

// Handle event create/update/delete/approve/cancel here
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Helper function to check if student already has any role in this club
    function studentHasRoleInClub($conn, $student_id, $club_id) {
        // Check President table
        $sql_pres = "SELECT COUNT(*) AS cnt FROM president p
                     INNER JOIN volunteer v ON p.student_id = v.student_id
                     INNER JOIN joins j ON v.student_id = j.student_id AND j.club_id = $club_id
                     WHERE p.student_id = $student_id";
        $res_pres = mysqli_query($conn, $sql_pres);
        $pres_count = mysqli_fetch_assoc($res_pres)['cnt'];

        // Check Executive table
        $sql_exec = "SELECT COUNT(*) AS cnt FROM executive e
                     INNER JOIN volunteer v ON e.student_id = v.student_id
                     INNER JOIN joins j ON v.student_id = j.student_id AND j.club_id = $club_id
                     WHERE e.student_id = $student_id";
        $res_exec = mysqli_query($conn, $sql_exec);
        $exec_count = mysqli_fetch_assoc($res_exec)['cnt'];

        // Check Supervisor table
        $sql_sup = "SELECT COUNT(*) AS cnt FROM supervisor s
                    INNER JOIN organizer o ON s.student_id = o.student_id
                    INNER JOIN joins j ON o.student_id = j.student_id AND j.club_id = $club_id
                    WHERE s.student_id = $student_id";
        $res_sup = mysqli_query($conn, $sql_sup);
        $sup_count = mysqli_fetch_assoc($res_sup)['cnt'];

        return ($pres_count > 0 || $exec_count > 0 || $sup_count > 0);
    }

    // Create event
    if (isset($_POST['create_event'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $event_type = mysqli_real_escape_string($conn, $_POST['event_type']);
        $event_date = $_POST['event_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $total_budget = floatval($_POST['total_budget']);
        $approval_status = 'Pending';

        $insert_sql = "INSERT INTO events (club_id, title, event_type, event_date, start_time, end_time, approval_status, total_budget)
                       VALUES ($club_id, '$title', '$event_type', '$event_date', '$start_time', '$end_time', '$approval_status', $total_budget)";
        mysqli_query($conn, $insert_sql);
        header("Location: advisor_club.php");
        exit;
    }

    // Edit event - fetch for editing
    if (isset($_POST['edit_event_btn'])) {
        $edit_event_id = intval($_POST['event_id']);
        $edit_res = mysqli_query($conn, "SELECT * FROM events WHERE event_id = $edit_event_id AND club_id = $club_id");
        $edit_event = mysqli_fetch_assoc($edit_res);
    }

    // Update event
    if (isset($_POST['update_event'])) {
        $event_id = intval($_POST['event_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $event_type = mysqli_real_escape_string($conn, $_POST['event_type']);
        $event_date = $_POST['event_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $total_budget = floatval($_POST['total_budget']);

        $update_sql = "UPDATE events SET title='$title', event_type='$event_type', event_date='$event_date',
                       start_time='$start_time', end_time='$end_time', total_budget=$total_budget WHERE event_id=$event_id AND club_id=$club_id";
        mysqli_query($conn, $update_sql);
        header("Location: advisor_club.php");
        exit;
    }

    // Delete event
    if (isset($_POST['delete_event_btn'])) {
        $event_id = intval($_POST['event_id']);
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id AND club_id = $club_id");
        header("Location: advisor_club.php");
        exit;
    }

    // Approve / Disapprove event
    if (isset($_POST['event_action'])) {
        $event_id = intval($_POST['event_id']);
        $action = $_POST['event_action']; // 'Approve' or 'Disapprove'

        if ($action === 'Approve' || $action === 'Disapprove') {
            $new_status = $action === 'Approve' ? 'Approved' : 'Disapproved';
            $sql = "UPDATE events SET approval_status = '$new_status' WHERE event_id = $event_id AND club_id = $club_id";
            mysqli_query($conn, $sql);
            header("Location: advisor_club.php");
            exit;
        }
    }

    // Delete role handler (president, executive, supervisor)
    if (isset($_POST['delete_role']) && isset($_POST['role_id'])) {
        $role = $_POST['delete_role'];
        $role_id = intval($_POST['role_id']);

        if ($role === 'president') {
            mysqli_query($conn, "DELETE FROM president WHERE president_id = $role_id");
        } elseif ($role === 'executive') {
            mysqli_query($conn, "DELETE FROM executive WHERE exec_id = $role_id");
        } elseif ($role === 'supervisor') {
            mysqli_query($conn, "DELETE FROM supervisor WHERE sup_id = $role_id");
        }
        header("Location: advisor_club.php");
        exit;
    }

    // Assign President
    if (isset($_POST['assign_president'])) {
        $student_id = intval($_POST['president_student_id']);
        $check = mysqli_query($conn, "
          SELECT p.president_id FROM president p
          WHERE p.student_id IN (
            SELECT student_id FROM joins WHERE club_id = $club_id
          )
          LIMIT 1
        ");
        if (mysqli_num_rows($check) == 0) {
            $vol_res = mysqli_query($conn, "
              SELECT v.vol_id FROM volunteer v
              INNER JOIN joins j ON v.student_id = j.student_id
              WHERE v.student_id = $student_id AND j.club_id = $club_id
              LIMIT 1
            ");
            if ($vol_row = mysqli_fetch_assoc($vol_res)) {
                $vol_id = intval($vol_row['vol_id']);
                $president_id = rand(1000, 9999);
                mysqli_query($conn, "
                  INSERT INTO president (student_id, vol_id, president_id, start_date)
                  VALUES ($student_id, $vol_id, $president_id, CURDATE())
                ");
            }
        }
        header("Location: advisor_club.php");
        exit;
    }

    // Assign Executive
    if (isset($_POST['assign_executive'])) {
        $student_id = intval($_POST['executive_student_id']);
        $check = mysqli_query($conn, "
          SELECT e.exec_id FROM executive e
          WHERE e.student_id IN (
            SELECT student_id FROM joins WHERE club_id = $club_id
          )
          LIMIT 1
        ");
        if (mysqli_num_rows($check) == 0) {
            $vol_res = mysqli_query($conn, "
              SELECT v.vol_id FROM volunteer v
              INNER JOIN joins j ON v.student_id = j.student_id
              WHERE v.student_id = $student_id AND j.club_id = $club_id
              LIMIT 1
            ");
            if ($vol_row = mysqli_fetch_assoc($vol_res)) {
                $vol_id = intval($vol_row['vol_id']);
                $exec_id = rand(1000, 9999);
                mysqli_query($conn, "
                  INSERT INTO executive (student_id, vol_id, exec_id, start_date)
                  VALUES ($student_id, $vol_id, $exec_id, CURDATE())
                ");
            }
        }
        header("Location: advisor_club.php");
        exit;
    }

    // Assign Supervisor
    if (isset($_POST['assign_supervisor'])) {
        $student_id = intval($_POST['supervisor_student_id']);
        $check = mysqli_query($conn, "
          SELECT s.sup_id FROM supervisor s
          WHERE s.student_id IN (
            SELECT student_id FROM joins WHERE club_id = $club_id
          )
          LIMIT 1
        ");
        if (mysqli_num_rows($check) == 0) {
            $org_res = mysqli_query($conn, "
              SELECT o.org_id FROM organizer o
              INNER JOIN joins j ON o.student_id = j.student_id
              WHERE o.student_id = $student_id AND j.club_id = $club_id
              LIMIT 1
            ");
            if ($org_row = mysqli_fetch_assoc($org_res)) {
                $org_id = intval($org_row['org_id']);
                $sup_id = rand(1000, 9999);
                mysqli_query($conn, "
                  INSERT INTO supervisor (student_id, org_id, sup_id, start_date)
                  VALUES ($student_id, $org_id, $sup_id, CURDATE())
                ");
            }
        }
        header("Location: advisor_club.php");
        exit;
    }

    // Approve pending join requests
    if (isset($_POST['approve_join'])) {
        $student_id = intval($_POST['student_id']);
        if (!studentHasRoleInClub($conn, $student_id, $club_id)) {
            mysqli_query($conn, "
                UPDATE joins 
                SET join_status = 'Active' 
                WHERE student_id = $student_id AND club_id = $club_id
            ");
        }
        header("Location: advisor_club.php");
        exit;
    }

    // Disapprove pending join requests
    if (isset($_POST['disapprove_join'])) {
        $student_id = intval($_POST['student_id']);
        mysqli_query($conn, "
            DELETE FROM joins 
            WHERE student_id = $student_id AND club_id = $club_id AND join_status = 'Pending'
        ");
        header("Location: advisor_club.php");
        exit;
    }
}

// Fetch events
$events_res = mysqli_query($conn, "SELECT * FROM events WHERE club_id = $club_id ORDER BY event_date DESC");

// Fetch Volunteers who are members of this club
$volunteers_res = mysqli_query($conn, "
    SELECT v.student_id, v.vol_id, m.first_name, m.last_name
    FROM volunteer v
    INNER JOIN joins j ON v.student_id = j.student_id
    INNER JOIN members m ON v.student_id = m.student_id
    WHERE j.club_id = $club_id
");

// Fetch Organizers who are members of this club
$organizers_res = mysqli_query($conn, "
    SELECT o.student_id, o.org_id, m.first_name, m.last_name
    FROM organizer o
    INNER JOIN joins j ON o.student_id = j.student_id
    INNER JOIN members m ON o.student_id = m.student_id
    WHERE j.club_id = $club_id
");

// Fetch pending join requests
$join_requests_res = mysqli_query($conn, "
    SELECT j.student_id, m.first_name, m.last_name
    FROM joins j
    INNER JOIN members m ON j.student_id = m.student_id
    WHERE j.club_id = $club_id AND j.join_status = 'Pending'
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style.css">
<meta charset="UTF-8" />
<title>Advisor Dashboard - <?php echo htmlspecialchars($club['club_name']); ?></title>
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
    padding: 30px;
    max-width: 1060px;
    margin: auto;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  h1, h3 {
    text-align: center;
    color: #0056b3;
  }
  h2 {
    color: #007BFF;
    border-bottom: 2px solid #eee;
    padding-bottom: 5px;
    margin-top: 30px;
    margin-bottom: 20px;
    text-align: left;
}
  form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-bottom: 30px;
  }
  select, input[type="text"],
  input[type="date"],
  input[type="time"],
  input[type="number"] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    flex: 1 1 180px;
    font-size: 1rem;
  }
  button {
    background-color: #007BFF;
    border: none;
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    flex: 1 1 150px;
    transition: background-color 0.3s;
    margin-top: 10px;
  }
  button:hover {
    background-color: #0056b3;
  }
  a.cancel-link {
    display: inline-block;
    margin-top: 14px;
    color: #007BFF;
    text-decoration: underline;
    cursor: pointer;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
  }
  th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
  }
  th {
    background-color: #f0f4ff;
    color: #003366;
  }
  tr:hover {
    background-color: #e6f0ff;
  }
  .actions form {
    display: inline-block;
    margin: 0 3px;
  }
  .actions button {
    padding: 6px 10px;
    font-size: 0.9rem;
  }
  .logout-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    display: block;
    text-align: center;
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
  }
  .logout-btn a:hover { background-color: #cc0000; }
</style>
</head>
<body>

<div class="container">
  <h1 style="text-align:center; color:#0056b3; margin-bottom:20px;">
    Welcome, <?php echo htmlspecialchars($advisor_name) . " (" . htmlspecialchars($advisor_id) . ")"; ?>
  </h1>

  <div class="advisor-info" style="margin-bottom: 32px;">
    <p style="margin-bottom: 10px;"><strong>Advisor to:</strong> <?php echo htmlspecialchars($club['club_name']); ?></p>
    <p style="margin-bottom: 10px;"><strong>Email:</strong>
      <?php
        if (!empty($advisor_emails)) {
          echo htmlspecialchars(implode(', ', $advisor_emails));
        } else {
          echo 'Not provided';
        }
      ?>
    </p>
    <p style="margin-bottom: 10px;"><strong>Phone Number:</strong>
      <?php
        if (!empty($advisor_phones)) {
          echo htmlspecialchars(implode(', ', $advisor_phones));
        } else {
          echo 'Not provided';
        }
      ?>
    </p>
  </div>

  <!-- Create/Edit Event Forms -->
  <?php if (!isset($edit_event)) { ?>
  <h2>Create Event</h2>
  <form method="post">
    <input type="text" name="title" placeholder="Event Title" required />
    <input type="text" name="event_type" placeholder="Event Type" required />
    <input type="date" name="event_date" required />
    <input type="time" name="start_time" required />
    <input type="time" name="end_time" required />
    <input type="number" step="0.01" name="total_budget" placeholder="Total Budget" required />
    <button type="submit" name="create_event">Create Event</button>
  </form>
  <?php } else { ?>
  <h2>Edit Event</h2>
  <form method="post">
    <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>" />
    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_event['title']); ?>" required />
    <input type="text" name="event_type" value="<?php echo htmlspecialchars($edit_event['event_type']); ?>" required />
    <input type="date" name="event_date" value="<?php echo $edit_event['event_date']; ?>" required />
    <input type="time" name="start_time" value="<?php echo $edit_event['start_time']; ?>" required />
    <input type="time" name="end_time" value="<?php echo $edit_event['end_time']; ?>" required />
    <input type="number" step="0.01" name="total_budget" value="<?php echo $edit_event['total_budget']; ?>" required />
    <button type="submit" name="update_event">Update Event</button>
    <a href="advisor_club.php" class="cancel-link">Cancel</a>
  </form>
  <?php } ?>

  <!-- Events Table -->
  <h2>Events</h2>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Type</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($event = mysqli_fetch_assoc($events_res)) { ?>
      <tr>
        <td><?php echo htmlspecialchars($event['title']); ?></td>
        <td><?php echo htmlspecialchars($event['event_type']); ?></td>
        <td><?php echo $event['event_date']; ?></td>
        <td><?php echo $event['approval_status']; ?></td>
        <td class="actions">
          <form method="post">
            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>" />
            <button type="submit" name="edit_event_btn">Edit</button>
          </form>
          <form method="post" onsubmit="return confirm('Are you sure you want to delete this event?');">
            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>" />
            <button type="submit" name="delete_event_btn">Delete</button>
          </form>
          <?php if ($event['approval_status'] != 'Approved') { ?>
          <form method="post">
            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>" />
            <button type="submit" name="event_action" value="Approve">Approve</button>
          </form>
          <?php } ?>
          <?php if ($event['approval_status'] != 'Disapproved') { ?>
          <form method="post">
            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>" />
            <button type="submit" name="event_action" value="Disapprove">Disapprove</button>
          </form>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

<?php
// Fetch current roles
$pres_res = mysqli_query($conn, "SELECT p.president_id, p.student_id, p.vol_id, m.first_name, m.last_name
    FROM president p JOIN members m ON p.student_id = m.student_id
    WHERE p.student_id IN (SELECT student_id FROM joins WHERE club_id = $club_id) LIMIT 1");
$current_president = mysqli_fetch_assoc($pres_res);

$exec_res = mysqli_query($conn, "SELECT e.exec_id, e.student_id, e.vol_id, m.first_name, m.last_name
    FROM executive e JOIN members m ON e.student_id = m.student_id
    WHERE e.student_id IN (SELECT student_id FROM joins WHERE club_id = $club_id) LIMIT 1");
$current_executive = mysqli_fetch_assoc($exec_res);

$sup_res = mysqli_query($conn, "SELECT s.sup_id, s.student_id, s.org_id, m.first_name, m.last_name
    FROM supervisor s JOIN members m ON s.student_id = m.student_id
    WHERE s.student_id IN (SELECT student_id FROM joins WHERE club_id = $club_id) LIMIT 1");
$current_supervisor = mysqli_fetch_assoc($sup_res);
?>

<h2>Assign Roles</h2>

<!-- PRESIDENT -->
<div style="margin-bottom: 30px; margin-top: 30px; text-align: left;">

<h3 style="text-align: left; color: #000;">President</h3>
<?php if ($current_president) { ?>
  <div>
    <strong>Current President:</strong>
    <?php echo htmlspecialchars($current_president['first_name'] . " " . $current_president['last_name']); 
    echo " (Student ID: " . $current_president['student_id'] . ")";
    echo ", Volunteer ID: " . $current_president['vol_id'];
    echo ", President ID: " . $current_president['president_id'];
    ?>
    <form method="post" style="display:inline; margin-left:10px;">
      <input type="hidden" name="delete_role" value="president" />
      <input type="hidden" name="role_id" value="<?php echo $current_president['president_id']; ?>" />
      <button type="submit" onclick="return confirm('Remove this President role?')">Delete Role</button>
    </form>
  </div>
<?php } else { ?>
  <form method="post">
    <select name="president_student_id" required>
      <option value="">Select Student</option>
      <?php
      mysqli_data_seek($volunteers_res, 0);
      while ($vol = mysqli_fetch_assoc($volunteers_res)) {
        echo '<option value="' . $vol['student_id'] . '">' .
             htmlspecialchars($vol['first_name'] . ' ' . $vol['last_name'] . " (ID:" . $vol['student_id'] . ")") .
             '</option>';
      }
      ?>
    </select>
    <button type="submit" name="assign_president">Assign President</button>
  </form>
<?php } ?>
</div>

<!-- EXECUTIVE -->
<div style="margin-bottom: 30px; text-align: left;">
<h3 style="text-align: left; color: #000;">Executive</h3>
<?php if ($current_executive) { ?>
  <div>
    <strong>Current Executive:</strong>
    <?php echo htmlspecialchars($current_executive['first_name'] . " " . $current_executive['last_name']);
    echo " (Student ID: " . $current_executive['student_id'] . ")";
    echo ", Volunteer ID: " . $current_executive['vol_id'];
    echo ", Executive ID: " . $current_executive['exec_id'];
      ?>
    <form method="post" style="display:inline; margin-left:10px;">
      <input type="hidden" name="delete_role" value="executive" />
      <input type="hidden" name="role_id" value="<?php echo $current_executive['exec_id']; ?>" />
      <button type="submit" onclick="return confirm('Remove this Executive role?')">Delete Role</button>
    </form>
  </div>
<?php } else { ?>
  <form method="post">
    <select name="executive_student_id" required>
      <option value="">Select Student</option>
      <?php
      mysqli_data_seek($volunteers_res, 0);
      while ($vol = mysqli_fetch_assoc($volunteers_res)) {
        echo '<option value="' . $vol['student_id'] . '">' .
             htmlspecialchars($vol['first_name'] . ' ' . $vol['last_name'] . " (ID:" . $vol['student_id'] . ")") .
             '</option>';
      }
      ?>
    </select>
    <button type="submit" name="assign_executive">Assign Executive</button>
  </form>
<?php } ?>
</div>

<!-- SUPERVISOR -->
<div style="margin-bottom: 30px; text-align: left;">
<h3 style="text-align: left; color: #000;">Supervisor</h3>
<?php if ($current_supervisor) { ?>
  <div>
    <strong>Current Supervisor:</strong>
    <?php echo htmlspecialchars($current_supervisor['first_name'] . " " . $current_supervisor['last_name']); 
    echo " (Student ID: " . $current_supervisor['student_id'] . ")";
    echo ", Organizer ID: " . $current_supervisor['org_id'];
    echo ", Supervisor ID: " . $current_supervisor['sup_id'];
    ?>
    <form method="post" style="display:inline; margin-left:10px;">
      <input type="hidden" name="delete_role" value="supervisor" />
      <input type="hidden" name="role_id" value="<?php echo $current_supervisor['sup_id']; ?>" />
      <button type="submit" onclick="return confirm('Remove this Supervisor role?')">Delete Role</button>
    </form>
  </div>
<?php } else { ?>
  <form method="post">
    <select name="supervisor_student_id" required>
      <option value="">Select Student</option>
      <?php
      mysqli_data_seek($organizers_res, 0);
      while ($org = mysqli_fetch_assoc($organizers_res)) {
        echo '<option value="' . $org['student_id'] . '">' .
             htmlspecialchars($org['first_name'] . ' ' . $org['last_name'] . " (ID:" . $org['student_id'] . ")") .
             '</option>';
      }
      ?>
    </select>
    <button type="submit" name="assign_supervisor">Assign Supervisor</button>
  </form>
<?php } ?>
</div>

<!-- APPROVE JOIN REQUESTS -->
<h2>Approve Join Requests</h2>
<?php if (mysqli_num_rows($join_requests_res) > 0) { ?>
<table>
  <thead>
    <tr>
      <th>Student Name</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($jr = mysqli_fetch_assoc($join_requests_res)) { ?>
    <tr>
      <td><?php echo htmlspecialchars($jr['first_name'] . ' ' . $jr['last_name']); ?></td>
      <td>
        <form method="post" style="display:inline-block;">
          <input type="hidden" name="student_id" value="<?php echo $jr['student_id']; ?>" />
          <button type="submit" name="approve_join">Approve</button>
        </form>
        <form method="post" style="display:inline-block;">
          <input type="hidden" name="student_id" value="<?php echo $jr['student_id']; ?>" />
          <button type="submit" name="disapprove_join" onclick="return confirm('Are you sure you want to disapprove this join request?')">Disapprove</button>
        </form>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>
<?php } else { ?>
<p>No pending join requests.</p>
<?php } ?>

</div>

<div class="logout-btn" style="top: 20px; right: 20px;">
  <a href="logout.php">Logout</a>
  <form method="get" action="sa_dashboard.php" style="margin-top:10px;">
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
      margin-top: 8px;
    ">Go to Admin Dashboard</button>
  </form>
</div>

</body>
</html>
