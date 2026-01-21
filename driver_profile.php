<?php
session_start();
include "db_conn.php";

// Prevent browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. SECURITY: Ensure user is logged in as a driver
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$accountID = $_SESSION['id'];
$success_msg = "";

$sql = "SELECT d.*, v.vehicleModel, v.vehiclePlateNum 
        FROM driver d 
        LEFT JOIN vehicle v ON d.driverID = v.driverID 
        WHERE d.accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $accountID);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();
$driverName = $driver['driverName'] ?? "Driver";

// 2. HANDLE PROFILE UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['driverName'];
    $phone = $_POST['driverPhone'];
    $email = $_POST['driverEmail'];
    $address = $_POST['driverAddress'];
    $icNum = $_POST['driverICNum'];
    $licenseNum = $_POST['driverLicenseNums'];
    $vModel = $_POST['vehicleModel'];
    $vPlate = $_POST['vehiclePlateNum'];
    $vColor = $_POST['vehicleColor'];

    // Update Driver Table
    $updateDriver = "UPDATE driver SET driverName=?, driverPhone=?, driverEmail=?, driverAddress=?, driverICNum=?, driverLicenseNum=? WHERE accountID=?";
    $stmt1 = $conn->prepare($updateDriver);
    $stmt1->bind_param("ssssssi", $name, $phone, $email, $address, $icNum, $licenseNum, $accountID);
    $stmt1->execute();
    
    // Fetch driverID to update vehicle table
    $getDID = "SELECT driverID FROM driver WHERE accountID = ?";
    $stmtID = $conn->prepare($getDID);
    $stmtID->bind_param("i", $accountID);
    $stmtID->execute();
    $dID = $stmtID->get_result()->fetch_assoc()['driverID'];

    // Update or Insert Vehicle Table (using driverID as foreign key)
    $checkVehicle = "SELECT * FROM vehicle WHERE driverID = ?";
    $stmtCheck = $conn->prepare($checkVehicle);
    $stmtCheck->bind_param("i", $dID);
    $stmtCheck->execute();
    
    if ($stmtCheck->get_result()->num_rows > 0) {
        $updateVehicle = "UPDATE vehicle SET vehicleModel=?, vehiclePlateNum=?, vehicleColor=? WHERE driverID=?";
        $stmt2 = $conn->prepare($updateVehicle);
        $stmt2->bind_param("sssi", $vModel, $vPlate, $vColor, $dID);
    } else {
        $updateVehicle = "INSERT INTO vehicle (vehicleModel, vehiclePlateNum, vehicleColor, driverID, vehicleType) VALUES (?, ?, ?, ?, 'Car')";
        $stmt2 = $conn->prepare($updateVehicle);
        $stmt2->bind_param("sssi", $vModel, $vPlate, $vColor, $dID);
    }
    $stmt2->execute();
    $success_msg = "Profile updated successfully!";
}

// 3. FETCH CURRENT DATA
$sql = "SELECT d.*, v.vehicleModel, v.vehiclePlateNum, v.vehicleColor 
        FROM driver d 
        LEFT JOIN vehicle v ON d.driverID = v.driverID 
        WHERE d.accountID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $accountID);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Driver Profile</title>
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

    <div class="profile-wrapper" style="max-width: 600px; margin: 40px auto;">
        <?php if($success_msg): ?>
            <div class="success-alert" style="margin-bottom: 20px; padding: 15px; background: #d4edda; color: #155724; border-radius: 8px;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <div class="section-title"><i class="fa-solid fa-user-gear"></i> Edit Profile Information</div>
            
            <form action="driver_profile.php" method="POST">
                <h3 style="margin-bottom: 15px; color: var(--primary-color);">Personal Details</h3>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Full Name</label>
                    <input type="text" name="driverName" class="form-control" value="<?php echo htmlspecialchars($data['driverName']); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Phone Number</label>
                    <input type="text" name="driverPhone" class="form-control" value="<?php echo htmlspecialchars($data['driverPhone']); ?>">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Email Address</label>
                    <input type="email" name="driverEmail" class="form-control" value="<?php echo htmlspecialchars($data['driverEmail']); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Home Address</label>
                    <textarea name="driverAddress" class="form-control" rows="3"><?php echo htmlspecialchars($data['driverAddress']); ?></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>IC Number</label>
                        <input type="text" name="driverICNum" class="form-control" value="<?php echo htmlspecialchars($data['driverICNum']); ?>" placeholder="e.g. 010101140505" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>License Number</label>
                        <input type="text" name="driverLicenseNumS" class="form-control" value="<?php echo htmlspecialchars($data['driverLicenseNum']); ?>" placeholder="e.g. 12345678" required>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
                
                <h3 style="margin-bottom: 15px; color: var(--primary-color);">Vehicle Details</h3>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Vehicle Model (e.g., Perodua Myvi)</label>
                    <input type="text" name="vehicleModel" class="form-control" value="<?php echo htmlspecialchars($data['vehicleModel'] ?? ''); ?>">
                </div>
                <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Plate Number</label>
                        <input type="text" name="vehiclePlateNum" class="form-control" value="<?php echo htmlspecialchars($data['vehiclePlateNum'] ?? ''); ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Color</label>
                        <input type="text" name="vehicleColor" class="form-control" value="<?php echo htmlspecialchars($data['vehicleColor'] ?? ''); ?>">
                    </div>
                </div>

                <button type="submit" class="search-btn" style="width: 100%;">Save All Changes</button>
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
        </script>
</body>
</html>