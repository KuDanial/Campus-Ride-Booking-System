<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get data from the Log In form
    $user = $_POST['login_user'];
    $pass = $_POST['login_pass'];
    $role = $_POST['login_role'];

    // 2. Prepare SQL to prevent injection
    $stmt = $conn->prepare("SELECT accountID, accountPassword, accountType FROM account WHERE accountUsername = ? AND accountType = ?");
    $stmt->bind_param("ss", $user, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // 3. Check password (In real apps, use password_verify for hashed passwords)
        if ($pass === $row['accountPassword']) {
            
            // Set Session variables
            $_SESSION['id'] = $row['accountID'];
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $row['accountType'];

            // 4. Redirect based on role
            if ($row['accountType'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['accountType'] == 'driver') {
                header("Location: index.html"); // Replace with driver dashboard if you have one
            } else {
                header("Location: customer_dashboard.php"); // Customer dashboard
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found or role mismatch!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grab - Login / Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align: center; margin-bottom: 25px;">
                <h2 style="color: var(--primary-color); margin:0; font-size: 28px;">
                    <i class="fa-solid fa-location-dot"></i> GrabWeb
                </h2>
                <p style="color:#888; font-size: 0.9rem;">Campus Ride Solution</p>
            </div>

            <?php if(isset($error)): ?>
                <div style="color: red; text-align: center; margin-bottom: 15px; font-size: 0.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
                <div style="color: var(--primary-color); text-align: center; margin-bottom: 15px; font-size: 0.9rem;">
                    <i class="fa-solid fa-circle-check"></i> Account created! Please log in.
                </div>
            <?php endif; ?>

            <div class="auth-tabs">
                <button class="tab-btn active" onclick="switchTab('login')">Log In</button>
                <button class="tab-btn" onclick="switchTab('signup')">Sign Up</button>
            </div>

            <form id="login-form" action="login.php" method="POST">
                <div class="role-selector">
                    <label>Select Role:</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="login_role" value="customer" checked> Customer
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="login_role" value="driver"> Driver
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="login_role" value="admin"> Admin
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="login_user" class="form-control" placeholder="Enter Username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="login_pass" class="form-control" placeholder="Enter Password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Log In <i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <form id="signup-form" action="signup.php" method="POST" class="hidden">
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-id-card"></i>
                        <input type="text" name="fullName" class="form-control" placeholder="Enter Full Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Create Username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Register As</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-users"></i>
                        <select name="role" id="signup-role" class="form-control" style="background: white;" required>
                            <option value="" disabled selected>Select Role...</option>
                            <option value="customer">Student / Staff (Customer)</option>
                            <option value="driver">Driver</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Create Password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Sign Up <i class="fa-solid fa-user-plus"></i></button>
            </form>

            <a style="text-align:center; margin-top:15px;" href="index.html" class="back-home">← Back to Homepage</a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            const tabs = document.querySelectorAll('.tab-btn');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
            } else {
                loginForm.classList.add('hidden');
                signupForm.classList.remove('hidden');
                tabs[0].classList.remove('active');
                tabs[1].classList.add('active');
            }
        }
    </script>
</body>
</html>