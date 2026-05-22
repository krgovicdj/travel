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

if(isset($_GET['id'])) {
    $trip_id = $_GET['id'];

    if($user_type == 3) {
        $stmt = $conn->prepare("DELETE FROM trip WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $trip_id, $user_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM trip WHERE id = ?");
        $stmt->bind_param("i", $trip_id);
    }

    $stmt->execute();
}

header("location: dashboard.php");
exit();
?>
