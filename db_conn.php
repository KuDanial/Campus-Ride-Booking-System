<?php
$sname = "localhost";
$username = "root";
$password = "";
$db_name = "grabdb";

$conn = mysqli_connect($sname, $username, $password, $db_name);

if (!$conn) {
    die("Connection failed!");
}
?>