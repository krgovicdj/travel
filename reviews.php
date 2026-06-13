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

$stmt = $conn->prepare("SELECT t.*, u.username as author_name FROM trip t JOIN user u ON t.user_id = u.id WHERE t.id = ?");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$trip = $stmt->get_result()->fetch_assoc();

if(!$trip) {
    header("location: explore.php");
    exit();
}

if(isset($_POST['add_review']) && $user_type == 3) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO review (rating, comment, user_id, trip_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $rating, $comment, $user_id, $trip_id);
    $stmt->execute();
    header("location: reviews.php?trip_id=" . $trip_id);
    exit();
}

if(isset($_GET['delete_id'])) {
    $review_id = $_GET['delete_id'];

    if($user_type == 1) {
        $stmt = $conn->prepare("DELETE FROM review WHERE id = ?");
        $stmt->bind_param("i", $review_id);
    } elseif($user_type == 2) {
        $stmt = $conn->prepare("DELETE FROM review WHERE id = ?");
        $stmt->bind_param("i", $review_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM review WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $review_id, $user_id);
    }
    $stmt->execute();
    header("location: reviews.php?trip_id=" . $trip_id);
    exit();
}

if(isset($_POST['edit_review'])) {
    $review_id = $_POST['review_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    if($user_type == 1) {
        $stmt = $conn->prepare("UPDATE review SET rating=?, comment=? WHERE id=?");
        $stmt->bind_param("isi", $rating, $comment, $review_id);
    } elseif($user_type == 3) {
        $stmt = $conn->prepare("UPDATE review SET rating=?, comment=? WHERE id=? AND user_id=?");
        $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
    } else {
        header("location: reviews.php?trip_id=" . $trip_id);
        exit();
    }
    $stmt->execute();
    header("location: reviews.php?trip_id=" . $trip_id);
    exit();
}

$stmt = $conn->prepare("SELECT r.*, u.username FROM review r JOIN user u ON r.user_id = u.id WHERE r.trip_id = ? ORDER BY r.id DESC");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$reviews = $stmt->get_result();

$stmt = $conn->prepare("SELECT id FROM review WHERE user_id = ? AND trip_id = ?");
$stmt->bind_param("ii", $user_id, $trip_id);
$stmt->execute();
$already_reviewed = $stmt->get_result()->num_rows > 0;

$edit_review = null;
if(isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    if($user_type == 1) {
        $stmt = $conn->prepare("SELECT * FROM review WHERE id = ?");
        $stmt->bind_param("i", $edit_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM review WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $edit_id, $user_id);
    }
    $stmt->execute();
    $edit_review = $stmt->get_result()->fetch_assoc();
}
?>
<!doctype html>
<html>
<head>
    <title>Reviews - <?php echo htmlspecialchars($trip['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Reviews for: <?php echo htmlspecialchars($trip['name']); ?></h2>
<p>Author: <?php echo htmlspecialchars($trip['author_name']); ?></p>
<a href="explore.php">Back to Explore</a>
<a href="dashboard.php">Dashboard</a>
<hr>

<?php if($user_type == 3 && !$already_reviewed && $user_id != $trip['user_id']) { ?>
    <h3>Add Review</h3>
    <form method="post">
        <label>Rating (1-5)</label>
        <select name="rating">
            <option value="5">⭐⭐⭐⭐⭐ 5</option>
            <option value="4">⭐⭐⭐⭐ 4</option>
            <option value="3">⭐⭐⭐ 3</option>
            <option value="2">⭐⭐ 2</option>
            <option value="1">⭐ 1</option>
        </select>
        <br>
        <label>Comment</label>
        <textarea name="comment" rows="3" cols="40" required></textarea>
        <br>
        <input type="submit" name="add_review" value="Post Review">
    </form>
    <hr>
<?php } elseif($user_type == 3 && $already_reviewed) { ?>
    <p><em>You already reviewed this trip.</em></p>
    <hr>
<?php } elseif($user_type == 3 && $user_id == $trip['user_id']) { ?>
    <p><em>You cannot review your own trip.</em></p>
    <hr>
<?php } ?>

<?php if($edit_review) { ?>
    <h3>Edit Review</h3>
    <form method="post">
        <input type="hidden" name="review_id" value="<?php echo $edit_review['id']; ?>">
        <label>Rating (1-5)</label>
        <select name="rating">
            <option value="5" <?php if($edit_review['rating']==5) { echo 'selected'; } ?>>⭐⭐⭐⭐⭐ 5</option>
            <option value="4" <?php if($edit_review['rating']==4) { echo 'selected'; } ?>>⭐⭐⭐⭐ 4</option>
            <option value="3" <?php if($edit_review['rating']==3) { echo 'selected'; } ?>>⭐⭐⭐ 3</option>
            <option value="2" <?php if($edit_review['rating']==2) { echo 'selected'; } ?>>⭐⭐ 2</option>
            <option value="1" <?php if($edit_review['rating']==1) { echo 'selected'; } ?>>⭐ 1</option>
        </select>
        <br>
        <label>Comment</label>
        <textarea name="comment" rows="3" cols="40" required><?php echo htmlspecialchars($edit_review['comment']); ?></textarea>
        <br>
        <input type="submit" name="edit_review" value="Update Review">
        <a href="reviews.php?trip_id=<?php echo $trip_id; ?>">Cancel</a>
    </form>
    <hr>
<?php } ?>

<h3>All Reviews (<?php echo $reviews->num_rows; ?>)</h3>

<?php while($rev = $reviews->fetch_assoc()) { ?>
    <div style="border:1px solid #ccc; margin:10px; padding:10px; border-radius:5px;">
        <strong><?php echo htmlspecialchars($rev['username']); ?></strong>
        <span>Rating: <?php echo str_repeat("⭐", $rev['rating']); ?></span>
        <p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
        <small><?php echo $rev['created_at']; ?></small>
        <br>

        <?php if($user_type == 1) { ?>
            <a href="?trip_id=<?php echo $trip_id; ?>&edit_id=<?php echo $rev['id']; ?>">✏️ Edit</a>
            <a href="?trip_id=<?php echo $trip_id; ?>&delete_id=<?php echo $rev['id']; ?>" onclick="return confirm('Delete review?')" style="color:red;">🗑️ Delete</a>
        <?php } elseif($user_type == 2) { ?>
            <a href="?trip_id=<?php echo $trip_id; ?>&delete_id=<?php echo $rev['id']; ?>" onclick="return confirm('Delete review?')" style="color:red;">🗑️ Delete</a>
        <?php } elseif($user_type == 3 && $rev['user_id'] == $user_id) { ?>
            <a href="?trip_id=<?php echo $trip_id; ?>&edit_id=<?php echo $rev['id']; ?>">✏️ Edit</a>
            <a href="?trip_id=<?php echo $trip_id; ?>&delete_id=<?php echo $rev['id']; ?>" onclick="return confirm('Delete review?')" style="color:red;">🗑️ Delete</a>
        <?php } ?>
    </div>
<?php } ?>

<?php if($reviews->num_rows == 0) { ?>
    <p>No reviews yet. Add your review!</p>
<?php } ?>

</body>
</html>