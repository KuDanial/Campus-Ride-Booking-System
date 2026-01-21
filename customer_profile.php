<?php
session_start();
include "db_conn.php";

// Prevent browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. SECURITY: If no session exists, kick them back to login.php
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// 2. FETCH USER DATA: Get the real name from the customer table
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrabWeb - Profile</title>

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

        <div class="profile-wrapper">
            <div class="profile-header-card">
                <div class="user-main">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($fullName); ?>&background=02B150&color=fff" class="profile-pic" alt="Profile">
                    <div class="user-info">
                        <h1><?php echo $fullName; ?></h1>
                        <p><i class="fa-solid fa-id-card"></i> Email: <?php echo htmlspecialchars($email); ?></p>
                        <p><i class="fa-solid fa-id-card"></i> ID: <?php echo $userID; ?></p>
                        <p><i class="fa-solid fa-phone"></i> <?php echo $phone; ?></p>
                    </div>
                </div>
                    <button class="edit-profile-btn" onclick="window.location.href='customer_edit.php'" style="width: 10%;">
                        <i class="fa-solid fa-user-pen"></i> Edit Profile
                    </button>
                </div>

            <div class="section-card">
                <div class="section-title"><i class="fa-solid fa-house-user"></i> Current Address</div>
                <div class="summary-text">
                    <?php echo nl2br($address); ?>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent Bookings</div>
                
                <?php
                // Fetch recent completed bookings for this customer
                // We LEFT JOIN feedback to check if the user has already rated the trip
                $recentSql = "SELECT b.*, d.driverName, f.feedbackID 
                            FROM booking b
                            JOIN driver d ON b.driverID = d.driverID
                            LEFT JOIN feedback f ON b.bookingID = f.bookingID
                            WHERE b.custID = (SELECT custID FROM customer WHERE accountID = ?) 
                            AND b.bookingStatus = 'Completed'
                            ORDER BY b.bookingTimestamp DESC LIMIT 5";
                
                $stmtR = $conn->prepare($recentSql);
                $stmtR->bind_param("i", $userID);
                $stmtR->execute();
                $recentResult = $stmtR->get_result();

                if ($recentResult->num_rows > 0):
                    while($row = $recentResult->fetch_assoc()):
                ?>
                    <div class="booking-item" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="trip-info">
                            <b>Driver: <?php echo htmlspecialchars($row['driverName']); ?></b>
                            <span><?php echo htmlspecialchars($row['pickupLocation']); ?> &rarr; <?php echo htmlspecialchars($row['dropoffLocation']); ?></span>
                            <span class="trip-date"><?php echo date('d M Y, h:i A', strtotime($row['bookingTimestamp'])); ?></span>
                        </div>
                        
                        <div style="text-align: right;">
                            <div class="trip-price" style="margin-bottom: 5px;">RM <?php echo number_format($row['bookingFare'] ?? 0, 2); ?></div>
                            
                            <?php if (!$row['feedbackID']): ?>
                                <a href="feedback.php?bookingID=<?php echo $row['bookingID']; ?>" 
                                class="book-btn" 
                                style="font-size: 11px; padding: 5px 10px; background: #FFD700; color: #000; text-decoration: none; border-radius: 5px;">
                                <i class="fa-solid fa-star"></i> Give Feedback
                                </a>
                            <?php else: ?>
                                <span style="font-size: 11px; color: #02B150;"><i class="fa-solid fa-check-circle"></i> Rated</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                    echo "<p style='color: #888; text-align: center; padding: 15px;'>No completed trips yet.</p>";
                endif; 
                ?>
            </div>
        </div>

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