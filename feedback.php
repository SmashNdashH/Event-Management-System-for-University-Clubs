<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: member_login.php");
    exit;
}
$student_id = (int)$_SESSION['student_id'];

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Validate the event_id and that the current user is registered for it
$event = null;
$is_registered = false;
$already_submitted = false;
$existing_feedback_id = null;
$success_msg = $error_msg = '';
$rating = $comments = $submitted_on = '';

// If event_id is provided, fetch event info and registration status
if ($event_id > 0) {
    // Get event info
    $sql_event = "
        SELECT e.event_id, e.title, e.event_date, e.event_type, c.club_name
        FROM events e
        JOIN clubs c ON c.club_id = e.club_id
        WHERE e.event_id = $event_id
        LIMIT 1
    ";
    $res_event = mysqli_query($conn, $sql_event);
    if ($res_event && mysqli_num_rows($res_event) > 0) {
        $event = mysqli_fetch_assoc($res_event);
    }

    // Check if the student is registered for this event
    $sql_registered = "
        SELECT 1 
        FROM partakes 
        WHERE student_id = $student_id 
          AND event_id = $event_id 
          AND LOWER(attendance_status) = 'registered'
        LIMIT 1
    ";
    $res_registered = mysqli_query($conn, $sql_registered);
    $is_registered = $res_registered && mysqli_num_rows($res_registered) > 0;

    // Check if feedback already submitted for this event by this student
    $sql_existing = "
        SELECT feedback_id 
        FROM feedback
        WHERE student_id = $student_id
          AND event_id = $event_id
        LIMIT 1
    ";
    $res_existing = mysqli_query($conn, $sql_existing);
    if ($res_existing && mysqli_num_rows($res_existing) > 0) {
        $row = mysqli_fetch_assoc($res_existing);
        $already_submitted = true;
        $existing_feedback_id = (int)$row['feedback_id'];
    }
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    // Re-validate that the user can submit for this event
    $sql_registered2 = "
        SELECT 1 
        FROM partakes 
        WHERE student_id = $student_id 
          AND event_id = $event_id 
          AND LOWER(attendance_status) = 'registered'
        LIMIT 1
    ";
    $res_registered2 = mysqli_query($conn, $sql_registered2);
    $is_registered = $res_registered2 && mysqli_num_rows($res_registered2) > 0;

    $sql_existing2 = "
        SELECT feedback_id 
        FROM feedback 
        WHERE student_id = $student_id AND event_id = $event_id
        LIMIT 1
    ";
    $res_existing2 = mysqli_query($conn, $sql_existing2);
    $already_submitted = $res_existing2 && mysqli_num_rows($res_existing2) > 0;
    $existing_feedback_id = null;
    if ($already_submitted) {
        $rxx = mysqli_fetch_assoc($res_existing2);
        $existing_feedback_id = (int)$rxx['feedback_id'];
    }

    // Fetch event again for display
    $sql_event2 = "
        SELECT e.event_id, e.title, e.event_date, e.event_type, c.club_name
        FROM events e
        JOIN clubs c ON c.club_id = e.club_id
        WHERE e.event_id = $event_id
        LIMIT 1
    ";
    $res_event2 = mysqli_query($conn, $sql_event2);
    if ($res_event2 && mysqli_num_rows($res_event2) > 0) {
        $event = mysqli_fetch_assoc($res_event2);
    }

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comments = mysqli_real_escape_string($conn, $_POST['comments'] ?? '');
    $submitted_on = $_POST['submitted_on'] ?? '';

    if ($event_id <= 0 || !$event) {
        $error_msg = "Invalid event.";
    } elseif (!$is_registered) {
        $error_msg = "You are not registered for this event.";
    } elseif ($already_submitted) {
        $error_msg = "Feedback already submitted for this event.";
    } elseif (empty($submitted_on)) {
        $error_msg = "Submitted date is required.";
    } else {
        // Insert into Feedback (your original table)
        $sql = "INSERT INTO feedback (student_id, event_id, rating, comments, submitted_on) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param("iiiss", $student_id, $event_id, $rating, $comments, $submitted_on);
            if ($stmt->execute()) {
                $new_feedback_id = (int)$stmt->insert_id;
                $stmt->close();
                $success_msg = "Feedback submitted successfully!";
                $already_submitted = true;
                $existing_feedback_id = $new_feedback_id;
            } else {
                $error_msg = "Error: " . htmlspecialchars($stmt->error);
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Submit Feedback</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f7fa;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 40px 20px;
        color:#333;
    }
    .card {
        background: #ffffff;
        padding: 28px 32px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 560px;
    }
    h2 { margin: 0 0 22px; color: #333; text-align: center; }
    .event-meta {
        background:#f7fafc;
        border:1px solid #e5e7eb;
        padding:12px 14px;
        border-radius:8px;
        margin-bottom:18px;
    }
    .event-meta div { margin: 4px 0; }
    label { display:block; margin-bottom:8px; font-weight:600; color:#555; }

    input[type="number"],
    input[type="date"],
    textarea {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 18px;
        border: 1.8px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s ease;
        resize: vertical;
        font-family: inherit;
    }
    input[type="number"]:focus,
    input[type="date"]:focus,
    textarea:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 5px rgba(59,130,246,0.3);
    }
    textarea { min-height: 100px; }

    .btn, .back-btn {
        background-color: #3b82f6;
        color: white;
        border: none;
        padding: 12px 0;
        width: 100%;
        font-size: 17px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 10px;
        text-align:center;
        text-decoration:none;
        display:inline-block;
    }
    .btn:hover, .back-btn:hover { background-color: #2563eb; }

    .message {
        padding: 12px 20px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-weight: 600;
        font-size: 15px;
        text-align: center;
    }
    .success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1.5px solid #10b981;
    }
    .error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1.5px solid #dc2626;
    }
    .muted { color:#666; font-size:0.95rem; }
    .badge {
        display:inline-block;
        padding:4px 8px;
        border-radius:6px;
        font-size:0.8rem;
        font-weight:700;
        background:#eef2ff;
        border:1px solid #c7d2fe;
        color:#3730a3;
    }
</style>
</head>
<body>

<div class="card">
    <h2>Submit Feedback</h2>

    <?php if ($success_msg): ?>
        <div class="message success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="message error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if ($event): ?>
      <div class="event-meta">
        <div><strong>Event:</strong> <?= htmlspecialchars($event['title']) ?></div>
        <div><strong>Event ID:</strong> <?= (int)$event['event_id'] ?></div>
        <div><strong>Club:</strong> <?= htmlspecialchars($event['club_name']) ?></div>
        <div><strong>Type / Date:</strong> <?= htmlspecialchars($event['event_type']) ?> &nbsp; | &nbsp; <?= htmlspecialchars($event['event_date']) ?></div>
      </div>
    <?php else: ?>
      <p class="muted">No event selected. Return to dashboard to choose an event.</p>
    <?php endif; ?>

    <?php if ($event && $is_registered && !$already_submitted): ?>
      <form method="post" action="">
          <input type="hidden" name="event_id" value="<?= (int)$event_id ?>" />

          <label for="rating">Rating (0-10):</label>
          <input type="number" id="rating" name="rating" min="0" max="10" value="<?= htmlspecialchars($rating) ?>" />

          <label for="comments">Comments:</label>
          <textarea id="comments" name="comments"><?= htmlspecialchars($comments) ?></textarea>

          <label for="submitted_on">Submitted On:</label>
          <input type="date" id="submitted_on" name="submitted_on" value="<?= htmlspecialchars($submitted_on) ?>" required />

          <button type="submit" class="btn">Submit Feedback</button>
      </form>
    <?php elseif ($event && $already_submitted): ?>
      <div class="message success">Feedback already submitted for this event.</div>
    <?php elseif ($event && !$is_registered): ?>
      <div class="message error">You are not registered for this event.</div>
    <?php endif; ?>

    <a class="back-btn" href="member_dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
