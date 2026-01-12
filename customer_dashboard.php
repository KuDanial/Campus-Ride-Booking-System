<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['id'];

// Fetch Customer Name for greeting
$sql = "SELECT custName FROM customer WHERE accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$fullName = $row['custName'] ?? "User";
$firstName = explode(' ', trim($fullName))[0];

// Handle Driver Search
$drivers = [];
if (isset($_GET['pickup']) && isset($_GET['dropoff'])) {
    // In a real app, you might calculate price based on distance here
    // For now, we fetch all drivers and their vehicle info
    $driverSql = "SELECT d.*, v.vehicleModel, v.vehiclePlateNum, v.vehicleColor 
                  FROM driver d 
                  JOIN vehicle v ON d.driverID = v.driverID";
    $drivers = $conn->query($driverSql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrabWeb - Welcome, <?php echo $firstName; ?></title>
    
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
                        <div class="divider"></div>
                        <a href="#" onclick="confirmLogout()" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>

        </div> </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Where to today, <?php echo $firstName; ?>?</h1>
            
            <form action="search_ride.php" method="GET" class="search-widget">
                <div class="input-group">
                    <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="input-text">
                        <label>FROM</label>
                        <select name="pickup" class="form-control" required>
                        <option value="" disabled selected>Select Pickup</option>
                        <option value="Kolej TDM">Kolej TDM</option>
                        <option value="Kolej THO">Kolej THO</option>
                        <option value="Library">Library</option>
                    </select>
                    </div>
                </div>

                <div class="input-group">
                    <div class="icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <div class="input-text">
                        <label>TO</label>
                        <select name="dropoff" class="form-control" required>
                        <option value="" disabled selected>Select Destination</option>
                        <option value="Block A">Block A</option>
                        <option value="Block D">Block D</option>
                        <option value="Stesen Bas">Stesen Bas</option>
                    </select>
                    </div>
                </div>

                <button type="submit" class="search-btn">SEARCH RIDES</button>
            </form>
        </div>
    </header>

    <section class="results-preview">
        <h2>Recommended for you</h2>
        
        <div class="ride-card">
            <div class="driver-section">
                <div class="driver-img"><i class="fa-solid fa-user"></i></div>
                <div class="driver-info">
                    <h3>Amier Zhafran</h3>
                    <span class="rating">★ 4.9</span>
                    <p class="car-model">Perodua Myvi (Grey)</p>
                    <p class="plate-num">VAA 1234</p>
                </div>
            </div>
            <div class="trip-details">
                <div class="time">10:00 AM</div>
                <div class="route">UiTM Gate <i class="fa-solid fa-arrow-right-long"></i> Kolej Tun Razak (TR)</div>
                <div class="amenities">
                    <span><i class="fa-solid fa-snowflake"></i> AC</span>
                    <span><i class="fa-solid fa-users"></i> 4 Seats</span>
                </div>
            </div>
            <div class="price-action">
                <div class="price">RM 8.00</div>
                <button class="book-btn">Book Now</button>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h1 style="display: flex; align-items: center; gap: 10px; color: var(--primary-color);">
                    <i class="fa-solid fa-location-dot"></i> <b>GrabWeb</b>
                </h1> 
                
                <div style="margin-top: 5px;">
                    <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 8px;">
                        In Collaboration With
                    </p>
                    
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="images/grab-logo-black-and-white.png" 
                            alt="Grab" 
                            style="height: 23px; opacity: 0.8;">
                        
                        <span style="color:#555;">|</span>
                        
                        <img src="images/LOGO UiTM OUTLINE 3 (WHITE).png" 
                            alt="UiTM" 
                            style="height: 40px; opacity: 0.8;">
                    </div>
                </div>

                <p style="margin-top: 10px; color: #aaa;">
                    The simplified campus ride solution <br> for students and staff.
                </p>
                
                <p style="margin-top: 10px; color: #aaa;">
                    <b style="color:#02B150">Hotline:</b> <br>
                    +60 16-262 4834 / +60 11-6551 3006
                </p>

                <p style="margin-top: 10px; color: #aaa;">
                    <b style="color:#02B150">Email:</b> <br>
                    support@grab.uitm.edu.my
                </p>
            </div>

            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="#">Home</a>
                <a href="login.html">Login / Sign Up</a>
                <a href="#">Help Center</a>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2025 GrabWeb. All Rights Reserved.
        </div>
    </footer>

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
