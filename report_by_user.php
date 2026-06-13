<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$sql = "SELECT 
            u.username,
            ut.type,
            COUNT(t.id) as broj_putovanja
        FROM user u
        JOIN user_type ut ON u.type_id = ut.id
        LEFT JOIN trip t ON u.id = t.user_id
        GROUP BY u.id
        ORDER BY broj_putovanja DESC";

$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="izvjestaj_po_korisnicima_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fwrite($output, "Korisnik;Tip;Broj putovanja\n");

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fwrite($output, $row['username'] . ";" . $row['type'] . ";" . $row['broj_putovanja'] . "\n");
    }
} else {
    fwrite($output, "Nema korisnika;;0\n");
}

fclose($output);
?>