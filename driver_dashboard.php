<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$accountID = $_SESSION['id'];

// 1. FETCH DRIVER DATA
$sql = "SELECT d.*, v.vehicleModel, v.vehiclePlateNum FROM driver d 
        LEFT JOIN vehicle v ON d.driverID = v.driverID WHERE d.accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $accountID);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
$driverID = $driver['driverID'];
$driverName = $driver['driverName'] ?? "Driver";

// 2. FETCH PENDING JOBS (Current)
$pendingSql = "SELECT b.*, c.custName FROM booking b
               JOIN customer c ON b.custID = c.custID
               WHERE b.driverID = ? AND b.bookingStatus = 'Pending'
               ORDER BY b.bookingTimestamp DESC";
$pStmt = $conn->prepare($pendingSql);
$pStmt->bind_param("i", $driverID);
$pStmt->execute();
$pendingJobs = $pStmt->get_result();

// 3. FETCH COMPLETED JOBS (History)
$completedSql = "SELECT b.*, c.custName FROM booking b
                 JOIN customer c ON b.custID = c.custID
                 WHERE b.driverID = ? AND b.bookingStatus = 'Completed'
                 ORDER BY b.bookingTimestamp DESC LIMIT 5";
$cStmt = $conn->prepare($completedSql);
$cStmt->bind_param("i", $driverID);
$cStmt->execute();
$completedJobs = $cStmt->get_result();

// 4. CALCULATE EARNINGS (Only counts 'Completed' jobs)
$earningsSql = "SELECT SUM(bookingFare) as total FROM booking 
                WHERE driverID = ? AND bookingStatus = 'Completed' AND DATE(bookingTimestamp) = CURDATE()";
$eStmt = $conn->prepare($earningsSql);
$eStmt->bind_param("i", $driverID);
$eStmt->execute();
$todaysEarnings = $eStmt->get_result()->fetch_assoc()['total'] ?? 0;

// 5. Calculate Total Completed Trips dynamically
$tripsSql = "SELECT COUNT(bookingID) as totalTrips FROM booking 
             WHERE driverID = ? AND bookingStatus = 'Completed'";
$tStmt = $conn->prepare($tripsSql);
$tStmt->bind_param("i", $driverID);
$tStmt->execute();
$tripsResult = $tStmt->get_result()->fetch_assoc();
$totalTrips = $tripsResult['totalTrips'] ?? 0;

// 6. Calculate Average Rating dynamically
$ratingSql = "SELECT AVG(rating) as avgRating FROM feedback f 
              JOIN booking b ON f.bookingID = b.bookingID 
              WHERE b.driverID = ?";
$rStmt = $conn->prepare($ratingSql);
$rStmt->bind_param("i", $driverID);
$rStmt->execute();
$ratingResult = $rStmt->get_result()->fetch_assoc();

// If NULL (no ratings yet), default to 5.0
$displayRating = $ratingResult['avgRating'] ? number_format($ratingResult['avgRating'], 1) : "5.0";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GrabWeb - Driver Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="driver_dashboard.php" style="color:white; text-decoration:none; display: flex; align-items: center; gap: 10px;">
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
                <div class="dropdown">
                    <button class="dropbtn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($driverName); ?>&background=fff&color=02B150" class="nav-avatar">
                        <?php echo $driverName; ?>
                        <i class="fa-solid fa-caret-down" style="margin-left: 5px; font-size: 0.8rem;"></i>
                    </button>
                    
                    <div id="myDropdown" class="dropdown-content">
                        <div class="dropdown-header">
                            <span style="font-size: 12px; color: #888;">Signed in as</span><br>
                            <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
                        </div>
                        <a href="driver_profile.php"><i class="fa-solid fa-user"></i> Edit Profile</a>
                        <div class="divider"></div>
                        <a href="#" onclick="confirmLogout()" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="profile-wrapper">
        <div class="profile-header-card">
            <div class="user-main">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($driverName); ?>&background=02B150&color=fff" class="profile-pic">
                <div class="user-info">
                    <h1>Welcome, <?php echo htmlspecialchars($driverName); ?></h1>
                    <p><i class="fa-solid fa-car"></i> Vehicle: <?php echo htmlspecialchars($driver['vehicleModel'] ?? 'Not Assigned'); ?> (<?php echo htmlspecialchars($driver['vehiclePlateNum'] ?? '---'); ?>)</p>
                    <p><i class="fa-solid fa-star" style="color: #FFD700;"></i> Rating: <?php echo $displayRating; ?>/5.0</p>
                    <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($driver['driverPhone'] ?: 'No phone set'); ?></p>
                    <p><i class="fa-solid fa-house"></i> <?php echo htmlspecialchars($driver['driverAddress'] ?: 'No address set'); ?></p>
                </div>
            </div>
            <div style="text-align: right;">
                <span class="success-alert" style="display: inline-block; padding: 5px 15px; border-radius: 20px;">Online</span>
            </div>
        </div>

            <div class="section-card" style="margin-bottom: 0; text-align: center;">
                <div class="section-title"><i class="fa-solid fa-wallet"></i> Today's Earnings</div>
                <h2 style="color: var(--primary-color); font-size: 2rem;">RM <?php echo number_format($todaysEarnings, 2); ?></h2>
            </div>
            <div class="section-card" style="margin-bottom: 0; text-align: center;">
                <div class="section-title"><i class="fa-solid fa-route"></i> Total Trips</div>
                <h2 style="color: var(--primary-color); font-size: 2rem;"><?php echo $totalTrips; ?></h2>
            </div>

        <div class="section-card">
            <div class="section-title" style="color: #e67e22;"><i class="fa-solid fa-spinner"></i> Current Pending Jobs</div>
            <?php if ($pendingJobs->num_rows > 0): ?>
                <?php while($row = $pendingJobs->fetch_assoc()): ?>
                    <div class="booking-item" style="border-left: 5px solid #e67e22;">
                        <div class="trip-info">
                            <b>Passenger: <?php echo htmlspecialchars($row['custName']); ?></b>
                            <span><?php echo htmlspecialchars($row['pickupLocation']); ?> &rarr; <?php echo htmlspecialchars($row['dropoffLocation']); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: bold; display: block; margin-bottom: 10px;">RM <?php echo number_format($row['bookingFare'], 2); ?></span>
                            <form action="driver_complete_booking.php" method="POST">
                                <input type="hidden" name="bookingID" value="<?php echo $row['bookingID']; ?>">
                                <button type="submit" class="book-btn" style="background: #02B150; padding: 5px 15px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-check"></i> Complete Trip
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center; padding: 15px;">No active bookings at the moment.</p>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <div class="section-title"><i class="fa-solid fa-history"></i> Recent Completed Jobs</div>
            <?php if ($completedJobs->num_rows > 0): ?>
                <?php while($row = $completedJobs->fetch_assoc()): ?>
                    <div class="booking-item">
                        <div class="trip-info">
                            <b>Passenger: <?php echo htmlspecialchars($row['custName']); ?></b>
                            <span><?php echo htmlspecialchars($row['pickupLocation']); ?> &rarr; <?php echo htmlspecialchars($row['dropoffLocation']); ?></span>
                            <small class="trip-date"><?php echo date('d M, h:i A', strtotime($row['bookingTimestamp'])); ?></small>
                        </div>
                        <div class="trip-price" style="text-align: right; color: #02B150;">
                            RM <?php echo number_format($row['bookingFare'], 2); ?><br>
                            <small style="color: #888;">Paid</small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center; padding: 15px;">Your completed history will appear here.</p>
            <?php endif; ?>
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