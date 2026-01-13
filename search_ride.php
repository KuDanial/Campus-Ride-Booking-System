<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pickup = $_GET['pickup'] ?? 'Unknown';
$dropoff = $_GET['dropoff'] ?? 'Unknown';

// Updated SQL to fetch drivers with their vehicle info and average rating
$sql = "SELECT d.*, v.vehicleModel, v.vehicleColor, v.vehiclePlateNum, AVG(f.rating) as avgRating
        FROM driver d 
        INNER JOIN vehicle v ON d.driverID = v.driverID
        LEFT JOIN booking b ON d.driverID = b.driverID
        LEFT JOIN feedback f ON b.bookingID = f.bookingID
        WHERE d.driverID NOT IN (SELECT driverID FROM booking WHERE bookingStatus = 'Pending')
        GROUP BY d.driverID";

$result = $conn->query($sql);

// We also need to prepare a way to fetch specific reviews for the modal
function getDriverReviews($conn, $driverID) {
    $reviews = [];
    $sql = "SELECT f.comments, f.rating, f.feedbackDate, c.custName 
            FROM feedback f
            JOIN booking b ON f.bookingID = b.bookingID
            JOIN customer c ON b.custID = c.custID
            WHERE b.driverID = ?
            ORDER BY f.feedbackDate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driverID);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $reviews[] = $row;
    }
    return $reviews;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Rides</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header class="search-header">
    <div class="header-route">
        <p style="margin: 0; font-size: 0.8rem; opacity: 0.9; color: gray">Searching rides for:</p>
        <div class="route-display" style="color: gray; font-size: 1.1rem;">
            <?php echo htmlspecialchars($pickup); ?> 
            <i class="fa-solid fa-arrow-right" style="margin: 0 10px; font-size: 0.9rem;"></i> 
            <?php echo htmlspecialchars($dropoff); ?>
        </div>
    </div>
</header>

<section class="results-preview" style="padding-top: 20px;">
    <h2>Available Drivers</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): 
            $price = ($pickup == "Kolej TDM" && $dropoff == "Block A") ? "5.00" : "8.00";
            $driverID = $row['driverID'];
            $driverRating = $row['avgRating'] ? number_format($row['avgRating'], 1) : "5.0";
            
            // Fetch reviews for this driver to pass into JavaScript
            $reviews = getDriverReviews($conn, $driverID);
            $driverData = [
                'name' => $row['driverName'],
                'phone' => $row['driverPhone'],
                'rating' => $driverRating,
                'vehicle' => $row['vehicleModel'] . " (" . $row['vehicleColor'] . ")",
                'plate' => $row['vehiclePlateNum'],
                'reviews' => $reviews
            ];
        ?>
            <div class="ride-card">
                <div onclick='openDriverModal(<?php echo json_encode($driverData); ?>)' class="driver-section driver-link">
                    <div class="driver-img">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['driverName']); ?>&background=02B150&color=fff" style="width:100%; border-radius:50%;">
                    </div>
                    <div class="driver-info">
                        <h3><?php echo htmlspecialchars($row['driverName']); ?></h3>
                        <span class="rating">★ <?php echo $driverRating; ?></span>
                        <p class="car-model"><?php echo htmlspecialchars($row['vehicleModel']); ?> (<?php echo htmlspecialchars($row['vehicleColor']); ?>)</p>
                        <p class="plate-num"><?php echo htmlspecialchars($row['vehiclePlateNum']); ?></p>
                    </div>
                </div>

                <div class="trip-details">
                    <div class="time">Available Now</div>
                    <div class="route"><?php echo htmlspecialchars($pickup); ?> <i class="fa-solid fa-arrow-right-long"></i> <?php echo htmlspecialchars($dropoff); ?></div>
                    <div class="amenities">
                        <span><i class="fa-solid fa-snowflake"></i> AC</span>
                        <span><i class="fa-solid fa-users"></i> 4 Seats</span>
                    </div>
                </div>

                <div class="price-action">
                    <div class="price">RM <?php echo $price; ?></div>
                    <form action="booking_confirmation.php" method="POST">
                        <input type="hidden" name="driverID" value="<?php echo $driverID; ?>">
                        <input type="hidden" name="pickup" value="<?php echo htmlspecialchars($pickup); ?>">
                        <input type="hidden" name="dropoff" value="<?php echo htmlspecialchars($dropoff); ?>">
                        <input type="hidden" name="fare" value="<?php echo $price; ?>">
                        <button type="submit" class="book-btn">Book Now</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:#666;">No drivers available at the moment.</p>
    <?php endif; ?>

    
    <div style="text-align: center; margin-top: 20px;">
        <a href="customer_dashboard.php" style="color: #666; text-decoration: none;"><i class="fa-solid fa-chevron-left"></i> Change Location</a>
    </div>
</section>

<div id="driverModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalBody">
            </div>
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
function openDriverModal(data) {
    const modal = document.getElementById("driverModal");
    const body = document.getElementById("modalBody");
    
    let reviewsHtml = '';
    if (data.reviews.length > 0) {
        data.reviews.forEach(r => {
            reviewsHtml += `
                <div class="review-item">
                    <div class="review-header">
                        <strong>${r.custName}</strong>
                        <span class="stars">${'★'.repeat(r.rating)}</span>
                    </div>
                    <p style="font-size: 0.85rem; color: #555;">${r.comments}</p>
                    <small style="color: #999;">${r.feedbackDate}</small>
                </div>
            `;
        });
    } else {
        reviewsHtml = '<p style="color: #888; text-align: center;">No reviews yet.</p>';
    }

    body.innerHTML = `
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=02B150&color=fff" style="width: 80px; border-radius: 50%; margin-bottom: 10px;">
            <h2 style="margin:0;">${data.name}</h2>
            <span class="rating" style="font-size: 1.1rem; color: #fbc02d;">★ ${data.rating}</span>
        </div>
        
        <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p><strong><i class="fa-solid fa-car"></i> Vehicle:</strong> ${data.vehicle}</p>
            <p><strong><i class="fa-solid fa-hashtag"></i> Plate:</strong> ${data.plate}</p>
            <p><strong><i class="fa-solid fa-phone"></i> Contact:</strong> ${data.phone || 'N/A'}</p>
        </div>

        <h3 style="border-bottom: 2px solid #02B150; padding-bottom: 5px;"><i class="fa-solid fa-star"></i> Recent Reviews</h3>
        <div style="max-height: 250px; overflow-y: auto;">
            ${reviewsHtml}
        </div>
    `;
    
    modal.style.display = "block";
}

function closeModal() {
    document.getElementById("driverModal").style.display = "none";
}

// Close modal if clicking outside of the content box
window.onclick = function(event) {
    const modal = document.getElementById("driverModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>