<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accountID = $_SESSION['id'];
    $driverID = $_POST['driverID'];
    $pickup = $_POST['pickup'];
    $dropoff = $_POST['dropoff'];
    $payMethod = $_POST['payMethod'];
    $fare = $_POST['fare']; // This will now work because of the change above

    // 1. Get custID
    $stmt = $conn->prepare("SELECT custID FROM customer WHERE accountID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $custID = $stmt->get_result()->fetch_assoc()['custID'];

    // 2. Insert Booking (Ensure 'bookingFare' is in your SQL statement)
    // Note: Adjust the column name 'bookingFare' if it differs in your table
    $bookingSql = "INSERT INTO booking (bookingTimestamp, pickupDate, pickupTime, pickupLocation, dropoffLocation, bookingStatus, custID, driverID, bookingFare) 
                   VALUES (NOW(), CURDATE(), CURTIME(), ?, ?, 'Pending', ?, ?, ?)";
    
    $stmtB = $conn->prepare($bookingSql);
    $stmtB->bind_param("ssiid", $pickup, $dropoff, $custID, $driverID, $fare);
    
    if ($stmtB->execute()) {
        $newBookingID = $conn->insert_id;

        // 3. Insert Payment
        $paymentSql = "INSERT INTO payment (payMethod, payStatus, bookingID) VALUES (?, 'Pending', ?)";
        $stmtP = $conn->prepare($paymentSql);
        $stmtP->bind_param("si", $payMethod, $newBookingID);
        $stmtP->execute();

        echo "<script>alert('Booking Successful!'); window.location.href='customer_dashboard.php';</script>";
    }
}
?>