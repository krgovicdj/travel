<?php
session_start();
require "connection.php";

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$trip_id = $_GET['trip_id'] ?? 0;

// Dohvati putovanje (samo ako user ima pravo)
$trip = null;
if($trip_id) {
    if($user_type == 3) {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trip_id, $user_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ?");
        $stmt->bind_param("i", $trip_id);
    }
    $stmt->execute();
    $trip = $stmt->get_result()->fetch_assoc();
}

if(!$trip) {
    header("location: dashboard.php");
    exit();
}

// CREATE - Dodaj destinaciju
if(isset($_POST['add_destination'])) {
    $dest_id = $_POST['destination_id'];
    $stmt = $conn->prepare("INSERT INTO trip_destination (trip_id, destination_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $trip_id, $dest_id);
    $stmt->execute();
}

// DELETE - Ukloni destinaciju
if(isset($_GET['remove'])) {
    $dest_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM trip_destination WHERE trip_id = ? AND destination_id = ?");
    $stmt->bind_param("ii", $trip_id, $dest_id);
    $stmt->execute();
    header("location: manage_destinations.php?trip_id=" . $trip_id);
    exit();
}

// READ - Dohvati sve destinacije
$all_destinations = $conn->query("SELECT * FROM destination ORDER BY city");

// READ - Dohvati destinacije koje već pripadaju putovanju
$stmt = $conn->prepare("SELECT d.* FROM destination d JOIN trip_destination td ON d.id = td.destination_id WHERE td.trip_id = ?");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$trip_destinations = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
    <title>Manage Destinations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Manage Destinations for: <?php echo htmlspecialchars($trip['name']); ?></h2>
<a href="edit_trip.php?id=<?php echo $trip_id; ?>">Back to Edit Trip</a>
<a href="dashboard.php">Dashboard</a>
<hr>

<h3>Add Destination</h3>
<form method="post">
    <select name="destination_id" required>
        <option value="">Select destination</option>
        <?php while($dest = $all_destinations->fetch_assoc()): ?>
            <option value="<?php echo $dest['id']; ?>">
                <?php echo htmlspecialchars($dest['city'] . ', ' . $dest['country']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <input type="submit" name="add_destination" value="Add">
</form>

<hr>

<h3>Current Destinations</h3>
<table border="1">
    <tr>
        <th>City</th>
        <th>Country</th>
        <th>Description</th>
        <th>Action</th>
    </tr>
    <?php while($dest = $trip_destinations->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($dest['city']); ?></td>
            <td><?php echo htmlspecialchars($dest['country']); ?></td>
            <td><?php echo htmlspecialchars($dest['description']); ?></td>
            <td>
                <a href="?trip_id=<?php echo $trip_id; ?>&remove=<?php echo $dest['id']; ?>" onclick="return confirm('Remove?')">🗑️ Remove</a>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if($trip_destinations->num_rows == 0): ?>
        <tr><td colspan="4">No destinations added yet.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>
