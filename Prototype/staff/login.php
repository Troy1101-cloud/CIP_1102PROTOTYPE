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
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'staff'");
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
        add_audit_log('login', 'Staff user logged in');

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
    <title>Staff Login | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f5f5f0 0%, #e0e0d5 100%);
        }
        .glassmorphism-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 40px;
            max-width: 500px;
            margin: 80px auto;
        }
        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .section-title span {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #666;
            display: block;
            margin-bottom: 10px;
        }
        .section-title h1 {
            font-family: 'Playfair Display', serif;
            margin: 0 0 10px 0;
            font-size: 1.8rem;
            color: #1a1a1a;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.7);
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #c9a84c;
            box-shadow: 0 0 0 3px rgba(201,168,76,0.15);
        }
        .btn-primary {
            background: #c9a84c;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-primary:hover {
            background: #b59444;
        }
        .alert-error {
            background: rgba(248,215,218,0.7);
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .back-link {
            text-align: center;
            margin-bottom: 30px;
        }
        .back-link a {
            text-decoration: none;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 2px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="../index.php">&larr; Back to site</a>
        </div>
        <div class="glassmorphism-card">
            <div class="section-title">
                <span>Authentication</span>
                <h1>Staff Login</h1>
                <p style="margin:0; color:#666; font-size:0.9rem;">Sign in to manage daily operations</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="staff@ralmitrokij.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" name="login" class="btn-primary">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>