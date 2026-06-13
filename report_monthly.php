<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$sql = "SELECT 
            YEAR(start_date) as godina,
            MONTH(start_date) as mjesec_broj,
            DATE_FORMAT(start_date, '%M') as mjesec,
            COUNT(*) as broj_putovanja
        FROM trip 
        WHERE start_date IS NOT NULL
        GROUP BY YEAR(start_date), MONTH(start_date)
        ORDER BY godina DESC, mjesec_broj DESC";

$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="izvjestaj_po_mjesecima_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fwrite($output, "Godina;Mjesec;Broj putovanja\n");

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fwrite($output, $row['godina'] . ";" . $row['mjesec'] . ";" . $row['broj_putovanja'] . "\n");
    }
} else {
    fwrite($output, "Nema podataka;;0\n");
}

fclose($output);
?>