<?php
session_start();
require "connection.php";
global $conn;

$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;

// Dohvati sva putovanja (sa autorom)
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

<?php if(isset($_SESSION['username'])): ?>
    <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <a href="dashboard.php">My Dashboard</a>
    <a href="saved_trips.php">Saved Trips</a>
    <a href="logout.php"><button>Logout</button></a>
<?php else: ?>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
<?php endif; ?>

<hr>

<!-- Search i Filter -->
<div>
    <input type="text" id="searchInput" placeholder="Search by name or author..." onkeyup="filterTrips()">
    <select id="statusFilter" onchange="filterTrips()">
        <option value="">All status</option>
        <option value="planirano">Planirano</option>
        <option value="u toku">U toku</option>
        <option value="završeno">Završeno</option>
    </select>
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
    <?php while($row = $result->fetch_assoc()): ?>
        <tr id="trip-row-<?php echo $row['id']; ?>"
            data-name="<?php echo strtolower(htmlspecialchars($row['name'])); ?>"
            data-author="<?php echo strtolower(htmlspecialchars($row['author_name'])); ?>"
            data-status="<?php echo $row['status']; ?>">
            <td class="trip-name"><?php echo htmlspecialchars($row['name']); ?></td>
            <td class="trip-status"><?php echo htmlspecialchars($row['status']); ?></td>
            <td class="trip-start"><?php echo $row['start_date']; ?></td>
            <td class="trip-end"><?php echo $row['end_date']; ?></td>
            <td class="trip-author"><?php echo htmlspecialchars($row['author_name']); ?></td>
                <td><?php if(isset($_SESSION['username'])): ?>
                    <!-- Sačuvaj dugme -->

                    <a href="javascript:void(0)" class="save-btn" data-id="<?php echo $row['id']; ?>">💾 Sačuvaj</a>


                    <!-- Ako je admin ili autor, može editovati -->
                    <?php if(($user_type == 1) || ($user_id == $row['author_id'])): ?>
            <a href="edit_trip.php?id=<?php echo $row['id']; ?>">✏️</a>
                    <?php endif; ?>


                    <!-- Ako je admin, moderator ili autor, može brisati -->
                    <?php if(($user_type == 1 || $user_type == 2) || ($user_id == $row['author_id'])): ?>
            <a href="javascript:void(0)" class="delete-btn" data-id="<?php echo $row['id']; ?>">🗑️</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span>Login to save trips</span>
                <?php endif; ?>
                    <a href="trip_details.php?id=<?php echo $row['id']; ?>" class="btn-details">📋 Details</a>
            </td>
            <td>
                <a href="reviews.php?trip_id=<?php echo $row['id']; ?>">💬 Reviews</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>

    <script>
        function filterTrips() {
            let search = document.getElementById('searchInput').value.toLowerCase();
            let status = document.getElementById('statusFilter').value;
            let rows = document.querySelectorAll('#tripsBody tr');

            rows.forEach(row => {
                let name = row.getAttribute('data-name');
                let author = row.getAttribute('data-author');
                let tripStatus = row.getAttribute('data-status');

                let matchSearch = name.includes(search) || author.includes(search);
                let matchStatus = status === '' || tripStatus === status;

                row.style.display = (matchSearch && matchStatus) ? '' : 'none';
            });
        }

        <?php if(isset($_SESSION['username'])): ?>
        // AJAX Save
        $(document).on('click', '.save-btn', function(e) {
            e.preventDefault();
            let tripId = $(this).data('id');
            let btn = $(this);

            $.ajax({
                url: 'save_trip.php',
                type: 'POST',
                data: { trip_id: tripId, action: 'save' },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        btn.text('✅ Sačuvano');
                        setTimeout(() => btn.text('💾 Sačuvaj'), 2000);
                    } else {
                        alert('Error: ' + response.error);
                    }
                }
            });
        });

        // AJAX Delete
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
                            row.fadeOut(300, () => row.remove());
                        } else {
                            alert('Error: ' + response.error);
                        }
                    }
                });
            }
        });

        // AJAX UPDATE - izmjena datuma (double click)
        $(document).on('dblclick', '.start-date, .end-date', function() {
            let td = $(this);
            let oldValue = td.text();
            let tripId = td.data('id');
            let field = td.data('field');

            let input = $('<input>', {
                type: 'date',
                value: oldValue,
                css: { width: '100%' }
            });
            td.html(input);
            input.focus();

            input.on('blur', function() {
                let newValue = $(this).val();

                if(newValue && newValue != oldValue) {
                    $.ajax({
                        url: 'ajax_update_date.php',
                        type: 'POST',
                        data: { id: tripId, field: field, value: newValue },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                td.html(newValue);
                                td.css('background-color', '#90EE90');
                                setTimeout(function() {
                                    td.css('background-color', '');
                                }, 500);
                            } else {
                                alert('Error: ' + response.error);
                                td.html(oldValue);
                            }
                        },
                        error: function() {
                            td.html(oldValue);
                            alert('AJAX error');
                        }
                    });
                } else {
                    td.html(oldValue);
                }
            });
        });
        <?php endif; ?>
    </script>

    <br>
    <a href="export_json.php">📥 Export JSON</a>
    <a href="report_monthly.php">📊 Izvještaj po mjesecima (CSV)</a>
    <a href="report_by_user.php">📊 Izvještaj po korisnicima (CSV)</a>
</body>
</html>