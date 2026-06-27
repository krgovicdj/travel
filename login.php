<?php
session_start();
require "connection.php";
if (isset($_SESSION['username'])) {
    header("location: dashboard.php");
    exit();
}
global $conn;
if(isset($_POST['submit'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $user_data = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_type'] = $user_data['type_id'];
        header("location: dashboard.php");
        exit();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
</head>
<body>
<script>
    $(function () {
        $("input[type=submit]").on('click', function (e) {
            let formUsername=$("input[name=username]").val();
            let formPassword=$("input[name=password]").val();
            let check=true;
            if (formUsername.length===0) {
                check=false;
            }
            if(formPassword.length<8){
                check=false;
            }
            if(!check){
                alert("Please enter valid data!");
            }
        });
    });
</script>
<h2>Login</h2>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="Enter Username">
    <br>
    <label for="password">Password</label>
    <input type="password" name="password" id="password" placeholder="Enter Password">
    <br>
    <input type="submit" value="Login" name="submit">
</form>
</body>
</html>
