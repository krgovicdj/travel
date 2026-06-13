<?php
session_start();
if (isset($_SESSION['username'])) {
    header("location: dashboard.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Travel system project</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="welcome">
    <h1>Welcome!</h1>
    <div class="home-desc">
        <h2>Document your travels, share with everyone!</h2>
        <p>
            <strong>Plan your trips</strong> - Create and organize your future adventures
        </p>
        <p>
            <strong>Share photos</strong> - Add and showcase your travel memories
        </p>
        <p>
            <strong>Track destinations</strong> - Mark places you've visited
        </p>
        <p>
            <strong>Rate & review</strong> - Share your experiences and read others' reviews
        </p>
        <p>
            <strong>Save favorites</strong> - Bookmark trips you love for later
        </p>
        <p>
            <strong>Explore</strong> - Discover amazing trips from travelers worldwide
        </p>
    </div>

    <a href="login.php">
        <button>Login</button>
    </a>

    <a href="register.php">
        <button>Register</button>
    </a>
</div>

</body>
</html>
