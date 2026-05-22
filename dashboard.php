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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
<a href="explore.php">🌍 Explore Trips</a>
<a href="saved_trips.php">💾 Saved Trips</a>
<hr>

<h3>Trips</h3>

<table border="1" id="tripsTable">
    <thead>
    <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Start</th>
        <th>End</th>
        <th>Notes</th>
        <?php if ($user_type != 3) { echo "<th>Author</th>"; } ?>
        <th colspan="2">Actions</th>
    </tr>
    </thead>
    <tbody id="tripsBody">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr id="trip-row-<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td class="trip-status">
                <select class="status-select" data-id="<?php echo $row['id']; ?>">
                    <option value="planirano" <?php echo $row['status']=='planirano' ? 'selected' : ''; ?>>Planirano</option>
                    <option value="u toku" <?php echo $row['status']=='u toku' ? 'selected' : ''; ?>>U toku</option>
                    <option value="završeno" <?php echo $row['status']=='završeno' ? 'selected' : ''; ?>>Završeno</option>
                </select>
            </td>
            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
            <td><?php echo htmlspecialchars($row['notes']); ?></td>
            <?php if ($user_type != 3) { ?>
                <td><?php echo htmlspecialchars($row['author_name']); ?></td>
            <?php } ?>
            <td>
                <?php if ($user_type == 1 || ($user_type == 3 && $row['user_id'] == $user_id)) { ?>
                    <a href="edit_trip.php?id=<?php echo $row['id']; ?>">✏️</a>
                <?php } else { ?>
                    <span style="opacity:0.3;">✏️</span>
                <?php } ?>
            </td>
            <td>
                <?php if (($user_type == 3 && $row['user_id'] == $user_id) || $user_type == 2 || $user_type == 1) { ?>
                    <a href="javascript:void(0)" class="delete-btn" data-id="<?php echo $row['id']; ?>">🗑️</a>
                <?php } else { ?>
                    <span style="opacity:0.3;">🗑️</span>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
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

<script>
    $(function() {
        // AJAX UPDATE - promjena statusa
        $(document).on('change', '.status-select', function() {
            let tripId = $(this).data('id');
            let newStatus = $(this).val();
            let select = $(this);

            $.ajax({
                url: 'ajax_update_status.php',
                type: 'POST',
                data: { id: tripId, status: newStatus },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        select.css('background-color', '#90EE90');
                        setTimeout(function() {
                            select.css('background-color', '');
                        }, 500);
                    } else {
                        alert('Error: ' + response.error);
                        location.reload();
                    }
                },
                error: function() {
                    alert('AJAX error - Update failed');
                }
            });
        });

        // AJAX DELETE
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();

            let tripId = $(this).data('id');
            let row = $('#trip-row-' + tripId);

            if(confirm('Delete this trip?')) {
                $.ajax({
                    url: 'ajax_delete_trip.php',
                    type: 'POST',
                    data: { id: tripId },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('AJAX error - Delete failed');
                    }
                });
            }
        });
    });
</script>

</body>
</html>