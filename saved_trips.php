<?php
session_start();
require "connection.php";
global $conn;

if (!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT t.*, u.username as author_name 
        FROM trip t 
        JOIN trip_save ts ON t.id = ts.trip_id 
        JOIN user u ON t.user_id = u.id
        WHERE ts.user_id = ?
        ORDER BY ts.saved_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
    <title>Saved Trips</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<h2>My Saved Trips</h2>
<a href="dashboard.php">Back to Dashboard</a>
<a href="logout.php">
    <button>Logout</button>
</a>
<hr>

<table border="1">
    <thead>
    <tr>
        <th>Name</th>
        <th>Author</th>
        <th>Start</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php
    while ($row = $result->fetch_assoc()) {
        echo '<tr id="saved-row-' . $row['id'] . '">';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['author_name']) . '</td>';
        echo '<td>' . $row['start_date'] . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td><a href="#" class="unsave-btn" data-id="' . $row['id'] . '">Otkaži čuvanje</a></td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>

<script>
    $(function () {
        $('.unsave-btn').on('click', function () {
            let tripId = $(this).data('id');
            let row = $('#saved-row-' + tripId);

            if (confirm('Remove from saved trips?')) {
                $.ajax({
                    url: 'save_trip.php',
                    type: 'POST',
                    data: {trip_id: tripId, action: 'unsave'},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            row.fadeOut(300, function () {
                                $(this).remove();
                            });
                        } else {
                            alert('Error: ' + response.error);
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>