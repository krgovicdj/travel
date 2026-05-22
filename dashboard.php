<?php
session_start();
require "connection.php";
global $conn;
if (!isset($_SESSION['username'])) {
    header("location:index.php");
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM trip WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Welcome, <?php echo $_SESSION['username'];?>!</h2>
<a href="logout.php">
    <button>Logout</button>
</a>

<hr>
<h3>My Trips</h3>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Start</th>
        <th>End</th>
        <th>Notes</th>
        <th colspan="4">Actions</th>
    </tr>
    <?php
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" .htmlspecialchars($row['name']) . "</td>";
        echo "<td>" .htmlspecialchars($row['status']) . "</td>";
        echo "<td>" .htmlspecialchars($row['start_date']) . "</td>";
        echo "<td>" .htmlspecialchars($row['end_date']) . "</td>";
        echo "<td>" .htmlspecialchars($row['notes']) . "</td>";
        echo "<td><a href='edit.php?id=" .$row['id'] . "'>✏️</a></td>";
        echo "<td><a href='delete.php?id=" .$row['id'] . "'>🗑️</a></td>";
        echo "</tr>";
    }
    ?>
</table>

<hr>

<!-- Prikaz uspješne poruke -->
<?php if(isset($_SESSION['success_message'])): ?>
    <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<form action="trip.php" method="post">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" placeholder="Trip Name" value="<?php echo htmlspecialchars($_SESSION['old_input']['name'] ?? ''); ?>">
    <br>
    <span><?php echo $_SESSION['form_errors']['name'] ?? ''; ?></span>
    <br>

    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="planirano" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'planirano') ? 'selected' : ''; ?>>Planirano</option>
        <option value="u toku" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'u toku') ? 'selected' : ''; ?>>U toku</option>
        <option value="završeno" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'završeno') ? 'selected' : ''; ?>>Završeno</option>
    </select>
    <br>
    <span><?php echo $_SESSION['form_errors']['status'] ?? ''; ?></span>
    <br>

    <label for="start_date">Start date</label>
    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($_SESSION['old_input']['start_date'] ?? ''); ?>">
    <br>
    <span><?php echo $_SESSION['form_errors']['start_date'] ?? ''; ?></span>
    <br>

    <label for="end_date">End date</label>
    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($_SESSION['old_input']['end_date'] ?? ''); ?>">
    <br>
    <span><?php echo $_SESSION['form_errors']['end_date'] ?? ''; ?></span>
    <br>

    <label for="notes">Notes</label>
    <textarea name="notes" id="notes" cols="30" rows="4" placeholder="Add a note..."><?php echo htmlspecialchars($_SESSION['old_input']['notes'] ?? ''); ?></textarea>
    <br>
    <span><?php echo $_SESSION['form_errors']['notes'] ?? ''; ?></span>
    <br>

    <span><?php echo $_SESSION['form_errors']['db'] ?? ''; ?></span>
    <br>

    <input type="submit" value="Add trip" name="submit">
</form>

<?php
unset($_SESSION['form_errors']);
unset($_SESSION['old_input']);
?>

</body>
</html>