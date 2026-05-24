<?php
session_start();
require "connection.php";
global $conn;

if(!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Dohvati sve postojeće destinacije za brzo povezivanje
$destinations_map = [];
$dest_result = $conn->query("SELECT id, city, country FROM destination");
while($dest = $dest_result->fetch_assoc()) {
    $key = strtolower($dest['city'] . '|' . $dest['country']);
    $destinations_map[$key] = $dest['id'];
}

if(isset($_FILES['json_file']) && $_FILES['json_file']['error'] == 0) {
    $content = file_get_contents($_FILES['json_file']['tmp_name']);
    $trips = json_decode($content, true);

    if($trips && is_array($trips)) {
        $imported = 0;

        foreach($trips as $trip_data) {
            // Provjera da li putovanje već postoji za ovog korisnika
            $check = $conn->prepare("SELECT id FROM trip WHERE name = ? AND user_id = ?");
            $check->bind_param("si", $trip_data['name'], $user_id);
            $check->execute();

            if($check->get_result()->num_rows == 0) {
                // 1. Ubaci TRIP
                $name = $trip_data['name'] ?? 'Untitled Trip';
                $status = $trip_data['status'] ?? 'planirano';
                $start_date = $trip_data['start_date'] ?? date('Y-m-d');
                $end_date = $trip_data['end_date'] ?? date('Y-m-d');
                $notes = $trip_data['notes'] ?? '';

                $stmt = $conn->prepare("INSERT INTO trip (name, status, start_date, end_date, notes, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $name, $status, $start_date, $end_date, $notes, $user_id);
                $stmt->execute();
                $new_trip_id = $stmt->insert_id;

                // 2. Ubaci DESTINACIJE
                if(isset($trip_data['destinations']) && is_array($trip_data['destinations'])) {
                    foreach($trip_data['destinations'] as $dest) {
                        $city = $dest['city'] ?? '';
                        $country = $dest['country'] ?? '';
                        $description = $dest['description'] ?? '';

                        if($city && $country) {
                            $key = strtolower($city . '|' . $country);
                            if(isset($destinations_map[$key])) {
                                $dest_id = $destinations_map[$key];
                            } else {
                                // Kreiraj novu destinaciju
                                $dest_stmt = $conn->prepare("INSERT INTO destination (city, country, description) VALUES (?, ?, ?)");
                                $dest_stmt->bind_param("sss", $city, $country, $description);
                                $dest_stmt->execute();
                                $dest_id = $dest_stmt->insert_id;
                                $destinations_map[$key] = $dest_id;
                            }

                            // Poveži putovanje sa destinacijom
                            $link_stmt = $conn->prepare("INSERT INTO trip_destination (trip_id, destination_id) VALUES (?, ?)");
                            $link_stmt->bind_param("ii", $new_trip_id, $dest_id);
                            $link_stmt->execute();
                        }
                    }
                }

                // 3. Ubaci AKTIVNOSTI
                if(isset($trip_data['activities']) && is_array($trip_data['activities'])) {
                    foreach($trip_data['activities'] as $act) {
                        $act_name = $act['name'] ?? 'Activity';
                        $act_start = $act['start_date'] ?? null;
                        $act_end = $act['end_date'] ?? null;
                        $act_notes = $act['notes'] ?? '';

                        $act_stmt = $conn->prepare("INSERT INTO activity (name, start_date, end_date, notes, trip_id) VALUES (?, ?, ?, ?, ?)");
                        $act_stmt->bind_param("ssssi", $act_name, $act_start, $act_end, $act_notes, $new_trip_id);
                        $act_stmt->execute();
                    }
                }

                // 4. Ubaci FOTOGRAFIJE
                if(isset($trip_data['photos']) && is_array($trip_data['photos'])) {
                    foreach($trip_data['photos'] as $photo) {
                        $photo_name = $photo['name'] ?? 'Photo';
                        $photo_url = $photo['url'] ?? '';
                        $photo_caption = $photo['caption'] ?? '';

                        if($photo_url) {
                            $photo_stmt = $conn->prepare("INSERT INTO trip_photo (name, url, caption, trip_id) VALUES (?, ?, ?, ?)");
                            $photo_stmt->bind_param("sssi", $photo_name, $photo_url, $photo_caption, $new_trip_id);
                            $photo_stmt->execute();
                        }
                    }
                }

                $imported++;
            }
        }

        $message = "✅ Successfully imported $imported trips!";
    } else {
        $error = "❌ Invalid JSON file!";
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Import JSON</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="import-page">
<div class="auth-container">
    <div class="auth-card">
        <h2>📥 Import Trips from JSON</h2>
        <p class="subtitle">Import your trips (trip, destinations, activities, photos)</p>

        <a href="dashboard.php" class="btn btn-secondary" style="display: inline-block; margin-bottom: 20px;">← Back to Dashboard</a>

        <?php if($message): ?>
            <div class="success-message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>JSON File</label>
            <input type="file" name="json_file" accept=".json" required>
            <br><br>
            <input type="submit" value="Import" class="btn btn-primary" style="width: 100%;">
        </form>

        <hr>
        <h3>JSON Format Example:</h3>
        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 11px;">
[
    {
        "name": "Trip to Paris",
        "status": "planirano",
        "start_date": "2026-06-01",
        "end_date": "2026-06-10",
        "notes": "Visit Eiffel Tower",
        "destinations": [
            {
                "city": "Paris",
                "country": "France",
                "description": "City of Light"
            }
        ],
        "activities": [
            {
                "name": "Eiffel Tower Visit",
                "start_date": "2026-06-02",
                "end_date": "2026-06-02",
                "notes": "Buy tickets online"
            }
        ],
        "photos": [
            {
                "name": "Eiffel Tower",
                "url": "https://example.com/eiffel.jpg",
                "caption": "Beautiful view"
            }
        ]
    }
]
        </pre>

        <div class="auth-footer">
            <a href="export_json.php">📤 Export your trips first</a>
        </div>
    </div>
</div>
</body>
</html>