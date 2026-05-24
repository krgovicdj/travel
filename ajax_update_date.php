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

if(isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
    $trip_id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    if($field != 'start_date' && $field != 'end_date') {
        echo json_encode(['success' => false, 'error' => 'Invalid field']);
        exit();
    }

    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        echo json_encode(['success' => false, 'error' => 'Invalid date']);
        exit();
    }

    if($user_type == 3) {
        $stmt = $conn->prepare("UPDATE trip SET $field = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $value, $trip_id, $user_id);
    } elseif($user_type == 1) {
        $stmt = $conn->prepare("UPDATE trip SET $field = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $trip_id);
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