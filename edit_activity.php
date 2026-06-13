<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$activity_id = $_GET['id'] ?? 0;
$trip_id = $_GET['trip_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM activity WHERE id = ?");
$stmt->bind_param("i", $activity_id);
$stmt->execute();
$activity = $stmt->get_result()->fetch_assoc();

if(!$activity) {
    header("location: dashboard.php");
    exit();
}

if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("UPDATE activity SET name=?, start_date=?, end_date=?, notes=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $start_date, $end_date, $notes, $activity_id);
    $stmt->execute();
    header("location: activities.php?trip_id=" . $trip_id);
    exit();
}
?>
<!doctype html>
<html>
<head>
    <title>Edit Activity</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Edit Activity</h2>
<form method="post">
    <label>Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($activity['name']); ?>" required>
    <br>
    <label>Start date</label>
    <input type="date" name="start_date" value="<?php echo $activity['start_date']; ?>">
    <br>
    <label>End date</label>
    <input type="date" name="end_date" value="<?php echo $activity['end_date']; ?>">
    <br>
    <label>Notes</label>
    <textarea name="notes" cols="30" rows="3"><?php echo htmlspecialchars($activity['notes']); ?></textarea>
    <br>
    <input type="submit" name="submit" value="Update">
</form>
<a href="activities.php?trip_id=<?php echo $trip_id; ?>">Back</a>
</body>
</html>
