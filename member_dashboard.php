<?php
session_start();
include 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: member_login.php");
    exit;
}

$student_id = (int)$_SESSION['student_id'];

// Fetch member info
$sql_member = "SELECT * FROM members WHERE student_id = $student_id";
$res = mysqli_query($conn, $sql_member);
if (!$res || mysqli_num_rows($res) === 0) {
    die("Member not found.");
}
$member = mysqli_fetch_assoc($res);

// Fetch member emails (multi-valued)
$sql_email = "SELECT email FROM Members_email WHERE student_id = $student_id";
$res_email = mysqli_query($conn, $sql_email);
$emails = [];
if ($res_email && mysqli_num_rows($res_email) > 0) {
    while ($row_email = mysqli_fetch_assoc($res_email)) {
        $emails[] = $row_email['email'];
    }
}

// Fetch member phone numbers (multi-valued)
$sql_phone = "SELECT phone_number FROM Members_phone WHERE student_id = $student_id";
$res_phone = mysqli_query($conn, $sql_phone);
$phone_numbers = [];
if ($res_phone && mysqli_num_rows($res_phone) > 0) {
    while ($row_phone = mysqli_fetch_assoc($res_phone)) {
        $phone_numbers[] = $row_phone['phone_number'];
    }
}

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
// Also LEFT JOIN partakes to know whether THIS student already registered (attendance_status='Registered')
$sql_events = "
  SELECT 
    e.event_id, e.club_id, e.title, e.event_type, e.event_date, e.start_time, e.end_time, e.approval_status,
    c.club_name,
    p.attendance_status AS my_attendance_status
  FROM events e
  JOIN clubs c ON e.club_id = c.club_id
  LEFT JOIN partakes p 
    ON p.event_id = e.event_id 
   AND p.student_id = $student_id
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

    // Check if already attending
    $check_attend_sql = "SELECT * FROM partakes WHERE student_id = $student_id AND event_id = $attend_event_id";
    $check_attend_res = mysqli_query($conn, $check_attend_sql);
    if ($check_attend_res && mysqli_num_rows($check_attend_res) === 0) {
        // Generate a new attendee_id
        $res_max = mysqli_query($conn, "SELECT MAX(attendee_id) AS max_id FROM partakes");
        $max_id = 0;
        if ($res_max && $row_max = mysqli_fetch_assoc($res_max)) {
            $max_id = (int)$row_max['max_id'];
        }
        $new_attendee_id = $max_id + 1;

        // Insert attendance with default status 'Registered'
        $insert_partake_sql = "
            INSERT INTO partakes (student_id, event_id, attendee_id, attendance_status) 
            VALUES ($student_id, $attend_event_id, $new_attendee_id, 'Registered')
        ";
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
    $sql_pres_clubs = "SELECT club_id FROM joins WHERE student_id = $student_id AND join_status IN ('Approved', 'Joined', 'Active')";
    $res_pres_clubs = mysqli_query($conn, $sql_pres_clubs);
    $club_ids = [];
    if ($res_pres_clubs && mysqli_num_rows($res_pres_clubs) > 0) {
        while ($row = mysqli_fetch_assoc($res_pres_clubs)) {
            $club_ids[] = (int)$row['club_id'];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
        // Sanitize and validate input
        $rpt_title = mysqli_real_escape_string($conn, trim($_POST['rpt_title']));
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        $performance_rating = (int)$_POST['performance_rating'];
        $event_id_for_report = (int)$_POST['event_id'];

        // Fetch reportable events (needed for validation)
        $reportable_events = [];
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
            if ($res_reportable && mysqli_num_rows($res_reportable) > 0) {
                while ($evt = mysqli_fetch_assoc($res_reportable)) {
                    $reportable_events[] = $evt;
                }
            }
        }

        // Validate rating range and event choice
        if ($performance_rating < 1 || $performance_rating > 5) {
            $report_error = "Performance rating must be between 1 and 5.";
        } elseif (!in_array($event_id_for_report, array_map(function($e){return (int)$e['event_id'];}, $reportable_events))) {
            $report_error = "Selected event is not eligible for reporting.";
        } else {
            // Insert report
            $insert_report_sql = "
                INSERT INTO report (rpt_title, event_id, description, performance_rating)
                VALUES ('$rpt_title', $event_id_for_report, '$description', $performance_rating)
            ";
            if (mysqli_query($conn, $insert_report_sql)) {
                $report_success = "Report submitted successfully.";
                // Refresh reportable_events after submission
                $reportable_events = [];
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
                    if ($res_reportable && mysqli_num_rows($res_reportable) > 0) {
                        while ($evt = mysqli_fetch_assoc($res_reportable)) {
                            $reportable_events[] = $evt;
                        }
                    }
                }
            } else {
                $report_error = "Failed to submit report: " . mysqli_error($conn);
            }
        }
    } else {
        // Initial fetch of reportable events for form
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
}

/* -------------------------------------------
   FEEDBACK SECTION DATA
   - List events where current user is Registered
   - Left join event_feedback to mark 'Submitted'
-------------------------------------------- */
$sql_feedback_events = "
    SELECT 
        e.event_id,
        e.title,
        e.event_date,
        e.event_type,
        c.club_name,
        p.attendance_status,
        f.feedback_id AS submitted_feedback_id
    FROM partakes p
    JOIN events e ON e.event_id = p.event_id
    JOIN clubs c ON c.club_id = e.club_id
    LEFT JOIN feedback f 
       ON f.student_id = p.student_id 
      AND f.event_id = p.event_id
    WHERE p.student_id = $student_id
      AND LOWER(p.attendance_status) = 'registered'
    ORDER BY e.event_date DESC, e.title ASC
";
$registered_events_for_feedback = [];
$res_fbe = mysqli_query($conn, $sql_feedback_events);
if ($res_fbe && mysqli_num_rows($res_fbe) > 0) {
    while ($row = mysqli_fetch_assoc($res_fbe)) {
        $registered_events_for_feedback[] = $row;
    }
}

// For Attendance table rendering convenience, build a quick set of registered event ids
$registered_event_ids = [];
foreach ($events as $ev) {
    if (!empty($ev['my_attendance_status']) && strtolower($ev['my_attendance_status']) === 'registered') {
        $registered_event_ids[(int)$ev['event_id']] = true;
    }
}

// Handle feedback submission (new code block integrated)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $event_id = (int)$_POST['event_id'];
    $rating = (int)$_POST['rating'];
    $comments = mysqli_real_escape_string($conn, trim($_POST['comments']));
    $submitted_on = date('Y-m-d');

    // Insert feedback into the feedback table
    $sql_feedback = "INSERT INTO feedback (student_id, event_id, rating, comments, submitted_on)
                     VALUES ($student_id, $event_id, $rating, '$comments', '$submitted_on')";
    mysqli_query($conn, $sql_feedback);

    // Redirect to avoid resubmission
    header("Location: member_dashboard.php?msg=Feedback+submitted");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
  .action-btn:hover { background-color: #09255eff; }

  form select[name="apply_club_id"] {
    background-color: transparent;
    color: #007BFF;
    border: 2px solid #007BFF;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: border-color 0.3s ease;
    height: 36px;
    width: 150px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    text-align-last: center;
    margin-right: 8px;
  }
  form select[name="apply_club_id"]:hover { border-color: #09255eff; }

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
    max-width: 1000px;
    margin: auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    position: relative;
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
    padding: 12px 20px;
    background-color: #b52b38;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
  }
  .logout-btn a:hover { background-color: #cc0000; }
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
  label { font-weight: bold; }

  /* Minor badges */
  .badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
  }
  .badge-green { background: #e7f8ee; color:#127c3f; border:1px solid #78d3a5; }
  .badge-gray  { background: #f0f0f0; color:#444; border:1px solid #ddd; }
  .muted { color:#666; font-size:0.9rem; }
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
  <p><strong>Email:</strong>
    <?php
      if (!empty($emails)) {
        echo htmlspecialchars(implode(', ', $emails));
      } else {
        echo 'Not provided';
      }
    ?>
  </p>
  <p><strong>Phone Number:</strong>
    <?php
      if (!empty($phone_numbers)) {
        echo htmlspecialchars(implode(', ', $phone_numbers));
      } else {
        echo 'Not provided';
      }
    ?>
  </p>

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
                <?php if (!empty($event['my_attendance_status']) && strtolower($event['my_attendance_status']) === 'registered'): ?>
                  <span class="badge badge-green">Registered</span>
                <?php else: ?>
                  <button type="submit" name="attend_event_id" value="<?php echo (int)$event['event_id']; ?>" class="action-btn">Attend</button>
                <?php endif; ?>
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
            <option value="<?php echo (int)$evt['event_id']; ?>">
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
      <p class="muted">No past events available for reporting.</p>
    <?php endif; ?>
  <?php endif; ?>

  <h2>Feedback</h2>

  <?php if (!empty($registered_events_for_feedback)): ?>
    <table>
      <thead>
        <tr>
          <th>Event</th>
          <th>Club</th>
          <th>Type</th>
          <th>Date</th>
          <th>Attendance</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($registered_events_for_feedback as $fe): ?>
        <tr>
          <td>
            <?php echo htmlspecialchars($fe['title']); ?>
            <br><span class="muted">ID: <?php echo (int)$fe['event_id']; ?></span>
          </td>
          <td><?php echo htmlspecialchars($fe['club_name']); ?></td>
          <td><?php echo htmlspecialchars($fe['event_type']); ?></td>
          <td><?php echo htmlspecialchars($fe['event_date']); ?></td>
          <td>
            <?php if (strtolower($fe['attendance_status']) === 'registered'): ?>
              <span class="badge badge-green">Registered</span>
            <?php else: ?>
              <span class="badge badge-gray"><?php echo htmlspecialchars($fe['attendance_status']); ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($fe['submitted_feedback_id'])): ?>
              <span class="badge badge-green">Submitted</span>
            <?php else: ?>
              <a 
                class="action-btn" 
                href="feedback.php?event_id=<?php echo (int)$fe['event_id']; ?>">
                Submit Feedback
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="muted">No registered events found to give feedback yet.</p>
  <?php endif; ?>

  <?php
  // Determine if member should see Resources section
  $show_resources_section = false;

  // Only allow if member is Organizer AND membership status is Approved/Joined/Active
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
                  <a href="resources.php?club_id=<?php echo (int)$club['club_id']; ?>" 
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
</body>
</html>
