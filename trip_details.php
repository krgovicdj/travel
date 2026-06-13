<?php
session_start();
require "connection.php";
global $conn;

$trip_id = $_GET['id'] ?? 0;

$sql = "SELECT t.*, u.username as author_name, u.id as author_id
        FROM trip t 
        JOIN user u ON t.user_id = u.id 
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$trip = $stmt->get_result()->fetch_assoc();

if(!$trip) {
    header("location: explore.php");
    exit();
}

$dest_sql = "SELECT d.city, d.country, d.description 
             FROM destination d 
             JOIN trip_destination td ON d.id = td.destination_id 
             WHERE td.trip_id = ?";
$dest_stmt = $conn->prepare($dest_sql);
$dest_stmt->bind_param("i", $trip_id);
$dest_stmt->execute();
$destinations = $dest_stmt->get_result();

$photos_sql = "SELECT * FROM trip_photo WHERE trip_id = ? ORDER BY upload_date DESC";
$photos_stmt = $conn->prepare($photos_sql);
$photos_stmt->bind_param("i", $trip_id);
$photos_stmt->execute();
$photos = $photos_stmt->get_result();

$act_sql = "SELECT * FROM activity WHERE trip_id = ? ORDER BY start_date";
$act_stmt = $conn->prepare($act_sql);
$act_stmt->bind_param("i", $trip_id);
$act_stmt->execute();
$activities = $act_stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($trip['name']); ?> - Trip Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="trip-details-container">
    <div class="trip-header">
        <h1>📌 <?php echo htmlspecialchars($trip['name']); ?></h1>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($trip['author_name']); ?></p>
        <p><strong>Status:</strong> <?php echo $trip['status']; ?></p>
        <p><strong>Period:</strong> <?php echo $trip['start_date']; ?> → <?php echo $trip['end_date']; ?></p>
        <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($trip['notes'])); ?></p>
        <a href="explore.php" class="btn btn-secondary">← Back to Explore</a>
        <?php if(isset($_SESSION['username'])) { ?>
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
        <?php } ?>
    </div>

    <?php if($destinations->num_rows > 0) { ?>
        <div class="section">
            <h2>📍 Destinations</h2>
            <ul class="destinations-list">
                <?php
                while($dest = $destinations->fetch_assoc()) {
                    echo '<li><strong>' . htmlspecialchars($dest['city']) . '</strong>, ' . htmlspecialchars($dest['country']) . ' - ' . htmlspecialchars($dest['description']) . '</li>';
                }
                ?>
            </ul>
        </div>
    <?php } ?>

    <div class="section">
        <h2>📷 Photos (<?php echo $photos->num_rows; ?>)</h2>
        <?php if($photos->num_rows > 0) { ?>
            <div class="photo-gallery">
                <?php
                while($photo = $photos->fetch_assoc()) {
                    echo '<div class="photo-card">';
                    echo '<div class="photo-frame">';
                    echo '<img src="' . htmlspecialchars($photo['url']) . '" alt="' . htmlspecialchars($photo['name']) . '">';
                    echo '</div>';
                    echo '<p><strong>' . htmlspecialchars($photo['name']) . '</strong></p>';
                    echo '<p>' . htmlspecialchars($photo['caption']) . '</p>';
                    echo '<small>' . $photo['upload_date'] . '</small>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } else { ?>
            <p class="no-data">No photos yet.</p>
        <?php } ?>
    </div>

    <div class="section">
        <h2>📅 Activities (<?php echo $activities->num_rows; ?>)</h2>
        <?php if($activities->num_rows > 0) { ?>
            <table class="activity-table" border="1">
                <thead>
                <tr><th>Name</th><th>Start Date</th><th>End Date</th><th>Notes</th></tr>
                </thead>
                <tbody>
                <?php
                while($act = $activities->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($act['name']) . '</td>';
                    echo '<td>' . $act['start_date'] . '</td>';
                    echo '<td>' . $act['end_date'] . '</td>';
                    echo '<td>' . htmlspecialchars($act['notes']) . '</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-data">No activities planned yet.</p>
        <?php } ?>
    </div>
</div>
</body>
</html>