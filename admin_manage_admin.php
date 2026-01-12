<?php
session_start();
include "db_conn.php";

// Security: Ensure only admins can access
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- LOGIC TO ADD NEW ADMIN ---
if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // In production, use password_hash()
    $name = $_POST['name'];
    $email = $_POST['email'];

    // 1. Insert into account table
    $sql1 = "INSERT INTO account (accountUsername, accountPassword, accountType) VALUES (?, ?, 'admin')";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ss", $username, $password);

    if ($stmt1->execute()) {
        $last_id = $conn->insert_id;
        // 2. Insert into admin table using the new accountID
        $sql2 = "INSERT INTO admin (adminName, adminEmail, accountID) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ssi", $name, $email, $last_id);
        $stmt2->execute();
        
        echo "<script>alert('New Admin Added Successfully!'); window.location.href='admin_manage.php';</script>";
    }
}

// --- FETCH ALL ADMINS ---
$sql_admins = "SELECT a.adminID, a.adminName, a.adminEmail, acc.accountUsername 
               FROM admin a 
               JOIN account acc ON a.accountID = acc.accountID";
$result_admins = $conn->query($sql_admins);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrabWeb - Manage Admin</title>
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
                <li><a href="admin_manage_driver.php"><i class="fa-solid fa-car"></i> Manage Drivers</a></li>
                <li><a href="admin_manage_customer.php"><i class="fa-solid fa-users"></i> Manage Customers</a></li>
                <li><a href="admin_manage_booking.php"><i class="fa-solid fa-ticket"></i> View Bookings</a></li>
                <li class="menu-divider">System</li>
                <li><a href="admin_manage_admin.php" class="active"><i class="fa-solid fa-user-shield"></i> Manage Admins<span class="badge">New</a></li>
                <li><a href="admin_report.php"><i class="fa-solid fa-file"></i> Reports</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" onclick="return confirmLogout()" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-title">
                    <h1>Manage Administrators</h1>
                    <p>Add and view system administrators.</p>
                </div>
            </header>

            <section class="stat-card" style="margin-bottom: 30px; display: block;">
                <h3>Add New Administrator</h3>
                <form action="admin_manage.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <input type="text" name="name" placeholder="Full Name" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <input type="email" name="email" placeholder="Email Address" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <input type="text" name="username" placeholder="Username" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <input type="password" name="password" placeholder="Password" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <button type="submit" name="add_admin" class="btn-primary" style="grid-column: span 2; width: 200px;">Add Admin</button>
                </form>
            </section>

            <section class="recent-activity">
                <div class="section-header">
                    <h2>Available Admins</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_admins->num_rows > 0): ?>
                                <?php while($row = $result_admins->fetch_assoc()): ?>
                                    <tr>
                                        <td>#ADM-<?php echo $row['adminID']; ?></td>
                                        <td><?php echo $row['adminName']; ?></td>
                                        <td><?php echo $row['adminEmail']; ?></td>
                                        <td><?php echo $row['accountUsername']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;">No administrators found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
                return true;
            }
            return false;
        }
    </script>
</body>
</html>