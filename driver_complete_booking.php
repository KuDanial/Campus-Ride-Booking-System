<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bookingID'])) {
    $bookingID = $_POST['bookingID'];

    // 1. Update Booking Status
    $updateBooking = "UPDATE booking SET bookingStatus = 'Completed' WHERE bookingID = ?";
    $stmt1 = $conn->prepare($updateBooking);
    $stmt1->bind_param("i", $bookingID);
    
    if ($stmt1->execute()) {
        // 2. Update Payment Status (Linked via bookingID)
        $updatePayment = "UPDATE payment SET payStatus = 'Completed' WHERE bookingID = ?";
        $stmt2 = $conn->prepare($updatePayment);
        $stmt2->bind_param("i", $bookingID);
        $stmt2->execute();

        header("Location: driver_dashboard.php?success=JobCompleted");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>