<?php
$conn=new mysqli("localhost","root","","travel");
if($conn->connect_error){
    die($conn->connect_error);
}
