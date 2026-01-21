<?php
session_start();
include "db_conn.php";

// Prevent browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['id'];

// 1. Get Customer ID first
$stmt = $conn->prepare("SELECT custID FROM customer WHERE accountID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$custID = $stmt->get_result()->fetch_assoc()['custID'];

$userID = $_SESSION['id'];
$fullName = "User"; // Fallback
$email = "Not Set";
$phone = "Not Set";
$address = "Not Set";

$sql = "SELECT custName, custEmail, custPhone, custAddress FROM customer WHERE accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $fullName = $row['custName'];
    $email    = $row['custEmail'];
    $phone    = $row['custPhone'] ?? "";
    $address  = $row['custAddress'] ?? "";
    // Extract first name for the "Where to today, Danial?" part
    $firstName = explode(' ', trim($fullName))[0];
}
// 2. Handle Cancellation Request
if (isset($_POST['cancel_booking'])) {
    $bookingID = $_POST['bookingID'];
    // Update booking status to Cancelled
    $updateSql = "UPDATE booking SET bookingStatus = 'Cancelled' WHERE bookingID = ? AND custID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ii", $bookingID, $custID);
    
    if ($updateStmt->execute()) {
        $msg = "Booking cancelled successfully.";
    }
}

// 3. Fetch Active Bookings (Pending or Confirmed)
$sql = "SELECT b.*, d.driverName, d.driverPhone, v.vehicleModel, v.vehiclePlateNum 
        FROM booking b
        JOIN driver d ON b.driverID = d.driverID
        JOIN vehicle v ON d.driverID = v.driverID
        WHERE b.custID = ? AND b.bookingStatus IN ('Pending', 'Confirmed')
        ORDER BY b.bookingTimestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $custID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - GrabWeb</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
        <div class="nav-container">
            
            <div class="logo">
                <a href="customer_dashboard.php" style="color:white; text-decoration:none; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-location-dot"></i> 
                    
                    <span style="font-weight: 800; letter-spacing: 1px;">GrabWeb</span>
                    
                    <span style="opacity: 0.6; font-weight: 300;">|</span>
                    
                    <img src="images/grab-logo-black-and-white.png" 
                         alt="Grab" 
                         style="height: 22px;">
                    
                    <img src="images/LOGO UiTM OUTLINE 3 (WHITE).png" 
                         alt="UiTM" 
                         style="height: 38px;">
                </a>
            </div>
            
            <div class="menu">
                <a href="customer_help.php">Help</a>
                <a href="customer_booking.php" onclick="checkLogin()">Manage Booking</a>
                
                <div class="dropdown">
                    <button class="dropbtn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($fullName); ?>&background=fff&color=02B150" class="nav-avatar">
                        <?php echo $firstName; ?>
                        <i class="fa-solid fa-caret-down" style="margin-left: 5px; font-size: 0.8rem;"></i>
                    </button>
                    
                    <div id="myDropdown" class="dropdown-content">
                        <div class="dropdown-header">
                            <span style="font-size: 12px; color: #888;">Signed in as</span><br>
                            <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
                        </div>
                        <a href="customer_profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="#" onclick="confirmLogout()" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>

        </div> </nav>

<section class="results-preview" style="padding-top: 40px; min-height: 80vh;">
    <h2>Your Active Bookings</h2>
    
    <?php if (isset($msg)) echo "<p style='color: green; text-align:center;'>$msg</p>"; ?>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="ride-card">
                <div class="driver-section">
                    <div class="driver-img">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['driverName']); ?>&background=02B150&color=fff" style="width:100%; border-radius:50%;">
                    </div>
                    <div class="driver-info">
                        <h3><?php echo htmlspecialchars($row['driverName']); ?></h3>
                        <p>Status: <strong style="color: orange;"><?php echo $row['bookingStatus']; ?></strong></p>
                        <p class="car-model"><?php echo htmlspecialchars($row['vehicleModel']); ?> (<?php echo htmlspecialchars($row['vehiclePlateNum']); ?>)</p>
                    </div>
                </div>

                <div class="trip-details">
                    <div class="route">
                        <?php echo htmlspecialchars($row['pickupLocation']); ?> 
                        <i class="fa-solid fa-arrow-right-long"></i> 
                        <?php echo htmlspecialchars($row['dropoffLocation']); ?>
                    </div>
                    <div class="time"><i class="fa-regular fa-clock"></i> Booked on: <?php echo $row['bookingTimestamp']; ?></div>
                </div>

                <div class="price-action">
                    <div class="price">RM <?php echo number_format($row['bookingFare'], 2); ?></div>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this ride?');">
                        <input type="hidden" name="bookingID" value="<?php echo $row['bookingID']; ?>">
                        <button type="submit" name="cancel_booking" class="book-btn" style="background-color: #e74c3c;">Cancel Ride</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; margin-top: 50px; color: #666;">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>You have no active bookings.</p>
            <a href="customer_dashboard.php" class="book-btn" style="display:inline-block; margin-top:15px; text-decoration:none;">Book a Ride Now</a>
        </div>
    <?php endif; ?>
</section>
<script>
        /* 1. Toggle the Dropdown Menu */
        function toggleDropdown() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        /* 2. Close the dropdown if the user clicks outside of it */
        window.onclick = function(event) {
            if (!event.target.matches('.dropbtn') && !event.target.matches('.nav-avatar') && !event.target.matches('.fa-caret-down')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        /* 3. Logout Confirmation */
        function toggleDropdown() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                // Point this to a logout.php file (we should create this)
                window.location.href = "logout.php"; 
            }
        }
    </script>
</body>
</html>