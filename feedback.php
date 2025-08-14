<?php
include 'connection.php';

$student_id = ""; // Will be set from session if available
session_start();
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
}

$rating = $comments = $submitted_on = "";
$success_msg = $error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = intval($_POST['rating']);
    $comments = $conn->real_escape_string($_POST['comments']);
    $submitted_on = $_POST['submitted_on'];

    if (empty($student_id) || empty($submitted_on)) {
        $error_msg = "Student ID and Submitted Date are required.";
    } else {
        $sql = "INSERT INTO Feedback (student_id, rating, comments, submitted_on) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param("iiss", $student_id, $rating, $comments, $submitted_on);
            if ($stmt->execute()) {
                $success_msg = "Feedback submitted successfully!";
                $rating = $comments = $submitted_on = "";
            } else {
                $error_msg = "Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Submit Feedback</title>
<style>
    /* Reset some defaults */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f7fa;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 40px 20px;
    }

    form {
        background: #ffffff;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
    }

    h2 {
        margin-bottom: 25px;
        color: #333;
        text-align: center;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }

    input[type="number"],
    input[type="date"],
    textarea {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
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
        box-shadow: 0 0 5px rgba(59,130,246,0.4);
    }

    textarea {
        min-height: 90px;
    }

    input[type="submit"], .back-btn {
        background-color: #3b82f6;
        color: white;
        border: none;
        padding: 14px 0;
        width: 100%;
        font-size: 18px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 15px;
    }

    input[type="submit"]:hover, .back-btn:hover {
        background-color: #2563eb;
    }

    .message {
        padding: 12px 20px;
        border-radius: 6px;
        margin-bottom: 20px;
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
</style>
</head>
<body>

<form method="post" action="">
    <h2>Submit Feedback</h2>

    <?php if ($success_msg): ?>
        <div class="message success"><?= $success_msg ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="message error"><?= $error_msg ?></div>
    <?php endif; ?>

    <label for="student_id">Student ID:</label>
    <input type="number" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" readonly />

    <label for="rating">Rating (0-10):</label>
    <input type="number" id="rating" name="rating" min="0" max="10" value="<?= htmlspecialchars($rating) ?>" />

    <label for="comments">Comments:</label>
    <textarea id="comments" name="comments"><?= htmlspecialchars($comments) ?></textarea>

    <label for="submitted_on">Submitted On:</label>
    <input type="date" id="submitted_on" name="submitted_on" value="<?= htmlspecialchars($submitted_on) ?>" required />

    <input type="submit" value="Submit Feedback" />
    
    <button type="button" class="back-btn" onclick="window.location.href='member_dashboard.php'">
        Back to Dashboard
    </button>
</form>

</body>
</html>