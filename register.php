<?php
session_start();
require "connection.php";
global $conn;
if (isset($_SESSION['username'])) {
    header("location: dashboard.php");
    exit();
}
$username=$password=$email="";
$usernameErr=$passwordErr=$emailErr="";
if(isset($_POST['submit'])){
    $check=true;
    if(empty($_POST["username"])){
        $usernameErr = "Username is required";
        $check=false;
    }else{
        $username=$_POST["username"];
    }
    if(strlen($_POST["password"])<8){
        $passwordErr = "Password must be at least 8 characters";
        $check=false;
    }else{
        $password=$_POST["password"];
    }
    if(!empty($_POST['email'])and!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
        $emailErr = "Invalid email format";
        $check=false;
    }else{
        $email=$_POST["email"];
    }
    if($check){
        $check_stmt = $conn->prepare("select * from user where username=?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if($result->num_rows == 0){
            $insert_stmt = $conn->prepare("insert into user(username,password,email) values(?,?,?)");
            $insert_stmt->bind_param("sss", $username, $password, $email);
            if($insert_stmt->execute()){
                header("location: index.php");
                exit();
            }
        }

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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
</head>
<body>
<script>
    $(function () {
        $("input[type=submit]").on('click', function (e) {
            let formUsername=$("input[name=username]").val();
            let formEmail=$("input[name=email]").val();
            let formPassword=$("input[name=password]").val();
            let check=true;
            if (formUsername.length===0) {
                check=false;
            }
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+$/;
            if(regex.test(formEmail)!==true){
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
<h2>Register</h2>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="Enter Username"
    value="<?php echo $username;?>">
    <span><?php echo $usernameErr;?></span>
    <br>
    <label for="email">E-mail</label>
    <input type="text" name="email" id="email" placeholder="Enter e-mail"
    value="<?php echo $email;?>">
    <span><?php echo $emailErr;?></span>
    <br>
    <label for="password">Password</label>
    <input type="password" name="password" id="password" placeholder="Enter Password"
    value="<?php echo $password;?>">
    <span><?php echo $passwordErr;?></span>
    <br>
    <input type="submit" value="Register" name="submit">
</form>

</body>
</html>
