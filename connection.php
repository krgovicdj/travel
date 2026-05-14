<?php
$conn=new mysqli("localhost","root","","travel");
if($conn->connect_error){
    echo $conn->connect_error;
}
