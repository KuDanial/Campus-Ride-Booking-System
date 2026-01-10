<?php
session_start();
include "db_conn.php";

// 1. SECURITY: Ensure only admins can access this page
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. FETCH TOTAL CUSTOMERS
$sql_cust = "SELECT COUNT(*) as total FROM customer";
$result_cust = $conn->query($sql_cust);
$total_customers = ($result_cust->fetch_assoc())['total'];

// 3. FETCH TOTAL DRIVERS
$sql_driver = "SELECT COUNT(*) as total FROM driver";
$result_driver = $conn->query($sql_driver);
$total_drivers = ($result_driver->fetch_assoc())['total'];

// 4. FETCH RECENT BOOKINGS (Joining tables to get Names)
$sql_bookings = "SELECT b.bookingID, c.custName, d.driverName, b.pickupLocation, b.dropoffLocation, b.bookingStatus 
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
                <li><a href="#"><i class="fa-solid fa-car"></i> Manage Drivers</a></li>
                <li><a href="#"><i class="fa-solid fa-users"></i> Manage Customers</a></li>
                <li><a href="#"><i class="fa-solid fa-ticket"></i> View Bookings</a></li>
                
                <li class="menu-divider">System</li>
                <li><a href="admin_manage.php"><i class="fa-solid fa-user-shield"></i> Manage Admins <span class="badge">New</span></a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
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
                        <h3>320</h3>
                        <p>Rides Today</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>RM 4,500</h3>
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
                            <tr>
                                <td>#BK-2023</td>
                                <td>Ali Ahmad</td>
                                <td>Pak Abu</td>
                                <td>Gate A -> Library</td>
                                <td><span class="status completed">Completed</span></td>
                                <td>RM 5.00</td>
                            </tr>
                            <tr>
                                <td>#BK-2024</td>
                                <td>Siti Sarah</td>
                                <td>Abang Grab</td>
                                <td>Kolej Melati -> Mall</td>
                                <td><span class="status pending">Searching...</span></td>
                                <td>RM 12.00</td>
                            </tr>
                            <tr>
                                <td>#BK-2025</td>
                                <td>John Doe</td>
                                <td>Mat Rempit</td>
                                <td>Cafeteria -> Gate B</td>
                                <td><span class="status cancelled">Cancelled</span></td>
                                <td>RM 3.00</td>
                            </tr>
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