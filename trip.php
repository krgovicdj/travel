<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$errors = [];
$name = $start_date = $end_date = $notes = $status = "";

if(isset($_POST['submit'])){
    $user_id = $_SESSION['user_id'];

    if(empty($_POST['name'])) {
        $errors['name'] = "Naziv putovanja je obavezan";
    } else {
        $name = trim($_POST['name']);

        $check_stmt = $conn->prepare("SELECT id FROM trip WHERE name = ? AND user_id = ?");
        $check_stmt->bind_param("si", $name, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows > 0) {
            $errors['name'] = "Već imate putovanje sa nazivom '$name'";
        }
        $check_stmt->close();
    }

    $allowed_statuses = ['planirano', 'u toku', 'završeno'];
    if(empty($_POST['status'])) {
        $status = 'planirano';
    } elseif(!in_array($_POST['status'], $allowed_statuses)) {
        $errors['status'] = "Nedozvoljen status";
    } else {
        $status = $_POST['status'];
    }

    if(empty($_POST['start_date'])) {
        $errors['start_date'] = "Datum početka je obavezan";
    } else {
        $start_date = $_POST['start_date'];
        $start_timestamp = strtotime($start_date);
        $today = strtotime(date('Y-m-d'));

        if($start_timestamp === false) {
            $errors['start_date'] = "Neispravan format datuma";
        } elseif($start_timestamp < $today) {
            $errors['start_date'] = "Datum početka ne može biti u prošlosti";
        }
    }

    if(!empty($_POST['end_date'])) {
        $end_date = $_POST['end_date'];
        $end_timestamp = strtotime($end_date);

        if($end_timestamp === false) {
            $errors['end_date'] = "Neispravan format datuma";
        } elseif(!empty($start_date) && $end_timestamp < strtotime($start_date)) {
            $errors['end_date'] = "Datum završetka ne može biti prije datuma početka";
        }
    }

    if(!empty($_POST['notes'])) {
        $notes = trim($_POST['notes']);
        if(strlen($notes) > 500) {
            $errors['notes'] = "Napomena ne smije imati više od 500 karaktera";
        }
    }

    if(empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO trip (name, start_date, end_date, notes, status, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $start_date, $end_date, $notes, $status, $user_id);

        if($stmt->execute()){
            $_SESSION['success_message'] = "Putovanje '$name' je uspešno dodato!";
            unset($_SESSION['form_errors']);
            unset($_SESSION['old_input']);
            header("location: dashboard.php");
            exit();
        } else {
            $errors['db'] = "Greška pri unosu: " . $conn->error;
        }
        $stmt->close();
    }

    if(!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_input'] = [
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'notes' => $notes,
            'status' => $status
        ];
        header("location: dashboard.php");
        exit();
    }
}
?>