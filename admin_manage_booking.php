<?php
session_start();
include "db_conn.php";

// Prevent browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. SECURITY: Admin only
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. FETCH ALL BOOKINGS WITH RELATED DATA
// We use LEFT JOINs to ensure we see the booking even if a driver isn't assigned yet
$sql = "SELECT 
            b.bookingID, 
            b.pickupDate, 
            b.pickupTime,
            b.bookingFare,
            c.custName, 
            d.driverName, 
            p.payMethod
        FROM booking b
        LEFT JOIN customer c ON b.custID = c.custID
        LEFT JOIN driver d ON b.driverID = d.driverID
        LEFT JOIN payment p ON b.bookingID = p.bookingID
        ORDER BY b.bookingID DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Bookings</title>
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
                <li><a href="admin_manage_booking.php" class="active"><i class="fa-solid fa-ticket"></i> View Bookings</a></li>
                <li class="menu-divider">System</li>
                <li><a href="admin_manage_admin.php"><i class="fa-solid fa-user-shield"></i> Manage Admins</a></li>
                <li><a href="admin_report.php"><i class="fa-solid fa-file"></i> Reports</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" onclick="return confirmLogout()" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Manage Bookings</h1><br>
            </header>

            <section class="recent-activity">
                <div class="section-header">
                    <h2><i class="fa-solid fa-list"></i> All System Bookings</h2>
                </div>
                <div class="table-container">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Payment</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#BK-<?php echo $row['bookingID']; ?></td>
                                        <td>
                                            <?php echo date("d M Y", strtotime($row['pickupDate'])); ?><br>
                                            <small style="color: #888;"><?php echo $row['pickupTime']; ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['custName'] ?? 'Unknown'); ?></td>
                                        <td><?php echo htmlspecialchars($row['driverName'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="badge" style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">
                                                <?php echo htmlspecialchars($row['payMethod'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td style="font-weight: bold; color: #2e7d32;">
                                            RM <?php echo number_format($row['bookingFare'] ?? 0, 2); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No bookings found in the system.</td></tr>
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
            }
        }
    </script>
</body>
</html>