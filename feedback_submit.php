<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookingID = $_POST['bookingID'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $sql = "INSERT INTO feedback (rating, comments, feedbackDate, bookingID) VALUES (?, ?, CURDATE(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $rating, $comments, $bookingID);

    if ($stmt->execute()) {
        echo "<script>
                alert('Thank you for your feedback!');
                window.location.href = 'customer_profile.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>