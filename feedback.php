<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id'])) { header("Location: login.php"); exit(); }

$bookingID = $_GET['bookingID'];

// Fetch trip details to show the user what they are rating
$sql = "SELECT b.*, d.driverName FROM booking b 
        JOIN driver d ON b.driverID = d.driverID 
        WHERE b.bookingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bookingID);
$stmt->execute();
$trip = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Your Trip</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="profile-wrapper" style="max-width: 500px; margin: 40px auto;">
        <div class="section-card">
            <div class="section-title"><i class="fa-solid fa-star"></i> Rate Your Trip</div>
            
            <p style="margin-bottom: 20px; color: #666;">
                How was your ride with <b><?php echo htmlspecialchars($trip['driverName']); ?></b>?<br>
                <small><?php echo $trip['pickupLocation']; ?> to <?php echo $trip['dropoffLocation']; ?></small>
            </p>

            <form action="feedback_submit.php" method="POST">
                <input type="hidden" name="bookingID" value="<?php echo $bookingID; ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Rating (1-5 Stars)</label>
                    <select name="rating" class="form-control" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Terrible</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Comments (Optional)</label>
                    <textarea name="comments" class="form-control" rows="4" placeholder="Share your experience..."></textarea>
                </div>

                <button type="submit" class="search-btn" style="width: 100%;">Submit Feedback</button>
                <a href="customer_profile.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>