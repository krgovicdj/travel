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
$trip = null;

if(isset($_GET['id'])) {
    $trip_id = $_GET['id'];

    if($user_type == 3) {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trip_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ?");
        $stmt->bind_param("i", $trip_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $trip = $result->fetch_assoc();
}

if(!$trip) {
    header("location: dashboard.php");
    exit();
}

if(isset($_POST['submit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $notes = $_POST['notes'];

    if($user_type == 3) {
        $stmt = $conn->prepare("UPDATE trip SET name=?, status=?, start_date=?, end_date=?, notes=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssii", $name, $status, $start_date, $end_date, $notes, $id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE trip SET name=?, status=?, start_date=?, end_date=?, notes=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $status, $start_date, $end_date, $notes, $id);
    }

    if($stmt->execute()) {
        $_SESSION['success_message'] = "Trip updated!";
        header("location: dashboard.php");
        exit();
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Edit Trip</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Edit Trip</h2>
<form method="post">
    <input type="hidden" name="id" value="<?php echo $trip['id']; ?>">

    <label>Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($trip['name']); ?>" required>
    <br>

    <label>Status</label>
    <select name="status">
        <option value="planirano" <?php echo $trip['status']=='planirano' ? 'selected' : ''; ?>>Planirano</option>
        <option value="u toku" <?php echo $trip['status']=='u toku' ? 'selected' : ''; ?>>U toku</option>
        <option value="završeno" <?php echo $trip['status']=='završeno' ? 'selected' : ''; ?>>Završeno</option>
    </select>
    <br>

    <label>Start date</label>
    <input type="date" name="start_date" value="<?php echo $trip['start_date']; ?>">
    <br>

    <label>End date</label>
    <input type="date" name="end_date" value="<?php echo $trip['end_date']; ?>">
    <br>

    <label>Notes</label>
    <textarea name="notes"><?php echo htmlspecialchars($trip['notes']); ?></textarea>
    <br>

    <input type="submit" value="Update" name="submit">
</form>
<a href="dashboard.php">Back</a>
</body>
</html>