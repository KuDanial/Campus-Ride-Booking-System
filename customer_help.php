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
    <title>GrabWeb - Help Centre</title>
    
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

    <div class="help-hero">
        <h1>How can we help you?</h1>
        <div class="help-search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search for issues (e.g., payment, lost item)...">
        </div>
    </div>

    <div class="help-container">
        
        <div class="help-categories">
            <div class="category-card">
                <i class="fa-solid fa-user-shield"></i>
                <h3>Account Issues</h3>
            </div>
            <div class="category-card">
                <i class="fa-solid fa-car"></i>
                <h3>Ride & Booking</h3>
            </div>
            <div class="category-card">
                <i class="fa-solid fa-credit-card"></i>
                <h3>Payments</h3>
            </div>
            <div class="category-card">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Safety & Emergency</h3>
            </div>
        </div>

        <section class="faq-section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="accordion-item">
                <button class="accordion-btn">
                    How do I book a ride?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>To book a ride, simply log in, enter your pickup and drop-off locations on the homepage, select your preferred date, and click "Search Rides".</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-btn">
                    Can I pay with cash?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Yes, we accept both cash payments directly to the driver and online transfers via GrabPay.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-btn">
                    I left an item in the car. What should I do?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Please go to "Manage Booking" and select the ride in question. Use the "Contact Driver" button to reach out immediately. If that fails, contact our support team.</p>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-btn">
                    How do I register as a driver?
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="accordion-content">
                    <p>Go to the Sign Up page, select "Driver" from the role dropdown menu, and fill in your details including your vehicle information.</p>
                </div>
            </div>

        </section>

        <div class="contact-support">
            <h3>Still need help?</h3>
            <p>Our support team is available 24/7 for students and staff.</p>
            <button class="btn-primary" style="width: auto; padding: 10px 30px;">Chat with Support</button>
        </div>

    </div>

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
                    +60 13-970 6363 / +60 18-264 6363
                </p>

                <p style="margin-top: 10px; color: #aaa;">
                    <b style="color:#02B150">Email:</b> <br>
                    support@uitm.edu.my
                </p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="#">Home</a>
                <a href="login.php">Login / Sign Up</a>
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