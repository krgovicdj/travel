<?php
session_start();
require "connection.php";

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Dohvati samo putovanja trenutnog korisnika
$sql = "SELECT * FROM trip WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while($row = $result->fetch_assoc()) {
    // Dohvati destinacije
    $dest_sql = "SELECT d.city, d.country, d.description FROM destination d 
                 JOIN trip_destination td ON d.id = td.destination_id 
                 WHERE td.trip_id = " . $row['id'];
    $destinations = $conn->query($dest_sql)->fetch_all(MYSQLI_ASSOC);

    // Dohvati aktivnosti
    $act_sql = "SELECT name, start_date, end_date, notes FROM activity WHERE trip_id = " . $row['id'];
    $activities = $conn->query($act_sql)->fetch_all(MYSQLI_ASSOC);

    // Dohvati fotografije
    $photo_sql = "SELECT name, url, caption FROM trip_photo WHERE trip_id = " . $row['id'];
    $photos = $conn->query($photo_sql)->fetch_all(MYSQLI_ASSOC);

    $trips[] = [
        'name' => $row['name'],
        'status' => $row['status'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date'],
        'notes' => $row['notes'],
        'destinations' => $destinations,
        'activities' => $activities,
        'photos' => $photos
    ];
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="trips_export_' . date('Y-m-d') . '.json"');
echo json_encode($trips, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>