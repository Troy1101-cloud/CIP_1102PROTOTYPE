<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple mock auth for prototype
    if ($username === 'admin' && $password === 'password') {
        session_start();
        $_SESSION['admin_logged_in'] = true;
        header('Location: bookings.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body style="background: var(--bg-light);">
    <div class="container">
        <div style="margin-top: 50px; text-align: left;">
            <a href="../index.php" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">&larr; BACK TO SITE</a>
        </div>
        
        <div class="admin-login-card" style="background: var(--white); box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <div class="logo-box" style="display: inline-block; margin-bottom: 20px;">RALMITROKIJ</div>
            <p style="font-size: 0.8rem; letter-spacing: 2px; color: var(--secondary-color); margin-bottom: 30px;">ADMIN PANEL</p>
            
            <?php if ($error): ?>
                <div style="background: #fef2f2; color: var(--error-color); padding: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fecaca;">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="username">USERNAME</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="password">PASSWORD</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <span style="position: absolute; right: 10px; top: 10px; cursor: pointer; font-size: 0.8rem; color: var(--secondary-color);">👁</span>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn-primary" style="width: 100%; margin-bottom: 20px;">LOGIN</button>
                
                <a href="forgot_password.php" style="font-size: 0.8rem; text-decoration: underline; color: var(--secondary-color);">FORGOT PASSWORD?</a>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 40px; font-size: 0.7rem; color: var(--secondary-color); text-transform: uppercase; letter-spacing: 1px;">
            &copy; 2024 RALMITROKIJ HOTEL &mdash; RESTRICTED AREA<br>
            <span style="opacity: 0.5;">DEMO: ADMIN / PASSWORD</span>
        </div>
    </div>
</body>
</html>
