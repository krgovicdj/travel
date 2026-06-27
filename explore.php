<?php
session_start();
require "connection.php";
global $conn;

$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;

$sql = "SELECT t.*, u.username as author_name, u.id as author_id
        FROM trip t 
        JOIN user u ON t.user_id = u.id 
        ORDER BY t.start_date DESC";
$result = $conn->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore Trips</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<h2>🌍 Explore All Trips</h2>

<?php if (isset($_SESSION['username'])) { ?>
    <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <a href="dashboard.php">My Dashboard</a>
    <a href="saved_trips.php">Saved Trips</a>
    <a href="logout.php">
        <button>Logout</button>
    </a>
<?php }?>
<hr>

<div>
    <input type="text" id="searchInput" placeholder="Search by name or author...">
    <select id="statusFilter">
        <option value="">All status</option>
        <option value="planirano">Planirano</option>
        <option value="u toku">U toku</option>
        <option value="završeno">Završeno</option>
    </select>
    <button id="filterBtn">🔍 Filter</button>
    <button id="resetBtn">🔄 Reset</button>
</div>
<br>

<table border="1" id="tripsTable">
    <thead>
    <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Start</th>
        <th>End</th>
        <th>Author</th>
        <th>Actions</th>
        <th>Reviews</th>
    </tr>
    </thead>
    <tbody id="tripsBody">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr id="trip-row-<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo $row['start_date']; ?></td>
            <td><?php echo $row['end_date']; ?></td>
            <td><?php echo htmlspecialchars($row['author_name']); ?></td>
            <td>
                <?php if (isset($_SESSION['username'])) { ?>
                    <a href="#" class="save-btn" data-id="<?php echo $row['id']; ?>">💾 Sačuvaj</a>

                    <?php if ($user_type == 1) { ?>
                        <a href="edit_trip.php?id=<?php echo $row['id']; ?>">✏️</a>
                        <a href="#" class="delete-btn" data-id="<?php echo $row['id']; ?>">🗑️</a>
                    <?php } elseif ($user_type == 2) { ?>
                        <a href="#" class="delete-btn" data-id="<?php echo $row['id']; ?>">🗑️</a>
                    <?php } elseif ($user_id == $row['author_id']) { ?>
                        <a href="edit_trip.php?id=<?php echo $row['id']; ?>">✏️</a>
                        <a href="#" class="delete-btn" data-id="<?php echo $row['id']; ?>">🗑️</a>
                    <?php } ?>
                <?php } ?>
                <a href="trip_details.php?id=<?php echo $row['id']; ?>" class="btn-details">📋 Details</a>
            </td>
            <td>
                <a href="reviews.php?trip_id=<?php echo $row['id']; ?>">💬 Reviews</a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready(function () {
        let userType = <?php echo $user_type ?? 'null'; ?>;
        let userId = <?php echo $user_id ?? 'null'; ?>;

        $('#filterBtn').click(function () {
            let search = $('#searchInput').val();
            let status = $('#statusFilter').val();

            $.ajax({
                url: 'ajax_filter_trips.php',
                type: 'GET',
                data: {
                    search: search,
                    status: status
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#tripsBody').empty();

                        if (response.data.length === 0) {
                            $('#tripsBody').html('<tr><td colspan="7" style="text-align:center; padding:20px;">No trips found</td></tr>');
                            return;
                        }

                        response.data.forEach(trip => {
                            let actions = '';

                            <?php if (isset($_SESSION['username'])) { ?>
                            actions += `<a href="#" class="save-btn" data-id="${trip.id}">💾 Sačuvaj</a>`;

                            if (userType == 1) {
                                actions += ` <a href="edit_trip.php?id=${trip.id}">✏️</a>`;
                                actions += ` <a href="#" class="delete-btn" data-id="${trip.id}">🗑️</a>`;
                            } else if (userType == 2) {
                                actions += ` <a href="#" class="delete-btn" data-id="${trip.id}">🗑️</a>`;
                            } else if (userId == trip.author_id) {
                                actions += ` <a href="edit_trip.php?id=${trip.id}">✏️</a>`;
                                actions += ` <a href="#" class="delete-btn" data-id="${trip.id}">🗑️</a>`;
                            }
                            <?php } ?>

                            actions += ` <a class="btn-details" href="trip_details.php?id=${trip.id}">📋 Details</a>`;

                            $('#tripsBody').append(`
                            <tr id="trip-row-${trip.id}">
                                <td>${trip.name}</td>
                                <td>${trip.status}</td>
                                <td>${trip.start_date}</td>
                                <td>${trip.end_date}</td>
                                <td>${trip.author_name}</td>
                                <td>${actions}</td>
                                <td><a href="reviews.php?trip_id=${trip.id}">💬 Reviews</a></td>
                            </tr>
                        `);
                        });
                    }
                }
            });
        });

        $('#resetBtn').click(function () {
            location.reload();
        });

        <?php if(isset($_SESSION['username'])) { ?>
        $(document).on('click', '.save-btn', function (e) {
            e.preventDefault();
            let tripId = $(this).data('id');
            let btn = $(this);

            $.ajax({
                url: 'save_trip.php',
                type: 'POST',
                data: {trip_id: tripId, action: 'save'},
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        btn.text('✅ Sačuvano');
                        setTimeout(() => btn.html('💾 Sačuvaj'), 2000);
                    } else {
                        alert('Error: ' + response.error);
                    }
                }
            });
        });

        $(document).on('click', '.delete-btn', function (e) {
            e.preventDefault();
            let tripId = $(this).data('id');
            let row = $('#trip-row-' + tripId);

            if (confirm('Delete this trip?')) {
                $.ajax({
                    url: 'ajax_delete_trip.php',
                    type: 'POST',
                    data: {id: tripId},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            row.fadeOut(300, () => row.remove());
                        } else {
                            alert('Error: ' + response.error);
                        }
                    }
                });
            }
        });

        $(document).on('dblclick', '.trip-start, .trip-end', function () {
            let td = $(this);
            let oldValue = td.text();
            let tripId = td.closest('tr').attr('id').replace('trip-row-', '');
            let field = td.hasClass('trip-start') ? 'start_date' : 'end_date';

            let input = $('<input>', {
                type: 'date',
                value: oldValue,
                css: {width: '100%'}
            });
            td.html(input);
            input.focus();

            input.on('blur', function () {
                let newValue = $(this).val();

                if (newValue && newValue != oldValue) {
                    $.ajax({
                        url: 'ajax_update_date.php',
                        type: 'POST',
                        data: {id: tripId, field: field, value: newValue},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                td.html(newValue);
                                td.css('background-color', '#90EE90');
                                setTimeout(function () {
                                    td.css('background-color', '');
                                }, 500);
                            } else {
                                alert('Error: ' + response.error);
                                td.html(oldValue);
                            }
                        },
                        error: function () {
                            td.html(oldValue);
                            alert('AJAX error');
                        }
                    });
                } else {
                    td.html(oldValue);
                }
            });
        });
        <?php } ?>
    });
</script>

<br>
<a href="export_json.php">Export JSON</a>
|
<a href="report_monthly.php">Izvještaj po mjesecima (CSV)</a>
|
<a href="report_by_user.php">Izvještaj po korisnicima (CSV)</a>
</body>
</html>