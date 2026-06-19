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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = "Email already exists!";
        } else {
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $password, $role, $full_name]);
            $user_id = $pdo->lastInsertId();

            if ($role === 'agency') {
                $agency_name = trim($_POST['agency_name'] ?? '');
                $commission_rate = (float)($_POST['commission_rate'] ?? 10.0);
                $stmt = $pdo->prepare("INSERT INTO travel_agencies (user_id, agency_name, commission_rate) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $agency_name, $commission_rate]);
            }

            if (function_exists('add_audit_log')) add_audit_log('user_created', "Created new $role user: $email");
            $success_message = "User created successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Error creating user: " . $e->getMessage();
    }

}

if (isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];


    try {
        // Prevent changing admin's role
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_role = $stmt->fetchColumn();
        
        if ($current_role === 'admin' && $role !== 'admin') {
            $error_message = "Cannot change role of admin.";
        } else {
            if (!empty($password)) {
                
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $role, $password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ? WHERE id = ?");
                $stmt->execute([$full_name, $role, $user_id]);
            }
            $success_message = "User updated successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Error updating user: " . $e->getMessage();
    }

}

if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM travel_agencies WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'"); // Protect admin
        $stmt->execute([$user_id]);
        $success_message = "User deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting user: " . $e->getMessage();
    }

}

if (isset($_POST['update_reservation'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    $timestamp = date('Y-m-d H:i:s');
    

    try {
        if ($new_status === 'checked_in') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ?, checked_in_at = ? WHERE booking_ref = ?");
            $stmt->execute([$new_status, $timestamp, $booking_id]);
        } elseif ($new_status === 'checked_out') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ?, checked_out_at = ? WHERE booking_ref = ?");
            $stmt->execute([$new_status, $timestamp, $booking_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE booking_ref = ?");
            $stmt->execute([$new_status, $booking_id]);
        }
        $success_message = "Reservation updated successfully!";
        if (function_exists('add_audit_log')) add_audit_log('booking_updated', "Updated booking $booking_id status to $new_status");
    } catch (PDOException $e) {
        $error_message = "Error updating reservation: " . $e->getMessage();
    }

}

if (isset($_POST['create_room'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $amenities = isset($_POST['amenities']) ? (is_array($_POST['amenities']) ? implode(', ', $_POST['amenities']) : trim($_POST['amenities'])) : '';
    $image = 'room-1.jpg'; // default placeholder
    

    try {
        $stmt = $pdo->prepare("INSERT INTO rooms (name, category, price, description, quantity, amenities, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $description, $quantity, $amenities, $image]);
        $success_message = "Room created successfully!";
        if (function_exists('add_audit_log')) add_audit_log('room_created', "Created new room: $name");
    } catch (PDOException $e) {
        $error_message = "Error creating room: " . $e->getMessage();
    }

}

if (isset($_POST['edit_room'])) {
    $room_id = (int)$_POST['room_id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = (float)$_POST['price'];
    $status = trim($_POST['status'] ?? 'Available');
    $description = trim($_POST['description']);
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $amenities = isset($_POST['amenities']) ? (is_array($_POST['amenities']) ? implode(', ', $_POST['amenities']) : trim($_POST['amenities'])) : '';
    

    try {
        $stmt = $pdo->prepare("UPDATE rooms SET name=?, category=?, price=?, description=?, quantity=?, amenities=?, status=? WHERE id=?");
        $stmt->execute([$name, $category, $price, $description, $quantity, $amenities, $status, $room_id]);
        $success_message = "Room updated successfully!";
        if (function_exists('add_audit_log')) add_audit_log('room_updated', "Updated room: $name");
    } catch (PDOException $e) {
        $error_message = "Error updating room: " . $e->getMessage();
    }

}

if (isset($_GET['action']) && $_GET['action'] == 'delete_room' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id=?");
        $stmt->execute([$del_id]);
        $success_message = "Room deleted successfully!";
        if (function_exists('add_audit_log')) add_audit_log('room_deleted', "Deleted room ID: $del_id");
    } catch (PDOException $e) {
        $error_message = "Error deleting room: " . $e->getMessage();
    }

}

if (isset($_POST['update_addon'])) {
    $addon_id = (int)$_POST['addon_id'];
    $addon_name = trim($_POST['addon_name']);
    $addon_price = (float)$_POST['addon_price'];
    $addon_category = trim($_POST['addon_category']);
    

    try {
        $stmt = $pdo->prepare("UPDATE addons SET name=?, price=?, category=? WHERE id=?");
        $stmt->execute([$addon_name, $addon_price, $addon_category, $addon_id]);
        $success_message = "Add-on updated successfully!";
        if (function_exists('add_audit_log')) add_audit_log('addon_updated', "Updated Add-on: $addon_name");
    } catch (PDOException $e) {
        $error_message = "Error updating Add-on: " . $e->getMessage();
    }

}

if (isset($_POST['create_addon'])) {
    $addon_name = trim($_POST['addon_name']);
    $addon_price = (float)$_POST['addon_price'];
    $addon_category = trim($_POST['addon_category']);
    

    try {
        $stmt = $pdo->prepare("INSERT INTO addons (name, price, category) VALUES (?, ?, ?)");
        $stmt->execute([$addon_name, $addon_price, $addon_category]);
        $success_message = "Add-on created successfully!";
        if (function_exists('add_audit_log')) add_audit_log('addon_created', "Created Add-on: $addon_name");
    } catch (PDOException $e) {
        $error_message = "Error creating Add-on: " . $e->getMessage();
    }

}

if (isset($_POST['delete_addon'])) {
    $addon_id = (int)$_POST['addon_id'];
    

    try {
        $stmt = $pdo->prepare("DELETE FROM addons WHERE id = ?");
        $stmt->execute([$addon_id]);
        $success_message = "Add-on deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting Add-on: " . $e->getMessage();
    }

}

if (isset($_POST['update_commission'])) {
    $agency_id = (int)$_POST['agency_id'];
    $new_rate = (float)$_POST['commission_rate'];
    

    try {
        $stmt = $pdo->prepare("UPDATE travel_agencies SET commission_rate = ? WHERE id = ?");
        $stmt->execute([$new_rate, $agency_id]);
        $success_message = "Commission rate updated successfully!";
        if (function_exists('add_audit_log')) add_audit_log('agency_updated', "Updated commission rate to $new_rate% for agency ID $agency_id");
    } catch (PDOException $e) {
        $error_message = "Error updating commission rate: " . $e->getMessage();
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

    // Get daily and monthly reservations count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM reservations WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $daily_reservations_count = $stmt->fetch()['count'];

    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM reservations WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$current_month]);
    $monthly_reservations_count = $stmt->fetch()['count'];
    
    // Get room reservation counts
    $stmt = $pdo->query("SELECT room_id, COUNT(*) as count FROM reservations GROUP BY room_id");
    $rows = $stmt->fetchAll();
    $room_reservation_counts = [];
    foreach($rows as $row) {
        $room_reservation_counts[$row['room_id']] = $row['count'];
    }

    // Get agency performance
    $stmt = $pdo->query("SELECT ta.id, ta.user_id, ta.agency_name, COUNT(r.id) AS total_bookings, COALESCE(SUM(r.total_cost), 0) AS total_revenue, ta.commission_rate, COALESCE(SUM(CASE WHEN r.status IN ('confirmed', 'checked_in', 'checked_out') THEN IF(r.commission_earned > 0, r.commission_earned, r.total_cost * (ta.commission_rate / 100)) ELSE 0 END), 0) AS total_commission FROM travel_agencies ta LEFT JOIN users u ON ta.user_id = u.id LEFT JOIN reservations r ON r.user_id = u.id GROUP BY ta.id, ta.user_id ORDER BY total_bookings DESC");
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
    <link rel="icon" type="image/png" href="../pictures/icon.png">
    
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
            gap: 15px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-item a {
            text-decoration: none;
            color: var(--admin-secondary);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
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
            width: 100%;
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
            padding: 10px;
            border-bottom: 1px solid var(--admin-border);
            font-size: 0.85rem;
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

        .status-pending_verification {
            background: #fff3cd;
            color: #856404;
        }

        .status-waiting_for_arrival {
            background: #cce5ff;
            color: #004085;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-checked-in, .status-checked_in {
            background: #cce5ff;
            color: #004085;
        }
        .status-checked-out, .status-checked_out {
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
                    <li class="nav-item"><a href="dashboard.php?tab=agencies" class="<?php echo $current_tab === 'agencies' ? 'active' : ''; ?>">Agencies</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=addons" class="<?php echo $current_tab === 'addons' ? 'active' : ''; ?>">Add-ons & Categories</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <div class="user-info">

                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? 'admin'); ?>)
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
                    <div class="metric-label">Daily Reservations</div>
                    <p class="metric-value"><?php echo number_format($daily_reservations_count); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Monthly Reservations</div>
                    <p class="metric-value"><?php echo number_format($monthly_reservations_count); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Today's Revenue</div>
                    <p class="metric-value">₱<?php echo number_format($today_revenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Total Reservations</div>
                    <p class="metric-value"><?php echo number_format($total_reservations); ?></p>
                </div>
            </div>

            <div class="grid-2">
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Reservations by Room Type</h3>
                        <p>Total bookings across all time for each room category</p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room Name</th>
                                    <th>Category</th>
                                    <th>Total Reservations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $most_reserved_room = 'None';
                                    $highest_count = -1;
                                    
                                    foreach ($rooms as $room): 
                                        $count = isset($room_reservation_counts[$room['id']]) ? $room_reservation_counts[$room['id']] : 0;
                                        if ($count > $highest_count) {
                                            $highest_count = $count;
                                            $most_reserved_room = $room['name'];
                                        }
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($room['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($room['category']); ?></td>
                                        <td><span style="font-weight: bold; color: var(--admin-accent);"><?php echo $count; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="glassmorphism-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: linear-gradient(135deg, var(--admin-primary) 0%, #333 100%); color: white; padding: 40px;">
                    <div class="section-title" style="margin-bottom: 20px;">
                        <span style="color: var(--admin-accent); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px;">Most Popular Choice</span>
                        <h3 style="color: white; margin-top: 10px; font-size: 2rem;"><?php echo htmlspecialchars($most_reserved_room); ?></h3>
                        <p style="color: #ccc;">This room type has the highest number of overall reservations.</p>
                    </div>
                    <div style="font-size: 4rem; font-family: 'Playfair Display', serif; color: var(--admin-accent); line-height: 1;">
                        <?php echo $highest_count > 0 ? $highest_count : 0; ?>
                    </div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; color: #aaa;">Total Bookings</div>
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
                                <th>Source</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                    <td>₱<?php echo number_format(isset($res['total_cost']) ? $res['total_cost'] : 0, 2); ?></td>
                                    <td><span style="font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; background: var(--admin-border); color: var(--admin-secondary); text-transform: uppercase;"><?php echo htmlspecialchars(empty($res['source']) ? 'Guest' : $res['source']); ?></span></td>
                                    <td><span class="status-badge status-<?php echo strtolower($res['status']); ?>"><?php echo htmlspecialchars(str_replace('_', ' ', $res['status'])); ?></span></td>
                                    <td>
                                        <form method="POST" action="dashboard.php?tab=reservations" style="display: flex; gap: 5px; align-items: center;">
                                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($res['booking_ref']); ?>">
                                            <select name="new_status" style="padding: 4px; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--admin-border);">
                                                <option value="pending" <?php echo $res['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $res['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="checked_in" <?php echo $res['status'] === 'checked_in' ? 'selected' : ''; ?>>Checked-In</option>
                                                <option value="checked_out" <?php echo $res['status'] === 'checked_out' ? 'selected' : ''; ?>>Checked-Out</option>
                                                <option value="cancelled" <?php echo $res['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="button" class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem; background: var(--admin-secondary); color: white; border: none; border-radius: 4px; cursor: pointer;" onclick='openBookingDetails(<?php echo json_encode($res, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>VIEW DETAILS</button>
                                            <button type="submit" name="update_reservation" class="btn-primary" style="padding: 4px 8px; font-size: 0.8rem;">Save</button>
                                        </form>
                                    </td>
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

            <?php if ($success_message): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php 
                $total_rooms = 0;
                $maintenance_rooms = 0;
                $available_rooms_count = 0;
                $booked_rooms = 0;

                $today_date = date('Y-m-d');
                $all_sys_bookings = (function() use ($pdo) { 
                    $stmt = $pdo->query("SELECT * FROM reservations"); 
                    $res = $stmt->fetchAll(); 
                    foreach($res as &$b) {
                        if (isset($b['amenities']) && is_string($b['amenities'])) $b['amenities'] = json_decode($b['amenities'], true) ?: [];
                        if (isset($b['dining']) && is_string($b['dining'])) $b['dining'] = json_decode($b['dining'], true) ?: [];
                    }
                    return $res; 
                })();

                foreach ($rooms as $rm) {
                    $qty = $rm['quantity'] ?? 1;
                    $total_rooms += $qty;
                    if (($rm['status'] ?? 'Available') === 'Maintenance') {
                        $maintenance_rooms += $qty;
                    } else {
                        $is_booked_count = 0;
                        foreach ($all_sys_bookings as $b) {
                            if ((string)$b['room_id'] === (string)$rm['id'] && !in_array(strtolower(trim($b['status'])), ['checked_out', 'checked-out', 'cancelled'])) {
                                if ($today_date >= $b['check_in'] && $today_date < $b['check_out']) {
                                    $is_booked_count++;
                                }
                            }
                        }
                        $booked_rooms += $is_booked_count;
                        $available_rooms_count += max(0, $qty - $is_booked_count);
                    }
                }
            ?>
            <div class="metrics-grid" style="margin-bottom: 30px;">
                <div class="metric-card">
                    <div class="metric-label">Total Rooms</div>
                    <p class="metric-value"><?php echo $total_rooms; ?></p>
                </div>
                <div class="metric-card" style="border-left: 4px solid #2ecc71;">
                    <div class="metric-label">Available</div>
                    <p class="metric-value"><?php echo $available_rooms_count; ?></p>
                </div>
                <div class="metric-card" style="border-left: 4px solid #3498db;">
                    <div class="metric-label">Booked Today</div>
                    <p class="metric-value"><?php echo $booked_rooms; ?></p>
                </div>
                <div class="metric-card" style="border-left: 4px solid #e74c3c;">
                    <div class="metric-label">Under Maintenance</div>
                    <p class="metric-value"><?php echo $maintenance_rooms; ?></p>
                </div>
            </div>

            <div class="grid-2">
                <div class="glassmorphism-card">
                    <?php 
                        $is_edit_room = isset($_GET['action']) && $_GET['action'] === 'edit_room' && isset($_GET['id']);
                        $edit_room = null;
                        if ($is_edit_room) {
                            $edit_id = (int)$_GET['id'];
                            foreach ($rooms as $rm) {
                                if ($rm['id'] === $edit_id) {
                                    $edit_room = $rm;
                                    break;
                                }
                            }
                        }
                    ?>
                    <div class="section-title">
                        <h3 style="font-family: var(--font-header);"><?php echo $edit_room ? 'Edit Room' : 'Add New Room'; ?></h3>
                        <p style="font-size: 0.9rem; color: var(--admin-secondary); margin-top: 5px;"><?php echo $edit_room ? 'Update room details.' : 'Configure a new room to be available for booking.'; ?></p>
                    </div>
                    <form method="POST" action="dashboard.php?tab=rooms">
                        <?php if ($edit_room): ?>
                            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($edit_room['id']); ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="name">Room Name</label>
                            <input type="text" id="name" name="name" required placeholder="e.g. Ocean View Suite" value="<?php echo htmlspecialchars($edit_room['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <?php
                                    $tmp_addons = (function() use ($pdo) { $stmt = $pdo->query("SELECT * FROM addons"); return $stmt->fetchAll(); })();
                                    
                                    $room_categories = ['Standard Room', 'Deluxe Room', 'Grand Suite'];
                                    foreach ($tmp_addons as $addon) {
                                        if ($addon['category'] === 'room_category' && !in_array($addon['name'], $room_categories)) {
                                            $room_categories[] = $addon['name'];
                                        }
                                    }
                                    
                                    foreach ($room_categories as $cat) {
                                        $selected = (isset($edit_room['category']) && $edit_room['category'] === $cat) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price per Night (₱)</label>
                            <input type="number" id="price" name="price" required step="0.01" min="0" placeholder="0.00" value="<?php echo htmlspecialchars($edit_room['price'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="quantity">Number of Rooms (Quantity)</label>
                            <input type="number" id="quantity" name="quantity" required min="1" value="<?php echo htmlspecialchars($edit_room['quantity'] ?? '1'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Room Status</label>
                            <select id="status" name="status">
                                <option value="Available" <?php echo ($edit_room['status'] ?? 'Available') === 'Available' ? 'selected' : ''; ?>>Available</option>
                                <option value="Maintenance" <?php echo ($edit_room['status'] ?? 'Available') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required rows="3" placeholder="Describe the room features..." style="width: 100%; padding: 12px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit; font-size: 0.9rem; resize: vertical;"><?php echo htmlspecialchars($edit_room['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Comforts / Amenities</label>
                            <div style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid var(--admin-border); border-radius: 8px; background: var(--admin-bg-light);">
                                <?php 
                                    $all_addons = (function() use ($pdo) { $stmt = $pdo->query("SELECT * FROM addons"); return $stmt->fetchAll(); })();
                                    
                                    $current_amenities = [];
                                    if ($edit_room && !empty($edit_room['amenities'])) {
                                        $current_amenities = is_array($edit_room['amenities']) ? $edit_room['amenities'] : array_map('trim', explode(',', $edit_room['amenities']));
                                    }
                                    
                                    foreach ($all_addons as $addon):
                                        if ($addon['category'] !== 'room_category'):
                                            $is_checked = in_array($addon['name'], $current_amenities) ? 'checked' : '';
                                ?>
                                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                        <input type="checkbox" id="room_amenity_<?php echo $addon['id']; ?>" name="amenities[]" value="<?php echo htmlspecialchars($addon['name']); ?>" <?php echo $is_checked; ?> style="width: auto; margin-right: 10px; cursor: pointer;">
                                        <label for="room_amenity_<?php echo $addon['id']; ?>" style="cursor: pointer; margin: 0; font-weight: normal; text-transform: none;"><?php echo htmlspecialchars($addon['name']); ?></label>
                                    </div>
                                <?php 
                                        endif;
                                    endforeach; 
                                ?>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--admin-secondary); margin-top: 5px;">Select multiple amenities. Add new amenities in the Add-ons & Categories tab.</p>
                        </div>
                        <?php if ($edit_room): ?>
                            <div class="btn-center" style="display: flex; gap: 10px;">
                                <button type="submit" name="edit_room" class="btn-primary" style="flex: 1;">Update Room</button>
                                <a href="dashboard.php?tab=rooms" class="btn-secondary" style="flex: 1; text-align: center; line-height: 2.5;">Cancel</a>
                            </div>
                        <?php else: ?>
                            <div class="btn-center">
                                <button type="submit" name="create_room" class="btn-primary" style="width: 100%;">Add Room</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="glassmorphism-card">
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Room Name</th>
                                    <th>Category</th>
                                    <th>Price/Night</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $room): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($room['id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($room['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($room['category']); ?></td>
                                        <td>₱<?php echo number_format($room['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($room['quantity'] ?? 1); ?></td>
                                        <td><span class="status-badge" style="background: <?php echo ($room['status'] ?? 'Available') === 'Available' ? '#2ecc71' : '#e74c3c'; ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($room['status'] ?? 'Available'); ?></span></td>
                                        <td>
                                            <a href="dashboard.php?tab=rooms&action=edit_room&id=<?php echo $room['id']; ?>" class="action-btn" style="background: var(--admin-primary); color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; text-decoration: none; display: inline-block; margin-bottom: 4px;">Edit</a>
                                            <a href="dashboard.php?tab=rooms&action=delete_room&id=<?php echo $room['id']; ?>" class="action-btn" style="background: #e74c3c; color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; text-decoration: none; display: inline-block;" onclick="return confirm('Are you sure you want to delete this room?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                    <?php 
                        $is_edit = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
                        $edit_user = null;
                        if ($is_edit) {
                            $edit_id = (int)$_GET['id'];

                            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->execute([$edit_id]);
                            $edit_user = $stmt->fetch();
                        
                        }
                    ?>
                    <div class="section-title">
                        <h3><?php echo $edit_user ? 'Edit User' : 'Create New User'; ?></h3>
                        <p><?php echo $edit_user ? 'Update user details and role' : 'Fill in the details to add a new user'; ?></p>
                    </div>
                    <form method="POST" action="dashboard.php?tab=users">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_user['id']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required placeholder="Enter full name" value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>">
                        </div>
                        <?php if (!$edit_user): ?>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required placeholder="email@example.com">
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="password"><?php echo $edit_user ? 'New Password (leave blank to keep current)' : 'Password'; ?></label>
                            <input type="password" id="password" name="password" <?php echo $edit_user ? '' : 'required'; ?> placeholder="<?php echo $edit_user ? 'Enter new password' : 'Create a password'; ?>">
                        </div>
                        <div class="form-group" id="role-group">
                            <style>
                                /* Zero-JS form toggle */
                                #agency_fields { display: none; }
                                #role-group:has(select option[value="agency"]:checked) ~ #agency_fields { display: block; }
                            </style>
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="staff" <?php echo ($edit_user && $edit_user['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                <option value="agency" <?php echo ($edit_user && $edit_user['role'] === 'agency') ? 'selected' : ''; ?>>Travel Agency</option>
                                <?php if ($edit_user && $edit_user['role'] === 'admin'): ?>
                                    <option value="admin" selected>Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div id="agency_fields">
                            <div class="form-group">
                                <label for="agency_name">Agency Name</label>
                                <input type="text" id="agency_name" name="agency_name" placeholder="Enter agency name">
                            </div>
                            <div class="form-group">
                                <label for="commission_rate">Commission Rate (%)</label>
                                <input type="number" id="commission_rate" name="commission_rate" step="0.01" min="0" max="100" value="10.0">
                            </div>
                        </div>

                        <?php if ($edit_user): ?>
                            <div class="btn-center" style="display: flex; gap: 10px;">
                                <button type="submit" name="edit_user" class="btn-primary" style="flex: 1;">Update User</button>
                                <a href="dashboard.php?tab=users" class="btn-secondary" style="flex: 1; text-align: center; line-height: 2.5;">Cancel</a>
                            </div>
                        <?php else: ?>
                            <div class="btn-center">
                                <button type="submit" name="create_user" class="btn-primary" style="width: 100%;">Create User</button>
                            </div>
                        <?php endif; ?>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo htmlspecialchars($user['agency_name'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <form method="POST" action="dashboard.php?tab=users" style="display: flex; gap: 5px; margin: 0;">
                                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                                    <a href="dashboard.php?tab=users&action=edit&id=<?php echo $user['id']; ?>" class="btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: #f39c12; border: none; text-decoration: none; border-radius: 4px; color: white;">Edit</a>
                                                    <button type="submit" name="delete_user" class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem; background-color: #ff4d4d; color: white; border: none;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: #888;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



        <?php elseif ($current_tab === 'agencies'): ?>

            <?php
            $view_agency_id = isset($_GET['view_agency_id']) ? (int)$_GET['view_agency_id'] : null;
            $view_agency = null;
            if ($view_agency_id) {
                foreach ($agency_performance as $ag) {
                    if ($ag['id'] === $view_agency_id) {
                        $view_agency = $ag;
                        break;
                    }
                }
            }
            ?>

            <?php if ($view_agency): ?>
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <span class="breadcrumb"><a href="dashboard.php?tab=agencies" style="color: var(--admin-secondary); text-decoration: none;">Partners &rsaquo; Agencies</a> &rsaquo; <?php echo htmlspecialchars($view_agency['agency_name']); ?></span>
                        <h1 class="page-title"><?php echo htmlspecialchars($view_agency['agency_name']); ?> Performance</h1>
                        <p class="page-subtitle">Detailed view of agency bookings and commission earnings.</p>
                    </div>
                    <div>
                        <a href="dashboard.php?tab=agencies" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; border-radius: 6px; border: 1px solid var(--admin-border); display: inline-block;">Back to Agencies</a>
                    </div>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-label">Total Bookings</div>
                        <p class="metric-value"><?php echo number_format($view_agency['total_bookings']); ?></p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Total Revenue</div>
                        <p class="metric-value">₱<?php echo number_format($view_agency['total_revenue'], 2); ?></p>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Earned Commission</div>
                        <p class="metric-value" style="color: #27ae60;">₱<?php echo number_format($view_agency['total_commission'], 2); ?></p>
                    </div>
                    <div class="metric-card" style="display: flex; flex-direction: column; justify-content: center;">
                        <div class="metric-label">Commission Rate</div>
                        <form method="POST" action="dashboard.php?tab=agencies&view_agency_id=<?php echo $view_agency['id']; ?>" style="display: flex; gap: 5px; align-items: center; justify-content: center; margin-top: 10px;">
                            <input type="hidden" name="agency_id" value="<?php echo htmlspecialchars($view_agency['id']); ?>">
                            <input type="number" name="commission_rate" value="<?php echo htmlspecialchars($view_agency['commission_rate']); ?>" step="0.1" min="0" max="100" style="padding: 8px; width: 80px; font-size: 1.2rem; border-radius: 4px; border: 1px solid var(--admin-border); text-align: center; background: rgba(255,255,255,0.8); font-family: 'Playfair Display', serif;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: var(--admin-primary);">%</span>
                            <button type="submit" name="update_commission" class="btn-primary" style="padding: 8px 12px; font-size: 0.8rem; border-radius: 4px;">Save</button>
                        </form>
                    </div>
                </div>

                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Agency Bookings</h3>
                        <p>All reservations made by <?php echo htmlspecialchars($view_agency['agency_name']); ?></p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking Ref</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Stay Dates</th>
                                    <th>Total Cost</th>
                                    <th>Commission Earned</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $agency_user_id = $view_agency['user_id'] ?? null;
                                    
                                    $agency_bookings = [];
                                    foreach ($reservations as $res) {
                                        if ($res['user_id'] == $agency_user_id) {
                                            $agency_bookings[] = $res;
                                        }
                                    }
                                ?>
                                <?php if (empty($agency_bookings)): ?>
                                    <tr><td colspan="7" style="text-align: center; padding: 20px;">No bookings found for this agency.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($agency_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['check_in'] . ' - ' . $booking['check_out']); ?></td>
                                            <td>₱<?php echo number_format(isset($booking['total_cost']) ? $booking['total_cost'] : 0, 2); ?></td>
                                            <td><span style="color: #27ae60; font-weight: bold;">₱<?php 
                                                $comm = (isset($booking['commission_earned']) && $booking['commission_earned'] > 0) ? $booking['commission_earned'] : ((isset($booking['total_cost']) ? $booking['total_cost'] : 0) * ($view_agency['commission_rate'] / 100));
                                                echo number_format($comm, 2);
                                            ?></span></td>
                                            <td><span class="status-badge status-<?php echo strtolower($booking['status']); ?>"><?php echo htmlspecialchars(str_replace('_', ' ', $booking['status'])); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>

                <div class="page-header">
                    <span class="breadcrumb">Partners &rsaquo; Agencies</span>
                    <h1 class="page-title">Agency Performance</h1>
                    <p class="page-subtitle">Track travel agency performance and commission earnings.</p>
                </div>

                <div class="grid-3">
                    <?php foreach ($agency_performance as $agency): ?>
                        <div class="agency-card" style="display: flex; flex-direction: column;">
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
                            <div class="agency-stats" style="border-top: 1px solid var(--admin-border); padding-top: 10px; margin-top: 10px; margin-bottom: 20px;">
                                <span>Total Commission:</span>
                                <span class="agency-stat-value" style="color: #27ae60;">₱<?php echo number_format($agency['total_commission'], 2); ?></span>
                            </div>
                            
                            <div style="margin-top: auto; padding-top: 15px; border-top: 1px dashed var(--admin-border); text-align: center;">
                                <a href="dashboard.php?tab=agencies&view_agency_id=<?php echo $agency['id']; ?>" class="btn-primary" style="display: block; text-decoration: none; padding: 10px;">View Full Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        <?php elseif ($current_tab === 'addons'): ?>

            <div class="page-header">
                <span class="breadcrumb">Settings &rsaquo; Add-ons & Categories</span>
                <h1 class="page-title">Global Add-ons & Room Categories</h1>
                <p class="page-subtitle">Manage room categories, global amenities, and dining options prices.</p>
            </div>

            <div class="grid-2">
                <div class="glassmorphism-card">
                    <?php 
                        $stmt = $pdo->query("SELECT * FROM addons ORDER BY category, id");
                        $addons_data = $stmt->fetchAll();
                        
                        $is_edit_addon = isset($_GET['action']) && $_GET['action'] === 'edit_addon' && isset($_GET['id']);
                        $edit_addon = null;
                        if ($is_edit_addon) {
                            $edit_id = (int)$_GET['id'];
                            foreach ($addons_data as $ad) {
                                if ($ad['id'] === $edit_id) {
                                    $edit_addon = $ad;
                                    break;
                                }
                            }
                        }
                    ?>
                    <div class="section-title">
                        <h3 style="font-family: var(--font-header);"><?php echo $edit_addon ? 'Edit Item' : 'Add New Item'; ?></h3>
                        <p style="font-size: 0.9rem; color: var(--admin-secondary); margin-top: 5px;"><?php echo $edit_addon ? 'Update the details globally.' : 'Create a new room category, amenity, or dining option.'; ?></p>
                    </div>
                    <form method="POST" action="dashboard.php?tab=addons">
                        <?php if ($edit_addon): ?>
                            <input type="hidden" name="addon_id" value="<?php echo htmlspecialchars($edit_addon['id']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="addon_name">Name</label>
                            <input type="text" id="addon_name" name="addon_name" required placeholder="e.g. Airport Transfer" value="<?php echo htmlspecialchars($edit_addon['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="addon_category">Category</label>
                            <select id="addon_category" name="addon_category" required>
                                <option value="room_category" <?php echo ($edit_addon && $edit_addon['category'] === 'room_category') ? 'selected' : ''; ?>>Room Category</option>
                                <option value="amenity" <?php echo ($edit_addon && $edit_addon['category'] === 'amenity') ? 'selected' : ''; ?>>Amenity / Comfort</option>
                                <option value="dining" <?php echo ($edit_addon && $edit_addon['category'] === 'dining') ? 'selected' : ''; ?>>Dining</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="addon_price">Price (₱) <small style="font-weight: normal;">(0 if free)</small></label>
                            <input type="number" id="addon_price" name="addon_price" required step="0.01" min="0" value="<?php echo htmlspecialchars($edit_addon['price'] ?? 0); ?>">
                        </div>
                        
                        <?php if ($edit_addon): ?>
                            <div class="btn-center" style="display: flex; gap: 10px;">
                                <button type="submit" name="update_addon" class="btn-primary" style="flex: 1;">Save Changes</button>
                                <a href="dashboard.php?tab=addons" class="btn-secondary" style="flex: 1; text-align: center; line-height: 2.5;">Cancel</a>
                            </div>
                        <?php else: ?>
                            <div class="btn-center">
                                <button type="submit" name="create_addon" class="btn-primary" style="width: 100%;">Add Item</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="glassmorphism-card">
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($addons_data as $addon): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($addon['name']); ?></strong></td>
                                        <td style="text-transform: capitalize;"><?php echo htmlspecialchars($addon['category']); ?></td>
                                        <td>₱<?php echo number_format($addon['price'], 2); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="dashboard.php?tab=addons&action=edit_addon&id=<?php echo $addon['id']; ?>" class="action-btn" style="background: var(--admin-primary); color: white; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; text-decoration: none;">Edit</a>
                                                <form method="POST" action="dashboard.php?tab=addons" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this add-on?');">
                                                    <input type="hidden" name="addon_id" value="<?php echo $addon['id']; ?>">
                                                    <button type="submit" name="delete_addon" class="action-btn" style="background: #e74c3c; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>
    
    <script>
    function openBookingDetails(booking) {
        document.getElementById('modal-guest-name').textContent = booking.first_name + ' ' + booking.last_name;
        document.getElementById('modal-email').textContent = booking.email;
        document.getElementById('modal-phone').textContent = booking.phone;
        document.getElementById('modal-dates').textContent = booking.check_in + ' to ' + booking.check_out;
        document.getElementById('modal-room').textContent = booking.room_name || ('Room ' + booking.room_id);
        document.getElementById('modal-guests').textContent = booking.guests_adults + ' Adults, ' + booking.guests_children + ' Children';
        
        let requests = booking.special_requests ? booking.special_requests.trim() : 'None';
        if (requests === '') requests = 'None';
        document.getElementById('modal-requests').textContent = requests;
        
        document.getElementById('booking-details-modal').style.display = 'flex';
    }

    function closeBookingDetails() {
        document.getElementById('booking-details-modal').style.display = 'none';
    }
    </script>

    <!-- Booking Details Modal -->
    <div id="booking-details-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:30px; border-radius:12px; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; position:relative; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
            <button onclick="closeBookingDetails()" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;">&times;</button>
            <h2 style="font-family:'Playfair Display', serif; color:var(--admin-primary); margin-bottom:20px;">Reservation Details</h2>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <h4 style="font-size:0.75rem; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">Guest Info</h4>
                    <p style="margin-bottom:5px;"><strong id="modal-guest-name" style="font-weight:600; font-size:1.1rem; color:#333;"></strong></p>
                    <p style="font-size:0.9rem; color:#555; margin-bottom:2px;" id="modal-email"></p>
                    <p style="font-size:0.9rem; color:#555;" id="modal-phone"></p>
                </div>
                <div>
                    <h4 style="font-size:0.75rem; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">Stay Info</h4>
                    <p style="margin-bottom:5px; font-weight:600; color:#333;" id="modal-room"></p>
                    <p style="font-size:0.9rem; color:#555; margin-bottom:2px;" id="modal-dates"></p>
                    <p style="font-size:0.9rem; color:#555;" id="modal-guests"></p>
                </div>
            </div>
            
            <div style="margin-bottom:20px; padding:15px; background:#f9f9f9; border-radius:8px; border:1px solid #eee;">
                <h4 style="font-size:0.75rem; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;">Special Requests / Notes</h4>
                <p style="font-size:0.95rem; color:#333; line-height:1.5;" id="modal-requests"></p>
            </div>
        </div>
    </div>

</body>
</html>
