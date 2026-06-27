<?php
header('Content-Type: application/json');
session_start();
require "connection.php";
global $conn;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "SELECT t.*, u.username as author_name, u.id as author_id 
        FROM trip t 
        JOIN user u ON t.user_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (LOWER(t.name) LIKE ? OR LOWER(u.username) LIKE ?)";
    $searchParam = "%" . strtolower($search) . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($status)) {
    $sql .= " AND t.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY t.start_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $trips
]);

$conn->close();
?>
