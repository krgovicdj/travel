<?php
session_start();
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . "<br>";
echo "Session user_type: " . ($_SESSION['user_type'] ?? 'not set') . "<br>";
echo "Session username: " . ($_SESSION['username'] ?? 'not set');
?>
