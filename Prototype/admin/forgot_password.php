<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body style="background: var(--bg-light);">
    <div class="container">
        <div class="admin-login-card" style="background: var(--white); box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-top: 100px;">
            <h2>Forgot Password</h2>
            <p style="font-size: 0.9rem; color: var(--secondary-color); margin-bottom: 30px;">Enter your email address and we'll send you a link to reset your password.</p>
            
            <form action="#">
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="email">EMAIL ADDRESS</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <button type="button" class="btn-primary" style="width: 100%; margin-bottom: 20px;" onclick="alert('Demo: Reset link sent to email.')">SEND RESET LINK</button>
                
                <a href="login.php" style="font-size: 0.8rem; text-decoration: underline; color: var(--secondary-color);">BACK TO LOGIN</a>
            </form>
        </div>
    </div>
</body>
</html>
