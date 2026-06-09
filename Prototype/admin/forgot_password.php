<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body style="background: var(--bg-light);">
    <div class="container">
        <div style="margin-top: 50px; text-align: center;">
            <a href="login.php" style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 2px; color: var(--secondary-color);">← BACK TO LOGIN</a>
        </div>

        <div class="admin-login-card">
            <div class="section-title">
                <span>RECOVERY</span>
                <h2>Forgot Password</h2>
                <p style="font-size: 0.8rem; color: var(--secondary-color); margin-top: 10px;">Enter your email address and we'll send you a link to reset your access.</p>
            </div>
            
            <form action="#">
                <div class="form-group" style="margin-bottom: 30px; text-align: left;">
                    <label for="email">EMAIL ADDRESS</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required style="background: var(--bg-light);">
                </div>
                
                <button type="button" class="btn-primary" style="width: 100%; margin-bottom: 25px;" onclick="alert('Demo: Reset link sent to email.')">SEND RESET LINK</button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 40px; font-size: 0.7rem; color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 300;">
            &copy; <?php echo date('Y'); ?> RALMITROKIJ HOTEL &mdash; SECURED AREA
        </div>
    </div>
</body>
</html>
