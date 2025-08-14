<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'connection.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: member_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Validate club_id from GET
if (!isset($_GET['club_id']) || !is_numeric($_GET['club_id'])) {
    die("Invalid club ID.");
}
$club_id = intval($_GET['club_id']);

// Fetch club info
$club_res = mysqli_query($conn, "SELECT club_name FROM clubs WHERE club_id = $club_id");
if ($club_res && mysqli_num_rows($club_res) > 0) {
    $club_row = mysqli_fetch_assoc($club_res);
    $club_name = $club_row['club_name'];
} else {
    die("Club not found.");
}

$message = '';
$error = '';

// Handle add resource form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $name = mysqli_real_escape_string($conn, $_POST['resource_name']);
    $type = mysqli_real_escape_string($conn, $_POST['resource_type']);
    $status = $_POST['availability_status'];
    $qty = intval($_POST['quantity']);

    $sql = "INSERT INTO resources (club_id, resource_name, resource_type, availability_status, quantity)
            VALUES ($club_id, '$name', '$type', '$status', $qty)";
    if (mysqli_query($conn, $sql)) {
        $message = "Resource added successfully.";
    } else {
        $error = "Error adding resource: " . mysqli_error($conn);
    }
}

// Handle resource update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resource_id'])) {
    $res_id = (int)$_POST['update_resource_id'];
    $name = mysqli_real_escape_string($conn, $_POST['resource_name']);
    $type = mysqli_real_escape_string($conn, $_POST['resource_type']);
    $status = $_POST['availability_status'];
    $qty = intval($_POST['quantity']);

    $update_sql = "
        UPDATE resources 
        SET resource_name='$name', resource_type='$type', availability_status='$status', quantity=$qty
        WHERE resource_id=$res_id AND club_id=$club_id
    ";
    if (mysqli_query($conn, $update_sql)) {
        $message = "Resource updated successfully.";
    } else {
        $error = "Error updating resource: " . mysqli_error($conn);
    }
}

// Fetch resources for this club
$resources = [];
$res = mysqli_query($conn, "SELECT * FROM resources WHERE club_id=$club_id ORDER BY resource_name ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $resources[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Club Resources</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
    margin: 0;
    padding: 20px;
    color: #333;
    min-height: 100vh;
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
.action-btn {
    background-color: #007BFF;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
}
.action-btn:hover {
    background-color: #09255eff;
}
form input, form select {
    padding: 6px 8px;
    margin-right: 8px;
    border-radius: 6px;
    border: 1px solid #007BFF;
    font-size: 0.9rem;
}
form input[type="number"] {
    width: 100px;
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
</style>
</head>
<body>
<div class="container">
    <h1>Resources for <?php echo htmlspecialchars($club_name); ?> (ID: <?php echo htmlspecialchars($club_id); ?>)</h1>

    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <h2>Add New Resource</h2>
    <form method="POST">
        <input type="hidden" name="add_resource" value="1">
        <input type="text" name="resource_name" placeholder="Resource Name" required>
        <input type="text" name="resource_type" placeholder="Resource Type" required>
        <select name="availability_status">
            <option value="Available">Available</option>
            <option value="In Use">In Use</option>
            <option value="Unavailable">Unavailable</option>
        </select>
        <input type="number" name="quantity" placeholder="Quantity" min="1" required>
        <button type="submit" class="action-btn">Add Resource</button>
    </form>

    <h2>Existing Resources</h2>
    <?php if (!empty($resources)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $r): ?>
                    <tr>
                        <form method="post">
                            <td><?php echo htmlspecialchars($r['resource_id']); ?></td>
                            <td>
                                <input type="text" name="resource_name" value="<?php echo htmlspecialchars($r['resource_name']); ?>" required>
                            </td>
                            <td>
                                <input type="text" name="resource_type" value="<?php echo htmlspecialchars($r['resource_type']); ?>" required>
                            </td>
                            <td>
                                <select name="availability_status">
                                    <option value="Available" <?php if($r['availability_status']=='Available') echo 'selected'; ?>>Available</option>
                                    <option value="In Use" <?php if($r['availability_status']=='In Use') echo 'selected'; ?>>In Use</option>
                                    <option value="Unavailable" <?php if($r['availability_status']=='Unavailable') echo 'selected'; ?>>Unavailable</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($r['quantity']); ?>" min="1" required>
                            </td>
                            <td>
                                <button type="submit" name="update_resource_id" value="<?php echo $r['resource_id']; ?>" class="action-btn">Update</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No resources added yet.</p>
    <?php endif; ?>

    <p>
        <a href="member_dashboard.php" class="action-btn" style="text-decoration:none;">Back to Dashboard</a>
    </p>
</div>
</body>
</html>
