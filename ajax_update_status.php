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

if(isset($_POST['id']) && isset($_POST['status'])) {
    $trip_id = $_POST['id'];
    $status = $_POST['status'];
    $allowed = ['planirano', 'u toku', 'završeno'];

    if(!in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit();
    }

    if($user_type == 1) {
        $stmt = $conn->prepare("UPDATE trip SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $trip_id);
    } elseif($user_type == 3) {
        $stmt = $conn->prepare("UPDATE trip SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $trip_id, $user_id);
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
    echo json_encode(['success' => false, 'error' => 'Missing data']);
}
?>
