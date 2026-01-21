<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id'])) exit();

$accountID = $_SESSION['id'];

// Get driver ID
$stmt = $conn->prepare("SELECT driverID FROM driver WHERE accountID = ?");
$stmt->bind_param("i", $accountID);
$stmt->execute();
$driverID = $stmt->get_result()->fetch_assoc()['driverID'];

// Count pending bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE driverID = ? AND bookingStatus = 'Pending'");
$stmt->bind_param("i", $driverID);
$stmt->execute();
echo $stmt->get_result()->fetch_assoc()['count'];
?>