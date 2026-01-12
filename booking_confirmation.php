<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get data from the search page
$driverID = $_POST['driverID'];
$pickup = $_POST['pickup'];
$dropoff = $_POST['dropoff'];
$fare = $_POST['fare'];

// Fetch driver and vehicle details for display
$sql = "SELECT d.driverName, v.vehicleModel, v.vehiclePlateNum 
        FROM driver d 
        LEFT JOIN vehicle v ON d.driverID = v.driverID 
        WHERE d.driverID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driverID);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Ride</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="profile-wrapper" style="max-width: 500px; margin: 50px auto;">
        <div class="section-card">
            <div class="section-title"><i class="fa-solid fa-clipboard-check"></i> Booking Details</div>
            
            <div style="margin-bottom: 20px;">
                <p><strong>From:</strong> <?php echo htmlspecialchars($pickup); ?></p>
                <p><strong>To:</strong> <?php echo htmlspecialchars($dropoff); ?></p>
                <p><strong>Driver:</strong> <?php echo htmlspecialchars($driver['driverName']); ?></p>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($driver['vehicleModel']); ?> (<?php echo htmlspecialchars($driver['vehiclePlateNum']); ?>)</p>
                <h2 style="color: #02B150; margin-top: 15px;">Total Fare: RM <?php echo number_format($fare, 2); ?></h2>
            </div>

            <form action="confirm_booking.php" method="POST">
                <input type="hidden" name="driverID" value="<?php echo $driverID; ?>">
                <input type="hidden" name="pickup" value="<?php echo htmlspecialchars($pickup); ?>">
                <input type="hidden" name="dropoff" value="<?php echo htmlspecialchars($dropoff); ?>">
                <input type="hidden" name="fare" value="<?php echo $fare; ?>">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label><strong>Select Payment Method:</strong></label>
                    <select name="payMethod" class="form-control" style="width: 100%; padding: 10px; margin-top: 5px;" required>
                        <option value="Cash">Cash</option>
                        <option value="GrabPay">GrabPay (E-Wallet)</option>
                        <option value="Bank Transfer">Online Banking</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="customer_dashboard.php" class="search-btn" style="background: #888; text-align: center; text-decoration: none; flex: 1;">Cancel</a>
                    <button type="submit" class="search-btn" style="flex: 2;">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>