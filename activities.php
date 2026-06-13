<?php
session_start();
require "connection.php";
global $conn;

if (!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$trip_id = $_GET['trip_id'] ?? 0;

$trip = null;
if ($trip_id) {
    if ($user_type == 3) {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trip_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ?");
        $stmt->bind_param("i", $trip_id);
    }
    $stmt->execute();
    $trip = $stmt->get_result()->fetch_assoc();
}

if (!$trip) {
    header("location: dashboard.php");
    exit();
}

if (isset($_POST['add_activity'])) {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO activity (name, start_date, end_date, notes, trip_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $start_date, $end_date, $notes, $trip_id);
    $stmt->execute();
    header("location: activities.php?trip_id=" . $trip_id);
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM activity WHERE id = ? AND trip_id = ?");
    $stmt->bind_param("ii", $delete_id, $trip_id);
    $stmt->execute();
    header("location: activities.php?trip_id=" . $trip_id);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM activity WHERE trip_id = ? ORDER BY start_date");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$activities = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
    <title>Activities - <?php echo htmlspecialchars($trip['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Activities for: <?php echo htmlspecialchars($trip['name']); ?></h2>
<a href="edit_trip.php?id=<?php echo $trip_id; ?>">Back to Trip</a>
<a href="dashboard.php">Dashboard</a>
<hr>

<h3>Add Activity</h3>
<form method="post">
    <label>Name</label>
    <input type="text" name="name" required>
    <br>
    <label>Start date</label>
    <input type="date" name="start_date">
    <br>
    <label>End date</label>
    <input type="date" name="end_date">
    <br>
    <label>Notes</label>
    <textarea name="notes" cols="30" rows="3"></textarea>
    <br>
    <input type="submit" name="add_activity" value="Add Activity">
</form>

<hr>

<h3>All Activities</h3>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Start</th>
        <th>End</th>
        <th>Notes</th>
        <th>Actions</th>
    </tr>
    <?php
    while ($act = $activities->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($act['name']) . "</td>";
        echo "<td>" . $act['start_date'] . "</td>";
        echo "<td>" . $act['end_date'] . "</td>";
        echo "<td>" . htmlspecialchars($act['notes']) . "</td>";
        echo "<td>";
        echo "<a href='edit_activity.php?id=" . $act['id'] . "&trip_id=" . $trip_id . "'>✏️</a> ";
        echo "<a href='?trip_id=" . $trip_id . "&delete_id=" . $act['id'] . "'>🗑️</a>";
        echo "</td>";
        echo "</tr>";
    }

    if ($activities->num_rows == 0) {
        echo "<tr><td colspan='5'>No activities yet.</td></tr>";
    }
    ?>
</table>
</body>
</html>
