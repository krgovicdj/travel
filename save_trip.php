<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

require "connection.php";
global $conn;

$user_id = $_SESSION['user_id'];

if(isset($_POST['trip_id'])) {
    $trip_id = $_POST['trip_id'];
    $action = $_POST['action'] ?? 'save';

    if($action == 'save') {
        $stmt = $conn->prepare("INSERT INTO trip_save (user_id, trip_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $trip_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM trip_save WHERE user_id = ? AND trip_id = ?");
        $stmt->bind_param("ii", $user_id, $trip_id);
    }

    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No trip ID']);
}
?>
