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
        header('Location: dashboard.php');
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
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body style="background: var(--bg-light);">
    <div class="container">
        <div style="margin-top: 50px; text-align: center;">
            <a href="../index.php" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 2px; color: var(--secondary-color);">← BACK TO SITE</a>
        </div>
        
        <div class="admin-login-card">
            <div class="section-title">
                <span>AUTHENTICATION</span>
                <div class="logo-box" style="display: inline-block; margin-bottom: 20px;">RALMITROKIJ</div>
                <p style="font-size: 0.7rem; letter-spacing: 3px; color: var(--secondary-color); margin-top: 10px; font-weight: 700;">ADMINISTRATION PANEL</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: var(--bg-light); color: var(--error-color); padding: 15px; margin-bottom: 30px; font-size: 0.8rem; border: 1px solid var(--error-color); letter-spacing: 1px;">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group" style="margin-bottom: 25px; text-align: left;">
                    <label for="username">USERNAME</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required style="background: var(--bg-light);">
                </div>
                
                <div class="form-group" style="margin-bottom: 30px; text-align: left;">
                    <label for="password">PASSWORD</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Enter password" required style="background: var(--bg-light);">
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn-primary" style="width: 100%; margin-bottom: 25px;">SIGN IN</button>
                
                <a href="forgot_password.php" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-color); font-weight: 600;">Forgot credentials?</a>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 40px; font-size: 0.7rem; color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 300;">
            &copy; <?php echo date('Y'); ?> RALMITROKIJ HOTEL &mdash; SECURED AREA<br>
            <span style="opacity: 0.6; display: block; margin-top: 10px;">DEMO ACCESS: <span style="font-weight: 700;">ADMIN</span> / <span style="font-weight: 700;">PASSWORD</span></span>
        </div>
    </div>
</body>
</html>
