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
$user_type = $_SESSION['user_type'];

if(isset($_POST['id'])) {
    $trip_id = $_POST['id'];

    if($user_type == 1 || $user_type == 2) {
        $stmt = $conn->prepare("DELETE FROM trip WHERE id = ?");
        $stmt->bind_param("i", $trip_id);
    } elseif($user_type == 3) {
        $stmt = $conn->prepare("DELETE FROM trip WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trip_id, $user_id);
    } else {
        echo json_encode(['success' => false, 'error' => 'No permission']);
        exit();
    }

    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No ID']);
}
?>
