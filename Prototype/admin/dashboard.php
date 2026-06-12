<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Check authentication
check_auth('admin');

// Get current tab from GET parameter (default: overview)
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Handle user creation
$success_message = '';
$error_message = '';

if (isset($_POST['create_user'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = "Email already exists!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Create user
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $password_hash, $role, $full_name]);
            $user_id = $pdo->lastInsertId();

            // If agency, create agency record
            if ($role === 'agency') {
                $agency_name = trim($_POST['agency_name'] ?? '');
                $commission_rate = (float)($_POST['commission_rate'] ?? 10.0);
                
                $stmt = $pdo->prepare("INSERT INTO travel_agencies (user_id, agency_name, commission_rate) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $agency_name, $commission_rate]);
            }

            // Add audit log
            add_audit_log('user_created', "Created new $role user: $email");

            $success_message = "User created successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Error creating user: " . $e->getMessage();
    }
}

// Fetch data for dashboard
$today = date('Y-m-d');
$current_month = date('Y-m');

// Get total reservations
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM reservations");
$total_reservations = $stmt->fetch()['count'];

// Get today's revenue
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_cost), 0) AS total FROM reservations WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_revenue = $stmt->fetch()['total'];

// Get monthly revenue
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_cost), 0) AS total FROM reservations WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$monthly_revenue = $stmt->fetch()['total'];

// Get audit logs
$stmt = $pdo->query("SELECT al.*, u.full_name, u.role FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 50");
$audit_logs = $stmt->fetchAll();

// Get failed login attempts
$stmt = $pdo->query("SELECT * FROM failed_logins ORDER BY created_at DESC LIMIT 20");
$failed_logins = $stmt->fetchAll();

// Get agency performance
$stmt = $pdo->query("SELECT ta.id, ta.agency_name, COUNT(r.id) AS total_bookings, COALESCE(SUM(r.total_cost), 0) AS total_revenue, ta.commission_rate, (COALESCE(SUM(r.total_cost), 0) * (ta.commission_rate / 100)) AS total_commission FROM travel_agencies ta LEFT JOIN users u ON ta.user_id = u.id LEFT JOIN reservations r ON r.user_id = u.id GROUP BY ta.id ORDER BY total_bookings DESC");
$agency_performance = $stmt->fetchAll();

// Get all reservations
$stmt = $pdo->query("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id ORDER BY r.created_at DESC");
$reservations = $stmt->fetchAll();

// Get all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ralmitrokij Hotel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        :root {
            --admin-primary: #1a1a1a;
            --admin-accent: #c9a84c;
            --admin-secondary: #666666;
            --admin-bg-light: #f8f9fa;
            --admin-border: #e9ecef;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f5f5f0 0%, #e0e0d5 100%);
            margin: 0;
            padding: 0;
        }

        .glassmorphism-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }

        .dashboard-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--admin-primary);
            text-decoration: none;
        }

        .admin-logo span {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 400;
            letter-spacing: 2px;
            color: var(--admin-secondary);
            display: block;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item a {
            text-decoration: none;
            color: var(--admin-secondary);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }

        .nav-item a:hover,
        .nav-item a.active {
            color: var(--admin-primary);
            border-bottom-color: var(--admin-accent);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            font-size: 0.85rem;
            color: var(--admin-secondary);
        }

        .logout-btn {
            text-decoration: none;
            color: var(--admin-accent);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid var(--admin-accent);
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--admin-accent);
            color: white;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .breadcrumb {
            font-size: 0.75rem;
            color: var(--admin-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin: 0 0 10px 0;
            color: var(--admin-primary);
        }

        .page-subtitle {
            color: var(--admin-secondary);
            margin: 0;
            font-size: 0.95rem;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .metric-label {
            font-size: 0.75rem;
            color: var(--admin-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .metric-value {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--admin-accent);
            margin: 0;
        }

        .section-title {
            margin-bottom: 25px;
        }

        .section-title h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin: 0 0 8px 0;
            color: var(--admin-primary);
        }

        .section-title p {
            margin: 0;
            color: var(--admin-secondary);
            font-size: 0.85rem;
        }

        .data-table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .data-table thead {
            background: var(--admin-bg-light);
        }

        .data-table th {
            padding: 15px;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--admin-secondary);
            border-bottom: 1px solid var(--admin-border);
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid var(--admin-border);
            font-size: 0.9rem;
            color: var(--admin-primary);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-checked-in {
            background: #cce5ff;
            color: #004085;
        }

        .status-checked-out {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
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
            color: var(--admin-secondary);
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Montserrat', sans-serif;
            background: rgba(255, 255, 255, 0.7);
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.15);
        }

        .btn-primary {
            background: var(--admin-accent);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #b59444;
        }

        .btn-secondary {
            background: transparent;
            color: var(--admin-accent);
            border: 1px solid var(--admin-accent);
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: var(--admin-accent);
            color: white;
        }

        .btn-center {
            display: flex;
            justify-content: center;
            margin-top: 25px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .agency-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .agency-card h4 {
            font-family: 'Playfair Display', serif;
            margin: 0 0 15px 0;
            color: var(--admin-primary);
        }

        .agency-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
        }

        .agency-stat-value {
            font-weight: 700;
            color: var(--admin-accent);
        }

        @media (max-width: 992px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            .grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Fixed Top Navigation -->
    <header class="dashboard-header">
        <div class="header-inner">
            <a href="dashboard.php" class="admin-logo">
                RALMITROKIJ <span>INTERNAL MANAGEMENT</span>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="dashboard.php?tab=overview" class="<?php echo $current_tab === 'overview' ? 'active' : ''; ?>">Overview</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=reservations" class="<?php echo $current_tab === 'reservations' ? 'active' : ''; ?>">Reservations</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=rooms" class="<?php echo $current_tab === 'rooms' ? 'active' : ''; ?>">Rooms</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=users" class="<?php echo $current_tab === 'users' ? 'active' : ''; ?>">Users</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=audit" class="<?php echo $current_tab === 'audit' ? 'active' : ''; ?>">Audit Logs</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=agencies" class="<?php echo $current_tab === 'agencies' ? 'active' : ''; ?>">Agencies</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <div class="user-info">
                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        
        <!-- View: Overview -->
        <?php if ($current_tab === 'overview'): ?>
            <div class="page-header">
                <span class="breadcrumb">Internal &rsaquo; Dashboard</span>
                <h1 class="page-title">Operational Overview</h1>
                <p class="page-subtitle">Real-time metrics and system health monitoring.</p>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Reservations</div>
                    <p class="metric-value"><?php echo number_format($total_reservations); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Today's Revenue</div>
                    <p class="metric-value">₱<?php echo number_format($today_revenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Monthly Revenue</div>
                    <p class="metric-value">₱<?php echo number_format($monthly_revenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Active Rooms</div>
                    <p class="metric-value"><?php echo count($rooms); ?></p>
                </div>
            </div>

            <div class="grid-2">
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Recent Activity</h3>
                        <p>Last 50 actions in the system</p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('M j, Y - g:i A', strtotime($log['created_at']))); ?></td>
                                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Failed Login Attempts</h3>
                        <p>Last 20 login failures</p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Email</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($failed_logins as $login): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('M j, Y - g:i A', strtotime($login['created_at']))); ?></td>
                                        <td><?php echo htmlspecialchars($login['email']); ?></td>
                                        <td><?php echo htmlspecialchars($login['ip_address'] ?? 'Unknown'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($current_tab === 'reservations'): ?>

            <div class="page-header">
                <span class="breadcrumb">Live Status &rsaquo; Reservations</span>
                <h1 class="page-title">Guest Bookings</h1>
                <p class="page-subtitle">Manage and track all hotel bookings and guest arrivals.</p>
            </div>

            <div class="glassmorphism-card">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Stay Dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $res): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($res['booking_ref']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($res['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($res['check_in']) . ' - ' . htmlspecialchars($res['check_out']); ?></td>
                                    <td><?php echo htmlspecialchars($res['guests_adults']) . ' Adults'; ?>
                                        <?php if ($res['guests_children'] > 0): ?>
                                            , <?php echo htmlspecialchars($res['guests_children']); ?> Children
                                        <?php endif; ?>
                                    </td>
                                    <td>₱<?php echo number_format($res['total_cost'], 2); ?></td>
                                    <td><span class="status-badge status-<?php echo $res['status']; ?>"><?php echo htmlspecialchars($res['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($current_tab === 'rooms'): ?>

            <div class="page-header">
                <span class="breadcrumb">Inventory &rsaquo; Rooms</span>
                <h1 class="page-title">Room Management</h1>
                <p class="page-subtitle">Monitor availability and assign room tasks.</p>
            </div>

            <div class="glassmorphism-card">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Room Name</th>
                                <th>Category</th>
                                <th>Price per Night</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($room['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($room['category']); ?></td>
                                    <td>₱<?php echo number_format($room['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($room['description'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($current_tab === 'users'): ?>

            <div class="page-header">
                <span class="breadcrumb">System &rsaquo; Users</span>
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Create new staff and travel agency accounts.</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="grid-2">
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Create New User</h3>
                        <p>Fill in the details to add a new user</p>
                    </div>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required placeholder="Enter full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required placeholder="Create a password">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="staff">Staff</option>
                                <option value="agency">Travel Agency</option>
                            </select>
                        </div>
                        
                        <div id="agency_fields" style="display: none;">
                            <div class="form-group">
                                <label for="agency_name">Agency Name</label>
                                <input type="text" id="agency_name" name="agency_name" placeholder="Enter agency name">
                            </div>
                            <div class="form-group">
                                <label for="commission_rate">Commission Rate (%)</label>
                                <input type="number" id="commission_rate" name="commission_rate" step="0.01" min="0" max="100" value="10.0">
                            </div>
                        </div>

                        <div class="btn-center">
                            <button type="submit" name="create_user" class="btn-primary">Create User</button>
                        </div>
                    </form>
                </div>

                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Existing Users</h3>
                        <p>All registered system users</p>
                    </div>
                    <?php
                        $stmt = $pdo->query("SELECT u.*, ta.agency_name FROM users u LEFT JOIN travel_agencies ta ON u.id = ta.user_id ORDER BY u.role, u.full_name");
                        $users = $stmt->fetchAll();
                    ?>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Agency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['agency_name'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                // Show/hide agency fields based on role
                document.addEventListener('DOMContentLoaded', function() {
                    const roleSelect = document.getElementById('role');
                    const agencyFields = document.getElementById('agency_fields');

                    function toggleAgencyFields() {
                        if (roleSelect.value === 'agency') {
                            agencyFields.style.display = 'block';
                        } else {
                            agencyFields.style.display = 'none';
                        }
                    }

                    toggleAgencyFields();
                    roleSelect.addEventListener('change', toggleAgencyFields);
                });
            </script>

        <?php elseif ($current_tab === 'audit'): ?>

            <div class="page-header">
                <span class="breadcrumb">Security &rsaquo; Audit Logs</span>
                <h1 class="page-title">System Audit Trails</h1>
                <p class="page-subtitle">Monitoring system activity and security events.</p>
            </div>

            <div class="glassmorphism-card">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('M j, Y - g:i A', strtotime($log['created_at']))); ?></td>
                                    <td><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($log['role'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['details'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?? 'Unknown'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($current_tab === 'agencies'): ?>

            <div class="page-header">
                <span class="breadcrumb">Partners &rsaquo; Agencies</span>
                <h1 class="page-title">Agency Performance</h1>
                <p class="page-subtitle">Track travel agency performance and commission earnings.</p>
            </div>

            <div class="grid-3">
                <?php foreach ($agency_performance as $agency): ?>
                    <div class="agency-card">
                        <h4><?php echo htmlspecialchars($agency['agency_name']); ?></h4>
                        <div class="agency-stats" style="margin-bottom: 10px;">
                            <span>Total Bookings:</span>
                            <span class="agency-stat-value"><?php echo number_format($agency['total_bookings']); ?></span>
                        </div>
                        <div class="agency-stats" style="margin-bottom: 10px;">
                            <span>Total Revenue:</span>
                            <span class="agency-stat-value">₱<?php echo number_format($agency['total_revenue'], 2); ?></span>
                        </div>
                        <div class="agency-stats" style="margin-bottom: 10px;">
                            <span>Commission Rate:</span>
                            <span class="agency-stat-value"><?php echo number_format($agency['commission_rate'], 2); ?>%</span>
                        </div>
                        <div class="agency-stats" style="border-top: 1px solid var(--admin-border); padding-top: 10px; margin-top: 10px;">
                            <span>Total Commission:</span>
                            <span class="agency-stat-value">₱<?php echo number_format($agency['total_commission'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
