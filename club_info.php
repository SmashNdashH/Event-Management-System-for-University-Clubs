<?php
include 'connection.php';
$club_id = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;

// Fetch club info
$club_res = mysqli_query($conn, "SELECT * FROM clubs WHERE club_id = $club_id");
$club = mysqli_fetch_assoc($club_res);

// Fetch events
$events_res = mysqli_query($conn, "SELECT * FROM events WHERE club_id = $club_id ORDER BY event_date DESC");
$events = [];
if ($events_res && mysqli_num_rows($events_res) > 0) {
    while ($row = mysqli_fetch_assoc($events_res)) {
        $events[] = $row;
    }
}

// Fetch feedbacks for past events
$feedbacks = [];
$feedback_res = mysqli_query($conn, "
    SELECT f.*, e.title AS event_title, e.event_date
    FROM feedback f
    JOIN events e ON f.event_id = e.event_id
    WHERE e.club_id = $club_id AND e.event_date < CURDATE()
    ORDER BY e.event_date DESC
");
if ($feedback_res && mysqli_num_rows($feedback_res) > 0) {
    while ($row = mysqli_fetch_assoc($feedback_res)) {
        $feedbacks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($club['club_name'] ?? 'Club Info'); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 32px; }
        h1 { color: #0056b3; margin-bottom: 18px; }
        .events-list, .feedback-list { margin-bottom: 32px; }
        .event-link { display: block; padding: 10px 0; color: #007BFF; text-decoration: none; font-weight: 500; }
        .event-link:hover { text-decoration: underline; }
        .feedback-item { background: #e0f7fa; border-radius: 8px; padding: 12px 16px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo htmlspecialchars($club['club_name'] ?? 'Club Info'); ?></h1>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($club['status'] ?? ''); ?></p>
    <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($club['registration_date'] ?? ''); ?></p>

    <div class="events-list">
        <h2>Events</h2>
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $evt): ?>
                <a class="event-link" href="event_info.php?event_id=<?php echo (int)$evt['event_id']; ?>">
                    <?php echo htmlspecialchars($evt['title']) . ' (' . htmlspecialchars($evt['event_date']) . ')'; ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No events found for this club.</p>
        <?php endif; ?>
    </div>

    <div class="feedback-list">
        <h2>Past Events' Feedback</h2>
        <?php if (!empty($feedbacks)): ?>
            <?php foreach ($feedbacks as $fb): ?>
                <div class="feedback-item">
                    <strong><?php echo htmlspecialchars($fb['event_title']); ?> (<?php echo htmlspecialchars($fb['event_date']); ?>)</strong><br>
                    <span><?php echo htmlspecialchars($fb['feedback_text']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No feedback found for past events.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>