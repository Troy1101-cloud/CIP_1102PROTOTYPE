<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Track failed login attempt
    $stmt = $pdo->prepare("INSERT INTO failed_logins (email, ip_address) VALUES (?, ?)");
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);

    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Clear failed logins for this email
        $stmt = $pdo->prepare("DELETE FROM failed_logins WHERE email = ?");
        $stmt->execute([$email]);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];

        // Add audit log
        add_audit_log('login', 'Admin user logged in');

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
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
    <style>
        .glassmorphism-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .glassmorphism-input {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #f5f5f0 0%, #e0e0d5 100%);">
    <div class="container" style="max-width: 500px; padding-top: 80px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="../index.php" style="text-decoration: none; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 2px; color: var(--secondary-color);">← BACK TO SITE</a>
        </div>
        
        <div class="glassmorphism-card" style="padding: 40px;">
            <div class="section-title" style="text-align: center; margin-bottom: 30px;">
                <span>AUTHENTICATION</span>
                <div style="font-family: var(--font-header); font-size: 2rem; margin-bottom: 10px; color: var(--primary-color);">RALMITROKIJ</div>
                <p style="font-size: 0.7rem; letter-spacing: 3px; color: var(--secondary-color); font-weight: 700;">ADMINISTRATION PANEL</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: rgba(255, 0, 0, 0.1); color: var(--error-color); padding: 15px; margin-bottom: 30px; border-radius: 8px; border: 1px solid var(--error-color); font-size: 0.9rem; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 8px;">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="admin@ralmitrokij.com" required class="glassmorphism-input" style="width: 100%; padding: 14px 16px; font-size: 1rem;">
                </div>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password" style="font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 8px;">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required class="glassmorphism-input" style="width: 100%; padding: 14px 16px; font-size: 1rem;">
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" name="login" class="btn-primary" style="width: 100%; padding: 16px; font-size: 1rem; letter-spacing: 2px;">SIGN IN</button>
                </div>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 40px; font-size: 0.7rem; color: var(--secondary-color); text-transform: uppercase; letter-spacing: 2px; font-weight: 300;">
            &copy; <?php echo date('Y'); ?> RALMITROKIJ HOTEL &mdash; SECURED AREA
        </div>
    </div>
</body>
</html>
