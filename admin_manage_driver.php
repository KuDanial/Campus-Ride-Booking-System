<?php
session_start();
include "db_conn.php";

// 1. SECURITY: Admin only
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. HANDLE DELETE ACTION (GET)
if (isset($_GET['delete_id'])) {
    $accountID = $_GET['delete_id'];
    $sql = "DELETE FROM account WHERE accountID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $accountID);
    
    if ($stmt->execute()) {
        header("Location: admin_manage_driver.php?msg=Deleted");
        exit();
    }
}

// 3. HANDLE UPDATE ACTION (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $driverID = $_POST['driverID'];
    $username = $_POST['accountUsername'];
    $newPass  = $_POST['newPassword'];

    // Get AccountID first
    $stmtAcc = $conn->prepare("SELECT accountID FROM driver WHERE driverID = ?");
    $stmtAcc->bind_param("i", $driverID);
    $stmtAcc->execute();
    $accountID = $stmtAcc->get_result()->fetch_assoc()['accountID'];

    if (!empty($newPass)) {
        $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
        $sql = "UPDATE account SET accountUsername=?, accountPassword=? WHERE accountID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $hashedPass, $accountID);
    } else {
        $sql = "UPDATE account SET accountUsername=? WHERE accountID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $accountID);
    }

    if ($stmt->execute()) {
        header("Location: admin_manage_driver.php?msg=Updated");
        exit();
    }
}

// 4. FETCH ALL DRIVERS (For the table)
$sql = "SELECT d.*, a.accountUsername FROM driver d 
        JOIN account a ON d.accountID = a.accountID 
        ORDER BY d.accountID ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Drivers</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
        <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-location-dot"></i> GrabWeb</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin_manage_driver.php" class="active"><i class="fa-solid fa-car"></i> Manage Drivers</a></li>
                <li><a href="admin_manage_customer.php"><i class="fa-solid fa-users"></i> Manage Customers</a></li>
                <li><a href="admin_manage_booking.php"><i class="fa-solid fa-ticket"></i> View Bookings</a></li>
                <li class="menu-divider">System</li>
                <li><a href="admin_manage_admin.php"><i class="fa-solid fa-user-shield"></i> Manage Admins<span class="badge">New</a></li>
                <li><a href="admin_report.php"><i class="fa-solid fa-file"></i> Reports</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" onclick="return confirmLogout()" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Manage Drivers</h1><br>
            </header>

            <section class="recent-activity">
                <div class="section-header">
                    <h2><i class="fa-solid fa-list"></i> Registered Drivers</h2>
                </div>
                <div class="table-container">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Driver ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Username</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#D-<?php echo $row['driverID']; ?></td>
                                        <td><?php echo htmlspecialchars($row['driverName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['driverEmail']); ?></td>
                                        <td><?php echo htmlspecialchars($row['driverPhone'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['accountUsername']); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 10px;">
                                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                                        style="color: #03a9f4; border: none; background: none; cursor: pointer;">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                
                                                <a href="admin_manage_driver.php?delete_id=<?php echo $row['accountID']; ?>" 
                                                onclick="return confirm('Delete this driver?')" style="color: #f44336;">
                                                <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No drivers registered yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="editModal" class="modal-overlay" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content" style="background: white; width: 90%; max-width: 500px; margin: 50px auto; padding: 25px; border-radius: 8px;">
            <div class="section-title"><i class="fa-solid fa-user-pen"></i> Update Driver Profile</div>
            <form action="admin_manage_driver.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="driverID" id="edit_driverID">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Username</label>
                    <input type="text" name="accountUsername" id="edit_username" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="newPassword" class="form-control" placeholder="••••••••">
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeModal()" class="search-btn" style="background: #888; flex: 1;">Cancel</button>
                    <button type="submit" class="search-btn" style="flex: 2;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(driverData) {
            document.getElementById('edit_driverID').value = driverData.driverID;
            document.getElementById('edit_username').value = driverData.accountUsername;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>