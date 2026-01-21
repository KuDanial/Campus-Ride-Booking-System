<?php
session_start();
include "db_conn.php";

// Prevent browser from caching the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. SECURITY
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. FETCH TOTAL CUSTOMERS & DRIVERS (Existing)
$total_customers = ($conn->query("SELECT COUNT(*) as total FROM customer")->fetch_assoc())['total'];
$total_drivers = ($conn->query("SELECT COUNT(*) as total FROM driver")->fetch_assoc())['total'];

// 3. FETCH RIDES TODAY (Dynamic)
$sql_today = "SELECT COUNT(*) as total FROM booking WHERE DATE(bookingTimestamp) = CURDATE()";
$rides_today = ($conn->query($sql_today)->fetch_assoc())['total'];

// 4. FETCH TOTAL EARNINGS TODAY (Dynamic)
// Note: bookingFare is the column we added in previous steps
$sql_earnings = "SELECT SUM(bookingFare) as total FROM booking WHERE DATE(bookingTimestamp) = CURDATE() AND bookingStatus = 'Completed'";
$earnings_today = ($conn->query($sql_earnings)->fetch_assoc())['total'] ?? 0;

// 5. FETCH RECENT BOOKINGS (Dynamic)
$sql_bookings = "SELECT b.*, c.custName, d.driverName 
                 FROM booking b
                 LEFT JOIN customer c ON b.custID = c.custID
                 LEFT JOIN driver d ON b.driverID = d.driverID
                 ORDER BY b.bookingTimestamp DESC LIMIT 5";
$bookings_result = $conn->query($sql_bookings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrabWeb - Admin Dashboard</title>
    
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
                <li><a href="#" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin_manage_driver.php"><i class="fa-solid fa-car"></i> Manage Drivers</a></li>
                <li><a href="admin_manage_customer.php"><i class="fa-solid fa-users"></i> Manage Customers</a></li>
                <li><a href="admin_manage_booking.php"><i class="fa-solid fa-ticket"></i> View Bookings</a></li>
                
                <li class="menu-divider">System</li>
                <li><a href="admin_manage_admin.php"><i class="fa-solid fa-user-shield"></i> Manage Admins <span class="badge">New</span></a></li>
                <li><a href="admin_report.php"><i class="fa-solid fa-file"></i> Reports</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="#" onclick="confirmLogout()"class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            
            <header class="dashboard-header">
                <div class="header-title">
                    <h1>Overview</h1>
                    <p>Welcome back, <?php echo $_SESSION['username']; ?>.</p>
                </div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=02B150&color=fff" alt="Admin">
                    <span>Super Admin</span>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_customers); ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_drivers); ?></h3>
                        <p>Active Drivers</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="stat-info"> 
                        <h3><?php echo number_format($rides_today); ?></h3>
                        <p>Rides Today</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>RM <?php echo number_format($earnings_today, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <section class="recent-activity">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <button class="btn-primary" style="width: auto; padding: 8px 20px;">View All</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Route</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tbody>
                                <?php if ($bookings_result->num_rows > 0): ?>
                                    <?php while($row = $bookings_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#BK-<?php echo $row['bookingID']; ?></td>
                                            <td><?php echo htmlspecialchars($row['custName'] ?? 'Deleted User'); ?></td>
                                            <td><?php echo htmlspecialchars($row['driverName'] ?? 'Searching...'); ?></td>
                                            <td><?php echo htmlspecialchars($row['pickupLocation']); ?> -> <?php echo htmlspecialchars($row['dropoffLocation']); ?></td>
                                            <td>
                                                <span class="status <?php echo strtolower($row['bookingStatus']); ?>">
                                                    <?php echo $row['bookingStatus']; ?>
                                                </span>
                                            </td>
                                            <td>RM <?php echo number_format($row['bookingFare'] ?? 0, 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align:center;">No bookings found today.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
    <script>
        function confirmLogout() {
                let text = "Are you sure you want to log out?";
                if (confirm(text) == true) {
                    // If user clicks OK, redirect to public index
                    window.location.href = "logout.php";
                    return true;
                } else {
                    // If user clicks Cancel, do nothing
                    return false;
                }
            }
    </script>
</body>
</html>