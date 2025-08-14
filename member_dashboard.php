<?php
session_start();
include 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: member_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch member info
$sql_member = "SELECT * FROM members WHERE student_id = $student_id";
$res = mysqli_query($conn, $sql_member);
if (!$res || mysqli_num_rows($res) === 0) {
    die("Member not found.");
}
$member = mysqli_fetch_assoc($res);

// Determine position and role
$position = '';
$role = '';
$id_value = '';

// Check President
$sql_pres = "SELECT * FROM president WHERE student_id = $student_id";
$res_pres = mysqli_query($conn, $sql_pres);
if ($res_pres && mysqli_num_rows($res_pres) > 0) {
    $pres = mysqli_fetch_assoc($res_pres);
    $position = 'Volunteer';
    $role = 'President';
    $id_value = $pres['president_id'];
} else {
    // Check Executive
    $sql_exec = "SELECT * FROM executive WHERE student_id = $student_id";
    $res_exec = mysqli_query($conn, $sql_exec);
    if ($res_exec && mysqli_num_rows($res_exec) > 0) {
        $exec = mysqli_fetch_assoc($res_exec);
        $position = 'Volunteer';
        $role = 'Executive';
        $id_value = $exec['exec_id'];
    } else {
        // Check Supervisor
        $sql_sup = "SELECT * FROM supervisor WHERE student_id = $student_id";
        $res_sup = mysqli_query($conn, $sql_sup);
        if ($res_sup && mysqli_num_rows($res_sup) > 0) {
            $sup = mysqli_fetch_assoc($res_sup);
            $position = 'Organizer';
            $role = 'Supervisor';
            $id_value = $sup['sup_id'];
        } else {
            // Else check Organizer
            $sql_org = "SELECT * FROM organizer WHERE student_id = $student_id";
            $res_org = mysqli_query($conn, $sql_org);
            if ($res_org && mysqli_num_rows($res_org) > 0) {
                $org = mysqli_fetch_assoc($res_org);
                $position = 'Organizer';
                $role = ''; // No special role
                $id_value = $org['org_id'];
            } else {
                // Else check Volunteer
                $sql_vol = "SELECT * FROM volunteer WHERE student_id = $student_id";
                $res_vol = mysqli_query($conn, $sql_vol);
                if ($res_vol && mysqli_num_rows($res_vol) > 0) {
                    $vol = mysqli_fetch_assoc($res_vol);
                    $position = 'Volunteer';
                    $role = ''; // No special role
                    $id_value = $vol['vol_id'];
                } else {
                    $position = 'None';
                    $role = '';
                    $id_value = '';
                }
            }
        }
    }
}

// Fetch clubs joined by member with join_date and join_status
$sql_joins = "
    SELECT j.club_id, j.join_date, j.join_status, c.club_name, c.registration_date, c.status
    FROM joins j
    JOIN clubs c ON j.club_id = c.club_id
    WHERE j.student_id = $student_id
";
$joined_clubs = [];
$res_joins = mysqli_query($conn, $sql_joins);
if ($res_joins && mysqli_num_rows($res_joins) > 0) {
    while ($row = mysqli_fetch_assoc($res_joins)) {
        $joined_clubs[] = $row;
    }
}

// Fetch all clubs for apply option
$sql_all_clubs = "SELECT * FROM clubs ORDER BY club_name ASC";
$all_clubs = [];
$res_all_clubs = mysqli_query($conn, $sql_all_clubs);
if ($res_all_clubs && mysqli_num_rows($res_all_clubs) > 0) {
    while ($row = mysqli_fetch_assoc($res_all_clubs)) {
        $all_clubs[] = $row;
    }
}

// Handle club join application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_club_id'])) {
    $apply_club_id = (int)$_POST['apply_club_id'];

// // Handle cancel join request
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_join_club_id'])) {
//     $cancel_club_id = (int)$_POST['cancel_join_club_id'];

//     // Only allow cancellation if status is 'Pending'
//     $check_sql = "SELECT * FROM joins WHERE student_id = $student_id AND club_id = $cancel_club_id AND join_status='Pending'";
//     $check_res = mysqli_query($conn, $check_sql);

//     if ($check_res && mysqli_num_rows($check_res) > 0) {
//         $delete_sql = "DELETE FROM joins WHERE student_id = $student_id AND club_id = $cancel_club_id AND join_status='Pending'";
//         if (mysqli_query($conn, $delete_sql)) {
//             header("Location: member_dashboard.php?msg=Join+request+cancelled");
//             exit;
//         } else {
//             $error = "Failed to cancel join request: " . mysqli_error($conn);
//         }
//     } else {
//         $error = "Cannot cancel this join request.";
//     }
// }


    // Check if already joined or pending
    $check_sql = "SELECT * FROM joins WHERE student_id = $student_id AND club_id = $apply_club_id";
    $check_res = mysqli_query($conn, $check_sql);
    if ($check_res && mysqli_num_rows($check_res) === 0) {
        // Insert join with join_status as 'Pending' and current date
        $insert_sql = "INSERT INTO joins (student_id, club_id, join_date, join_status) VALUES ($student_id, $apply_club_id, CURDATE(), 'Pending')";
        mysqli_query($conn, $insert_sql);
        header("Location: member_dashboard.php?msg=Application+submitted");
        exit;
    } else {
        $error = "You have already applied or joined this club.";
    }
}

// Fetch upcoming approved events with their club names
$sql_events = "
  SELECT e.event_id, e.club_id, e.title, e.event_type, e.event_date, e.start_time, e.end_time, e.approval_status,
         c.club_name
  FROM events e
  JOIN clubs c ON e.club_id = c.club_id
  WHERE e.event_date >= CURDATE()
    AND LOWER(e.approval_status) = 'approved'
  ORDER BY e.event_date ASC, e.start_time ASC
";
$events = [];
$res_events = mysqli_query($conn, $sql_events);
if ($res_events && mysqli_num_rows($res_events) > 0) {
    while ($row = mysqli_fetch_assoc($res_events)) {
        $events[] = $row;
    }
}

// Handle event attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attend_event_id'])) {
    $attend_event_id = (int)$_POST['attend_event_id'];

    // Generate a new attendee_id
    $res_max = mysqli_query($conn, "SELECT MAX(attendee_id) AS max_id FROM partakes");
    $max_id = 0;
    if ($res_max && $row_max = mysqli_fetch_assoc($res_max)) {
        $max_id = (int)$row_max['max_id'];
    }
    $new_attendee_id = $max_id + 1;

    // Check if already attending
    $check_attend_sql = "SELECT * FROM partakes WHERE student_id = $student_id AND event_id = $attend_event_id";
    $check_attend_res = mysqli_query($conn, $check_attend_sql);
    if ($check_attend_res && mysqli_num_rows($check_attend_res) === 0) {
        // Insert attendance with default status 'Registered'
        $insert_partake_sql = "INSERT INTO partakes (student_id, event_id, attendee_id, attendance_status) VALUES ($student_id, $attend_event_id, $new_attendee_id, 'Registered')";
        mysqli_query($conn, $insert_partake_sql);
        header("Location: member_dashboard.php?msg=Event+attendance+registered");
        exit;
    } else {
        $error = "You are already registered for this event.";
    }
}

// PRESIDENT REPORT SUBMISSION LOGIC (Only for president role)
$report_error = '';
$report_success = '';
$reportable_events = [];

if ($role === 'President') {
    // Get club ids where this president is joined with status Approved or Joined
    $sql_pres_clubs = "SELECT club_id FROM joins WHERE student_id = $student_id AND join_status IN ('Approved', 'Joined')";
    $res_pres_clubs = mysqli_query($conn, $sql_pres_clubs);
    $club_ids = [];
    if ($res_pres_clubs && mysqli_num_rows($res_pres_clubs) > 0) {
        while ($row = mysqli_fetch_assoc($res_pres_clubs)) {
            $club_ids[] = $row['club_id'];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
        // Sanitize and validate input
        $rpt_title = mysqli_real_escape_string($conn, trim($_POST['rpt_title']));
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        $performance_rating = (int)$_POST['performance_rating'];
        $event_id = (int)$_POST['event_id'];

        // Validate rating range
        if ($performance_rating < 1 || $performance_rating > 5) {
            $report_error = "Performance rating must be between 1 and 5.";
        } elseif (!in_array($event_id, array_column($reportable_events, 'event_id')) && !empty($club_ids)) {
            // To check if event_id is allowed, we first need to fetch reportable events below
            // We'll do that shortly before this condition check, so this needs reordering
        } else {
            // Insert report with auto-increment id
            $insert_report_sql = "
                INSERT INTO report (rpt_title, event_id, description, performance_rating)
                VALUES ('$rpt_title', $event_id, '$description', $performance_rating)
            ";
            if (mysqli_query($conn, $insert_report_sql)) {
                $report_success = "Report submitted successfully.";
                // Refresh reportable_events after submission
            } else {
                $report_error = "Failed to submit report: " . mysqli_error($conn);
            }
        }
    }

    // Fetch reportable events (events in clubs president joined, past events with no report)
    if (!empty($club_ids)) {
        $club_ids_list = implode(',', $club_ids);
        $sql_reportable_events = "
            SELECT e.event_id, e.title, e.event_date
            FROM events e
            LEFT JOIN report r ON e.event_id = r.event_id
            WHERE e.club_id IN ($club_ids_list)
              AND e.event_date < CURDATE()
              AND r.event_id IS NULL
            ORDER BY e.event_date DESC
        ";
        $res_reportable = mysqli_query($conn, $sql_reportable_events);
        $reportable_events = [];
        if ($res_reportable && mysqli_num_rows($res_reportable) > 0) {
            while ($evt = mysqli_fetch_assoc($res_reportable)) {
                $reportable_events[] = $evt;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- <link rel="stylesheet" href="style.css"> -->
<meta charset="UTF-8">
<title>Member Dashboard</title>
<style>

.action-btn {
  background-color: #007BFF;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.action-btn:hover {
  background-color: #09255eff; /* Slightly darker on hover */
}

form select[name="apply_club_id"] {
  background-color: transparent;
  color: #007BFF;
  border: 2px solid #007BFF;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: border-color 0.3s ease;
  height: 36px; /* Match button height approximately */
  width: 150px; /* Match button width */
  appearance: none; /* Remove default dropdown arrow for consistency */
  -webkit-appearance: none;
  -moz-appearance: none;
  text-align-last: center; /* Center selected option text */
  margin-right: 8px; /* small spacing between dropdown and button */
}
form select[name="apply_club_id"]:hover {
  border-color: #09255eff;
}

  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
    margin: 0;
    min-height: 100vh;
    padding: 20px;
    color: #333;
  }
  .container {
    background: white;
    border-radius: 10px;
    padding: 30px;
    max-width: 900px;
    margin: auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  h1 {
    text-align: center;
    color: #0056b3;
    margin-bottom: 20px;
  }
  h2 {
    color: #007BFF;
    border-bottom: 2px solid #eee;
    padding-bottom: 5px;
    margin-top: 30px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }
  th, td {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 0.95rem;
  }
  th {
    background-color: #f4f4f4;
    text-align: left;
  }
   .logout-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    display: block;
    margin-top: 25px;
    text-align: center;
  }
  .logout-btn a {
    display: inline-block;
    padding: 8px 14px;
    background-color: #b52b38;
;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
  }
  .logout-btn a:hover {
    background-color: #cc0000;
  }
  .msg {
    background-color: #d4edda;
    color: #155724;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 15px;
  }
  .error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 15px;
  }
  label {
    font-weight: bold;
  }
</style>
</head>
<body>

<div class="container">

  <?php if (isset($_GET['msg'])): ?>
    <div class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($report_error)): ?>
    <div class="error"><?php echo htmlspecialchars($report_error); ?></div>
  <?php endif; ?>

  <?php if (!empty($report_success)): ?>
    <div class="msg"><?php echo htmlspecialchars($report_success); ?></div>
  <?php endif; ?>

  <h1>
    Welcome, <?php echo htmlspecialchars($member['first_name'] . " " . $member['last_name']) . " (" . htmlspecialchars($student_id) . ")"; ?>
  </h1>

  <p><strong><?php echo ($position === 'Organizer') ? 'Organizer ID' : 'Volunteer ID'; ?>:</strong> <?php echo htmlspecialchars($id_value); ?></p>
  <p><strong>Position:</strong> <?php echo htmlspecialchars($position); ?></p>
  <p><strong>Role:</strong> <?php echo htmlspecialchars($role ?: 'No role'); ?></p>

<h2>Your Joined Clubs</h2>
<?php if (!empty($joined_clubs)): ?>
    <table>
        <thead>
            <tr>
                <th>Club Name</th>
                <th>Registration Date</th>
                <th>Club Status</th>
                <th>Join Date</th>
                <th>Join Status</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($joined_clubs as $jc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($jc['club_name']); ?></td>
                    <td><?php echo htmlspecialchars($jc['registration_date']); ?></td>
                    <td><?php echo htmlspecialchars($jc['status']); ?></td>
                    <td><?php echo htmlspecialchars($jc['join_date']); ?></td>
                    <td><?php echo htmlspecialchars($jc['join_status']); ?></td>
                
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have not joined any clubs yet.</p>
<?php endif; ?>


  <h2>Apply to Join New Clubs</h2>
  <form method="post" style="display: flex; align-items: center;">
    <select name="apply_club_id" required>
      <option value="">-- Select Club --</option>
      <?php foreach ($all_clubs as $club): ?>
        <option value="<?php echo $club['club_id']; ?>">
          <?php echo htmlspecialchars($club['club_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="action-btn">Apply</button>
  </form>

  <h2>Available Events to Attend</h2>
  <?php if (!empty($events)): ?>
    <form method="post">
      <table>
        <thead>
          <tr>
            <th>Event Title</th>
            <th>Club</th>
            <th>Event Type</th>
            <th>Event Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): ?>
            <tr>
              <td><?php echo htmlspecialchars($event['title']); ?></td>
              <td><?php echo htmlspecialchars($event['club_name']); ?></td>
              <td><?php echo htmlspecialchars($event['event_type']); ?></td>
              <td><?php echo htmlspecialchars($event['event_date']); ?></td>
              <td><?php echo htmlspecialchars($event['start_time']); ?></td>
              <td><?php echo htmlspecialchars($event['end_time']); ?></td>
              <td>
                <button type="submit" name="attend_event_id" value="<?php echo $event['event_id']; ?>" class="action-btn">Attend</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </form>
  <?php else: ?>
    <p>No upcoming approved events available.</p>
  <?php endif; ?>

  <?php if ($role === 'President'): ?>
    <h2>Submit Report for Your Club's Events</h2>
    <?php if (!empty($reportable_events)): ?>
      <form method="post">
        <label for="event_id">Select Event:</label><br>
        <select name="event_id" id="event_id" required>
          <option value="">-- Select Event --</option>
          <?php foreach ($reportable_events as $evt): ?>
            <option value="<?php echo $evt['event_id']; ?>">
              <?php echo htmlspecialchars($evt['title'] . ' (' . $evt['event_date'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select><br><br>

        <label for="rpt_title">Report Title:</label><br>
        <input type="text" name="rpt_title" id="rpt_title" required maxlength="100" style="width: 100%; padding: 6px;"><br><br>

        <label for="description">Description:</label><br>
        <textarea name="description" id="description" rows="5" style="width: 100%; padding: 6px;"></textarea><br><br>

        <label for="performance_rating">Performance Rating (1 to 5):</label><br>
        <input type="number" name="performance_rating" id="performance_rating" min="1" max="5" value="3" required><br><br>

        <button type="submit" name="submit_report" class="action-btn">Submit Report</button>
      </form>
    <?php else: ?>
      <p>No past events available for reporting.</p>
    <?php endif; ?>
  <?php endif; ?>



  <h2>Feedback</h2>
<p>
  <a href="feedback.php" class="action-btn" style="text-decoration:none; display:inline-block; margin-top:10px;">
    Submit Feedback
  </a>

</p>

<?php
// Determine if member should see Resources section
$show_resources_section = false;

// Only allow if member is Organizer AND membership status is Approved/Joined
if ($position === 'Organizer' && !empty($id_value)) {
    foreach ($joined_clubs as $club) {
        if (in_array($club['join_status'], ['Approved', 'Active', 'Joined'])) {
            $show_resources_section = true;
            break; // Only need one club to allow section
        }
    }
}

?>

<?php if ($show_resources_section && !empty($joined_clubs)): ?>
    <h2>Club Resources</h2>
    <?php foreach ($joined_clubs as $club): ?>
        <?php if (in_array($club['join_status'], ['Approved', 'Active', 'Joined'])): ?>
            <p>
                <a href="resources.php?club_id=<?php echo $club['club_id']; ?>" 
                   class="action-btn" 
                   style="text-decoration:none; display:inline-block; margin-top:10px;">
                    Resources for <?php echo htmlspecialchars($club['club_name']); ?>
                </a>
            </p>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>


</div>

<div class="logout-btn">
    <a href="logout.php">Logout</a>
</div>

</body>
</html>
