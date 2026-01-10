<?php
session_start();
include "db_conn.php";

// 1. SECURITY: Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['id'];
$fullName = "User";
$success_msg = "";

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

// 2. UPDATE LOGIC: If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = $_POST['fullName'];
    $newEmail = $_POST['email'];
    $newPhone = $_POST['phone'];
    $newAddress = $_POST['address'];

    $updateSql = "UPDATE customer SET custName = ?, custEmail = ?, custPhone = ?, custAddress = ? WHERE accountID = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssi", $newName, $newEmail, $newPhone, $newAddress, $userID);

    if ($stmt->execute()) {
        $success_msg = "Changes Saved Successfully!";
        // Refresh session if name changed
        $_SESSION['user_name'] = $newName; 
    }
}

// 3. FETCH DATA: Get current info to pre-fill the form
$sql = "SELECT * FROM customer WHERE accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fallbacks if data is empty
$fullName = $user['custName'] ?? "";
$email = $user['custEmail'] ?? "";
$phone = $user['custPhone'] ?? "";
$address = $user['custAddress'] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrabWeb - Edit Profile</title>
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

    <div class="edit-wrapper">
        <div class="edit-card">
            <?php if ($success_msg): ?>
                <div class="success-alert"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <div style="text-align: center; margin-bottom: 30px;">
                <div class="profile-upload-area">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($fullName); ?>&background=02B150&color=fff" class="profile-preview">
                    <div class="camera-icon"><i class="fa-solid fa-camera"></i></div>
                </div>
                <h2 style="margin:0; color: var(--text-dark);">Edit Personal Data</h2>
            </div>

            <form method="POST" action="customer_edit.php">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullName" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>

                <div class="form-group">
                    <label>Current Address</label>
                    <textarea name="address" class="form-control" style="height: 100px; resize: none;" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn-primary" style="background: #eee; color: #333;" onclick="window.location.href='customer_profile.php'">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Changes</button>
                </div>
            </form>
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

        function goBack() {
            window.location.href = 'customer_profile.php';
        }

        function confirmSave() {
            // In a real app, you would save data to a database here
            alert("Changes Saved Successfully!");
            window.location.href = 'customer_profile.php';
        }
    </script>
</body>
</html>