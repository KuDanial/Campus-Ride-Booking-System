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

// 2. FETCH OVERALL STATISTICS
$stats_sql = "SELECT 
                COUNT(bookingID) as total_bookings,
                SUM(CASE WHEN bookingStatus = 'Completed' THEN 1 ELSE 0 END) as completed_rides,
                SUM(CASE WHEN bookingStatus = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_rides,
                SUM(bookingFare) as total_revenue
              FROM booking";
$stats_result = $conn->query($stats_sql)->fetch_assoc();

// 3. FETCH TOP PERFORMING DRIVERS (By Revenue)
$driver_perf_sql = "SELECT 
                        d.driverName, 
                        COUNT(b.bookingID) as trips, 
                        SUM(b.bookingFare) as earnings
                    FROM driver d
                    JOIN booking b ON d.driverID = b.driverID
                    WHERE b.bookingStatus = 'Completed'
                    GROUP BY d.driverID
                    ORDER BY earnings DESC LIMIT 5";
$driver_perf_result = $conn->query($driver_perf_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - System Reports</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .report-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        .report-card h3 { color: #888; font-size: 0.9rem; margin-bottom: 10px; }
        .report-card .value { font-size: 1.8rem; font-weight: bold; color: #333; }
        .revenue-value { color: #2e7d32 !important; }
    </style>
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
                <li><a href="admin_manage_admin.php"><i class="fa-solid fa-user-shield"></i> Manage Admins</a></li>
                <li><a href="admin_reports.php" class="active"><i class="fa-solid fa-file-invoice-dollar"></i> Reports</a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" onclick="return confirmLogout()" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>System Performance Reports</h1><br>
            </header>

            <div class="stats-grid">
                <div class="report-card">
                    <h3>Total Revenue</h3>
                    <div class="value revenue-value">RM <?php echo number_format($stats_result['total_revenue'] ?? 0, 2); ?></div>
                </div>
                <div class="report-card">
                    <h3>Total Bookings</h3>
                    <div class="value"><?php echo $stats_result['total_bookings'] ?? 0; ?></div>
                </div>
                <div class="report-card">
                    <h3>Completed Rides</h3>
                    <div class="value" style="color: #0288d1;"><?php echo $stats_result['completed_rides'] ?? 0; ?></div>
                </div>
                <div class="report-card">
                    <h3>Cancellation Rate</h3>
                    <div class="value" style="color: #d32f2f;">
                        <?php 
                            $total = $stats_result['total_bookings'] ?: 1;
                            echo round(($stats_result['cancelled_rides'] / $total) * 100, 1); 
                        ?>%
                    </div>
                </div>
            </div>

            <section class="recent-activity">
                <div class="section-header">
                    <h2><i class="fa-solid fa-trophy"></i> Top 5 Drivers by Earnings</h2>
                </div>
                <div class="table-container">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Driver Name</th>
                                <th>Total Trips</th>
                                <th>Total Earnings</th>
                                <th>Avg. Per Trip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($driver_perf_result && $driver_perf_result->num_rows > 0): ?>
                                <?php while($row = $driver_perf_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['driverName']); ?></td>
                                        <td><?php echo $row['trips']; ?></td>
                                        <td style="font-weight: bold;">RM <?php echo number_format($row['earnings'], 2); ?></td>
                                        <td>RM <?php echo number_format($row['earnings'] / $row['trips'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;">No completed trip data available.</td></tr>
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