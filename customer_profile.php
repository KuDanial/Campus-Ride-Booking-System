<?php
session_start();
include "db_conn.php";

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
                <a href="help.html">Help</a>
                <a href="#" onclick="checkLogin()">Manage Booking</a>
                
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
                        <a href="#"><i class="fa-solid fa-wallet"></i> Payment Method</a>
                        <a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Ride History</a>
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
                <button class="edit-profile-btn" onclick="window.location.href='customer_edit.php'">
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
                
                <div class="booking-item">
                    <div class="trip-info">
                        <b>Driver: Amier Zhafran</b>
                        <span>UiTM Machang &rarr; Aeon Mall KB</span>
                        <span class="trip-date">12 May 2024, 2:30 PM</span>
                    </div>
                    <div class="trip-price">RM 25.00</div>
                </div>

                <div class="booking-item">
                    <div class="trip-info">
                        <b>Driver: Muhd Nur Ikhmal</b>
                        <span>Kolej Tun Razak &rarr; Bandar Machang</span>
                        <span class="trip-date">10 May 2024, 10:15 AM</span>
                    </div>
                    <div class="trip-price">RM 6.00</div>
                </div>
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