<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$trip_id = $_GET['trip_id'] ?? 0;

// Dohvati putovanje (samo ako user ima pravo)
if($user_type == 3) {
    $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $trip_id, $user_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM trip WHERE id = ?");
    $stmt->bind_param("i", $trip_id);
}
$stmt->execute();
$trip = $stmt->get_result()->fetch_assoc();

if(!$trip) {
    header("location: dashboard.php");
    exit();
}

// CREATE - dodaj fotografiju (neka baza stavi upload_date)
if(isset($_POST['add_photo'])) {
    $name = $_POST['name'];
    $url = $_POST['url'];
    $caption = $_POST['caption'];

    $stmt = $conn->prepare("INSERT INTO trip_photo (name, url, caption, trip_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $url, $caption, $trip_id);
    $stmt->execute();
    header("location: photos.php?trip_id=" . $trip_id);
    exit();
}

// DELETE - brisanje fotografije
if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM trip_photo WHERE id = ? AND trip_id = ?");
    $stmt->bind_param("ii", $delete_id, $trip_id);
    $stmt->execute();
    header("location: photos.php?trip_id=" . $trip_id);
    exit();
}

// AJAX UPDATE - izmjena imena i captiona
if(isset($_POST['ajax_update']) && isset($_POST['photo_id']) && isset($_POST['field']) && isset($_POST['value'])) {
    header('Content-Type: application/json');

    $photo_id = $_POST['photo_id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    if($field != 'name' && $field != 'caption') {
        echo json_encode(['success' => false, 'error' => 'Invalid field']);
        exit();
    }

    // Provjera da li korisnik ima pravo
    if($user_type == 3) {
        $check = $conn->prepare("
            SELECT tp.id FROM trip_photo tp 
            JOIN trip t ON tp.trip_id = t.id 
            WHERE tp.id = ? AND t.user_id = ?
        ");
        $check->bind_param("ii", $photo_id, $user_id);
        $check->execute();
        if($check->get_result()->num_rows == 0) {
            echo json_encode(['success' => false, 'error' => 'No permission']);
            exit();
        }
    } elseif($user_type != 1) {
        echo json_encode(['success' => false, 'error' => 'No permission']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE trip_photo SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $photo_id);

    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// READ - dohvati sve fotografije
$stmt = $conn->prepare("SELECT * FROM trip_photo WHERE trip_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$photos = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
    <title>Photos - <?php echo htmlspecialchars($trip['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<div class="trip-details-container">
    <div class="trip-header">
        <h1>📷 Photos for: <?php echo htmlspecialchars($trip['name']); ?></h1>
        <a href="edit_trip.php?id=<?php echo $trip_id; ?>" class="btn btn-secondary">← Back to Trip</a>
        <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
    </div>

    <!-- Forma za dodavanje fotografije -->
    <div class="section">
        <h2>➕ Add New Photo</h2>
        <form method="post" style="max-width: 500px;">
            <label>Name</label>
            <input type="text" name="name" required>
            <br>
            <label>Image URL</label>
            <input type="url" name="url" placeholder="https://..." required>
            <br>
            <label>Caption</label>
            <input type="text" name="caption">
            <br>
            <input type="submit" name="add_photo" value="Add Photo">
        </form>
    </div>

    <!-- Galerija slika -->
    <div class="section">
        <h2>📸 All Photos (<?php echo $photos->num_rows; ?>)</h2>
        <?php if($photos->num_rows > 0): ?>
            <div class="photo-gallery">
                <?php while($photo = $photos->fetch_assoc()): ?>
                    <div class="photo-card" data-id="<?php echo $photo['id']; ?>">
                        <div class="photo-frame">
                            <img src="<?php echo htmlspecialchars($photo['url']); ?>" alt="<?php echo htmlspecialchars($photo['name']); ?>">
                        </div>
                        <p><strong class="photo-name" data-id="<?php echo $photo['id']; ?>" data-field="name"><?php echo htmlspecialchars($photo['name']); ?></strong></p>
                        <p class="photo-caption" data-id="<?php echo $photo['id']; ?>" data-field="caption"><?php echo htmlspecialchars($photo['caption']); ?></p>
                        <small><?php echo $photo['upload_date']; ?></small>
                        <br>
                        <a href="?trip_id=<?php echo $trip_id; ?>&delete_id=<?php echo $photo['id']; ?>" class="delete-photo" onclick="return confirm('Delete this photo?')" style="color:red;">🗑️ Delete</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-data">No photos yet. Add your first photo!</p>
        <?php endif; ?>
    </div>
</div>

<script>
    $(function() {
        // AJAX UPDATE - izmjena imena slike (double click)
        $(document).on('dblclick', '.photo-name', function() {
            let element = $(this);
            let oldValue = element.text();
            let photoId = element.data('id');
            let field = element.data('field');

            let input = $('<input>', {
                type: 'text',
                value: oldValue,
                css: { width: '100%' }
            });

            element.html(input);
            input.focus();

            input.on('blur', function() {
                let newValue = $(this).val().trim();

                if(newValue && newValue != oldValue) {
                    $.ajax({
                        url: 'photos.php',
                        type: 'POST',
                        data: {
                            ajax_update: 1,
                            photo_id: photoId,
                            field: field,
                            value: newValue
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                element.html(newValue);
                                element.css('background-color', '#90EE90');
                                setTimeout(function() {
                                    element.css('background-color', '');
                                }, 500);
                            } else {
                                alert('Error: ' + response.error);
                                element.html(oldValue);
                            }
                        },
                        error: function() {
                            element.html(oldValue);
                            alert('AJAX error');
                        }
                    });
                } else {
                    element.html(oldValue);
                }
            });
        });

        // AJAX UPDATE - izmjena captiona (double click)
        $(document).on('dblclick', '.photo-caption', function() {
            let element = $(this);
            let oldValue = element.text();
            let photoId = element.data('id');
            let field = element.data('field');

            let input = $('<input>', {
                type: 'text',
                value: oldValue == '(no caption)' ? '' : oldValue,
                css: { width: '100%' },
                placeholder: 'Enter caption...'
            });

            element.html(input);
            input.focus();

            input.on('blur', function() {
                let newValue = $(this).val().trim();

                if(newValue != oldValue) {
                    $.ajax({
                        url: 'photos.php',
                        type: 'POST',
                        data: {
                            ajax_update: 1,
                            photo_id: photoId,
                            field: field,
                            value: newValue
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                element.html(newValue || '(no caption)');
                                element.css('background-color', '#90EE90');
                                setTimeout(function() {
                                    element.css('background-color', '');
                                }, 500);
                            } else {
                                alert('Error: ' + response.error);
                                element.html(oldValue || '(no caption)');
                            }
                        },
                        error: function() {
                            element.html(oldValue || '(no caption)');
                            alert('AJAX error');
                        }
                    });
                } else {
                    element.html(oldValue || '(no caption)');
                }
            });
        });
    });
</script>

</body>
</html>