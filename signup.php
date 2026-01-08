<?php
include "db_conn.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $email    = isset($_POST['email']) ? $_POST['email'] : '';
    $user     = isset($_POST['username']) ? $_POST['username'] : '';
    $pass     = isset($_POST['password']) ? $_POST['password'] : '';
    $role     = isset($_POST['role']) ? $_POST['role'] : '';

    if (empty($fullName) || empty($user) || empty($role)) {
        die("Error: Please fill in all required fields.");
    }

    //insert into account table
    $sql1 = "INSERT INTO account (accountUsername, accountPassword, accountType) 
            VALUES ('$user', '$pass', '$role')";
    
    if (mysqli_query($conn, $sql1)) {
        $last_id = mysqli_insert_id($conn);

        //insert into customer or driver table based on role
        if ($role === 'customer') {
            $sql2 = "INSERT INTO customer (custName, custEmail, accountID) 
                    VALUES ('$fullName', '$email', '$last_id')";
        } else if ($role === 'driver') {
            $sql2 = "INSERT INTO driver (driverName, driverEmail, accountID) 
                    VALUES ('$fullName', '$email', '$last_id')";
        } else {
            die("Error: Invalid role selected.");
        }

        //execute the second insert
        if (mysqli_query($conn, $sql2)) {
            header("Location: login.php?success=registered");
            exit();
        } else {
            die("Error inserting into profile table: " . mysqli_error($conn));
        }
    } else {
        die("Error inserting into account table: " . mysqli_error($conn));
    }
}
?>