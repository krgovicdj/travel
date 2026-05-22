<?php
session_start();
require "connection.php";
global $conn;

if (!isset($_SESSION['username'])) {
    header("location:index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($user_type == 3) {
    $sql = "SELECT * FROM trip WHERE user_id = ? ORDER BY start_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql = "SELECT t.*, u.username as author_name 
            FROM trip t 
            JOIN user u ON t.user_id = u.id 
            ORDER BY t.start_date DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

<p>Role:
    <?php
    if ($user_type == 1) {
        echo "Administrator";
    } elseif ($user_type == 2) {
        echo "Moderator";
    } else {
        echo "User";
    }
    ?>
</p>

<a href="logout.php">
    <button>Logout</button>
</a>

<hr>

<h3>Trips</h3>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Start</th>
        <th>End</th>
        <th>Notes</th>
        <?php if ($user_type != 3) {
            echo "<th>Author</th>";
        } ?>
        <th colspan="2">Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
            <td><?php echo htmlspecialchars($row['notes']); ?></td>
            <?php if ($user_type != 3) { ?>
                <td><?php echo htmlspecialchars($row['author_name']); ?></td>
            <?php } ?>
            <td>
                <?php
                if ($user_type == 1 || $user_type == 3) {
                    echo "<a href='edit_trip.php?id=" . $row['id'] . "'>✏️</a>";
                } else {
                    echo "<span style='opacity:0.2;'>✏️</span>";
                }
                ?>
            </td>
            <td>
                <?php
                if (($user_type == 3 && $row['user_id'] == $user_id) || ($user_type == 2 || $user_type == 1)) {
                    echo "<a href='delete_trip.php?id=" . $row['id'] . "' onclick=\"return confirm('Delete this trip?')\">🗑️</a>";
                } else {
                    echo "<span style='opacity:0.5;'>🗑️</span>";
                }
                ?>
            </td>
        </tr>
    <?php } ?>
</table>

<hr>

<?php
if ($user_type != 2) {
    if (isset($_SESSION['success_message'])) {
        echo "<p>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
        unset($_SESSION['success_message']);
    }
    ?>

    <form action="trip.php" method="post">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" placeholder="Trip Name"
               value="<?php echo htmlspecialchars($_SESSION['old_input']['name'] ?? ''); ?>">
        <br>
        <span><?php echo $_SESSION['form_errors']['name'] ?? ''; ?></span>
        <br>

        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="planirano" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'planirano') ? 'selected' : ''; ?>>
                Planirano
            </option>
            <option value="u toku" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'u toku') ? 'selected' : ''; ?>>
                U toku
            </option>
            <option value="završeno" <?php echo (($_SESSION['old_input']['status'] ?? '') == 'završeno') ? 'selected' : ''; ?>>
                Završeno
            </option>
        </select>
        <br>
        <span><?php echo $_SESSION['form_errors']['status'] ?? ''; ?></span>
        <br>

        <label for="start_date">Start date</label>
        <input type="date" name="start_date" id="start_date"
               value="<?php echo htmlspecialchars($_SESSION['old_input']['start_date'] ?? ''); ?>">
        <br>
        <span><?php echo $_SESSION['form_errors']['start_date'] ?? ''; ?></span>
        <br>

        <label for="end_date">End date</label>
        <input type="date" name="end_date" id="end_date"
               value="<?php echo htmlspecialchars($_SESSION['old_input']['end_date'] ?? ''); ?>">
        <br>
        <span><?php echo $_SESSION['form_errors']['end_date'] ?? ''; ?></span>
        <br>

        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" cols="30" rows="4"
                  placeholder="Add a note..."><?php echo htmlspecialchars($_SESSION['old_input']['notes'] ?? ''); ?></textarea>
        <br>
        <span><?php echo $_SESSION['form_errors']['notes'] ?? ''; ?></span>
        <br>

        <span><?php echo $_SESSION['form_errors']['db'] ?? ''; ?></span>
        <br>

        <input type="submit" value="Add trip" name="submit">
    </form>
    <?php
} else {
    echo "<p><em>Moderators cannot add new trips. You can only delete inappropriate content.</em></p>";
}

unset($_SESSION['form_errors']);
unset($_SESSION['old_input']);
?>

</body>
</html>