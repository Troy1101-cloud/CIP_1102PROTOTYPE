<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

check_auth('staff');

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// Handle booking status updates
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = trim($_POST['booking_id']);
    $status = str_replace('-', '_', strtolower($_POST['status'])); // normalize to checked_in
    $timestamp = date('Y-m-d H:i:s');
    
    if ($status === 'checked_in') {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ?, checked_in_at = ? WHERE id = ? OR booking_ref = ?");
        $stmt->execute([$status, $timestamp, $booking_id, $booking_id]);
    } elseif ($status === 'checked_out') {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ?, checked_out_at = ? WHERE id = ? OR booking_ref = ?");
        $stmt->execute([$status, $timestamp, $booking_id, $booking_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ? OR booking_ref = ?");
        $stmt->execute([$status, $booking_id, $booking_id]);
    }
    if (function_exists('add_audit_log')) add_audit_log('booking_updated', "Updated booking ID $booking_id status to $status");
}

// Handle create reservation
if (isset($_POST['create_reservation'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $room_id = (int)$_POST['room_id'];
    $adults = (int)$_POST['guests_adults'];
    $children = (int)$_POST['guests_children'];
    $booking_ref = 'BK' . strtoupper(substr(uniqid(), -8));
    
    // Get room price
    $room_price = 0;
    $room_name = 'Unknown';
    $stmt = $pdo->prepare("SELECT name, price FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $row = $stmt->fetch();
    if ($row) {
        $room_price = $row['price'];
        $room_name = $row['name'];
    }
    
    $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
    $total_cost = $room_price * max(1, $days);
    
    if ($check_out <= $check_in) {
        $error_message = "Check-out date must be after check-in date.";
    } elseif (!is_room_available($pdo, $room_id, $check_in, $check_out)) {
        $error_message = "The selected room is not available for these dates.";
    } else {
        $special_requests = $_POST['special_requests'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO reservations (first_name, last_name, email, phone, check_in, check_out, room_id, guests_adults, guests_children, special_requests, total_cost, status, source, booking_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'staff', ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone, $check_in, $check_out, $room_id, $adults, $children, $special_requests, $total_cost, $booking_ref]);
        if (function_exists('add_audit_log')) add_audit_log('booking_created', "Staff created booking $booking_ref");
        $success_message = "Reservation created successfully! Reference: $booking_ref";
    }
}

// Get Data for Overview
$today = date('Y-m-d');
$current_month = date('Y-m');

    $stmt = $pdo->query("SELECT COALESCE(SUM(total_cost), 0) AS total FROM reservations WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'");
    $monthly_revenue = $stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_in = ? ORDER BY r.id DESC");
    $stmt->execute([$today]);
    $arrivals = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_out = ? ORDER BY r.id DESC");
    $stmt->execute([$today]);
    $departures = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_out = ? AND r.status NOT IN ('checked-out', 'checked_out') ORDER BY r.id DESC");
    $stmt->execute([$today]);
    $pending_balances = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id ORDER BY r.created_at DESC");
    $all_reservations = $stmt->fetchAll();

// Split into active and history
$active_reservations = [];
$history_reservations = [];

foreach ($all_reservations as $b) {
    if (in_array($b['status'], ['checked_out', 'checked-out', 'cancelled'])) {
        $history_reservations[] = $b;
    } else {
        $active_reservations[] = $b;
    }
}

// Pagination logic
$total_active = count($active_reservations);
$total_active_pages = ceil($total_active / $items_per_page);
$active_paginated = array_slice($active_reservations, $offset, $items_per_page);

$total_history = count($history_reservations);
$total_history_pages = ceil($total_history / $items_per_page);
$history_paginated = array_slice($history_reservations, $offset, $items_per_page);

// For messages tab
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$all_messages = $stmt->fetchAll();
$total_messages = count($all_messages);
$total_messages_pages = ceil($total_messages / $items_per_page);
$messages_paginated = array_slice($all_messages, $offset, $items_per_page);

// For create form
$available_rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Ralmitrokij Hotel</title>
    <link rel="icon" type="image/png" href="../pictures/icon.png">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        :root {
            --admin-primary: #1a1a1a;
            --admin-accent: #c9a84c;
            --admin-secondary: #666;
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
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.4);
            padding: 20px 0;
            position: sticky;
            top:0;
            z-index:100;
        }
        .header-inner {
            max-width:1400px;
            margin:0 auto;
            padding:0 30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .admin-logo {
            font-family: 'Playfair Display', serif;
            font-size:1.2rem;
            font-weight:700;
            color: var(--admin-primary);
            text-decoration:none;
        }
        .admin-logo span {
            font-family: 'Montserrat', sans-serif;
            font-size:0.7rem;
            font-weight:400;
            letter-spacing:2px;
            color: var(--admin-secondary);
            display:block;
        }
        .nav-menu {
            display:flex;
            gap:30px;
            list-style:none;
            margin:0;
            padding:0;
        }
        .nav-item a {
            text-decoration:none;
            color: var(--admin-secondary);
            font-size:0.8rem;
            font-weight:600;
            letter-spacing:1px;
            text-transform:uppercase;
            padding:8px 0;
            border-bottom:2px solid transparent;
            transition:all 0.3s;
        }
        .nav-item a:hover,
        .nav-item a.active {
            color: var(--admin-primary);
            border-bottom-color: var(--admin-accent);
        }
        .header-actions {
            display:flex;
            align-items:center;
            gap:20px;
        }
        .user-info {
            font-size:0.85rem;
            color: var(--admin-secondary);
        }
        .logout-btn {
            text-decoration:none;
            color: var(--admin-accent);
            font-size:0.8rem;
            font-weight:600;
            letter-spacing:1px;
            text-transform:uppercase;
            border:1px solid var(--admin-accent);
            padding:10px 20px;
            border-radius:4px;
            transition:all 0.3s;
        }
        .logout-btn:hover {
            background: var(--admin-accent);
            color:white;
        }
        .dashboard-container {
            max-width:1400px;
            margin:0 auto;
            padding:40px 30px;
        }
        .page-header {
            margin-bottom:40px;
        }
        .breadcrumb {
            font-size:0.75rem;
            color: var(--admin-secondary);
            text-transform:uppercase;
            letter-spacing:1px;
            margin-bottom:10px;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size:2rem;
            margin:0 0 10px 0;
            color: var(--admin-primary);
        }
        .page-subtitle {
            color: var(--admin-secondary);
            margin:0;
            font-size:0.95rem;
        }
        .metrics-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
            gap:25px;
            margin-bottom:40px;
        }
        .metric-card {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            border:1px solid rgba(255,255,255,0.4);
            border-radius:12px;
            padding:25px;
            text-align:center;
            box-shadow:0 4px 16px rgba(0,0,0,0.06);
        }
        .metric-label {
            font-size:0.75rem;
            color: var(--admin-secondary);
            text-transform:uppercase;
            letter-spacing:2px;
            margin-bottom:10px;
        }
        .metric-value {
            font-family: 'Playfair Display', serif;
            font-size:2.5rem;
            color: var(--admin-accent);
            margin:0;
        }
        .section-title {
            margin-bottom:25px;
        }
        .section-title h3 {
            font-family: 'Playfair Display', serif;
            font-size:1.5rem;
            margin:0 0 8px 0;
            color: var(--admin-primary);
        }
        .section-title p {
            margin:0;
            color: var(--admin-secondary);
            font-size:0.85rem;
        }
        .data-table-container {
            width: 100%;
        }
        .data-table {
            width:100%;
            border-collapse:collapse;
            background:white;
            border-radius:8px;
            overflow:hidden;
            box-shadow:0 2px 8px rgba(0,0,0,0.05);
        }
        .data-table thead {
            background: var(--admin-bg-light);
        }
        .data-table th {
            padding:15px;
            text-align:left;
            font-size:0.75rem;
            text-transform:uppercase;
            letter-spacing:1px;
            color: var(--admin-secondary);
            border-bottom:1px solid var(--admin-border);
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid var(--admin-border);
            font-size: 0.85rem;
            color: var(--admin-primary);
        }
        .status-badge {
            padding:6px 12px;
            border-radius:20px;
            font-size:0.75rem;
            font-weight:600;
            text-transform:uppercase;
            letter-spacing:1px;
            display:inline-block;
        }
        .status-pending { background:#fff3cd; color:#856404; }
        .status-confirmed { background:#d4edda; color:#155724; }
        .status-checked-in, .status-checked_in { background:#cce5ff; color:#004085; }
        .status-checked-out, .status-checked_out { background:#e2e3e5; color:#383d41; }
        .status-cancelled { background:#f8d7da; color:#721c24; }
        .status-waiting_for_arrival { background:#cce5ff; color:#004085; }
        .status-pending_verification { background:#fff3cd; color:#856404; }
        .btn-primary {
            background: var(--admin-accent);
            color:white;
            border:none;
            padding:10px 20px;
            border-radius:8px;
            font-size:0.85rem;
            font-weight:600;
            letter-spacing:1px;
            text-transform:uppercase;
            cursor:pointer;
            transition:all 0.3s;
        }
        .btn-secondary {
            background: var(--admin-secondary);
            color:white;
            border:none;
            padding:10px 20px;
            border-radius:8px;
            font-size:0.85rem;
            font-weight:600;
            letter-spacing:1px;
            text-transform:uppercase;
            cursor:pointer;
            transition:all 0.3s;
        }
        .btn-primary:hover {
            background:#b59444;
        }
        .btn-center {
            display:flex;
            justify-content:center;
        }
        .grid-2 {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:30px;
        }
        .alert-warning, .alert-success {
            padding:15px 20px;
            border-radius:8px;
            margin-bottom:30px;
        }
        .alert-warning {
            background: rgba(255,193,7,0.1);
            color:#856404;
            border-left:4px solid #ffc107;
        }
        .alert-success {
            background: rgba(40,167,69,0.1);
            color:#155724;
            border-left:4px solid #28a745;
        }
        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap;
        }
        select, input, textarea {
            padding:8px 12px;
            border:1px solid var(--admin-border);
            border-radius:8px;
            background:white;
            font-size:0.85rem;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--admin-secondary);
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            box-sizing: border-box;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding: 20px 0;
        }
        .page-link {
            padding: 8px 16px;
            background: white;
            border: 1px solid var(--admin-border);
            border-radius: 4px;
            text-decoration: none;
            color: var(--admin-primary);
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .page-link:hover, .page-link.active {
            background: var(--admin-accent);
            color: white;
            border-color: var(--admin-accent);
        }
        @media (max-width: 992px) {
            .grid-2 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <div class="header-inner">
            <a href="dashboard.php" class="admin-logo">
                RALMITROKIJ <span>STAFF DASHBOARD</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="dashboard.php?tab=overview" class="<?php echo $current_tab === 'overview' ? 'active' : ''; ?>">Overview</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=reservations" class="<?php echo $current_tab === 'reservations' ? 'active' : ''; ?>">Active Bookings</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=history" class="<?php echo $current_tab === 'history' ? 'active' : ''; ?>">History</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=messages" class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">Messages</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Staff'); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</div>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        <?php if (isset($success_message)): ?>
            <div class="alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($current_tab === 'overview'): ?>
            <div class="page-header">
                <span class="breadcrumb">Daily Operations &rsaquo; Overview</span>
                <h1 class="page-title">Today's Operations</h1>
                <p class="page-subtitle">Manage arrivals, departures, and booking status</p>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Monthly Revenue</div>
                    <p class="metric-value">₱<?php echo number_format($monthly_revenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Today's Arrivals</div>
                    <p class="metric-value"><?php echo count($arrivals); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Today's Departures</div>
                    <p class="metric-value"><?php echo count($departures); ?></p>
                </div>
            </div>



            <div class="grid-2">
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Today's Arrivals</h3>
                        <p>Guests checking in today</p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrivals as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($booking['status']))); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Today's Departures</h3>
                        <p>Guests checking out today</p>
                    </div>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departures as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td>₱<?php echo number_format(isset($booking['total_cost']) ? $booking['total_cost'] : 0, 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($booking['status']))); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($current_tab === 'reservations'): ?>
            <div class="page-header">
                <span class="breadcrumb">Daily Operations &rsaquo; Reservations</span>
                <h1 class="page-title">Manage Active Bookings</h1>
                <p class="page-subtitle">Update booking statuses or create a new walk-in reservation.</p>
            </div>

            <div class="grid-2" style="grid-template-columns: 1fr 2.5fr;">
                <div class="glassmorphism-card">
                    <div class="section-title">
                        <h3>Create Reservation</h3>
                        <p>Add a new booking for a guest</p>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" required placeholder="+63 9XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label>Check In</label>
                            <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Check Out</label>
                            <input type="date" name="check_out" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <select name="room_id" required>
                                <?php foreach ($available_rooms as $r): ?>
                                    <?php
                                        $qty = $r['quantity'] ?? 1;
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE room_id = ? AND status NOT IN ('cancelled', 'checked_out', 'checked-out') AND check_in <= ? AND check_out > ?");
                                        $stmt->execute([$r['id'], date('Y-m-d'), date('Y-m-d')]);
                                        $booked_today = $stmt->fetch()['count'];
                                        $avail = max(0, $qty - $booked_today);
                                    ?>
                                    <option value="<?php echo $r['id']; ?>" <?php echo $avail <= 0 ? 'disabled' : ''; ?>><?php echo htmlspecialchars($r['name']); ?> (₱<?php echo number_format($r['price'], 2); ?>) - <?php echo $avail; ?>/<?php echo $qty; ?> Available Now</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid-2" style="gap:15px; margin-bottom: 15px;">
                            <div class="form-group" style="margin:0;">
                                <label>Adults</label>
                                <input type="number" name="guests_adults" min="1" value="1" required>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label>Children</label>
                                <input type="number" name="guests_children" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Special Requests (Optional)</label>
                            <textarea name="special_requests" rows="3" placeholder="Any special requests (e.g., extra bed, early check-in)..."></textarea>
                        </div>
                        <button type="submit" name="create_reservation" class="btn-primary" style="width: 100%;">Create Booking</button>
                    </form>
                </div>

                <div class="glassmorphism-card">
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Dates</th>
                                    <th>Total</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_paginated as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_ref'] ?? $booking['id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M j', strtotime($booking['check_in'])) . ' - ' . date('M j', strtotime($booking['check_out']))); ?></td>
                                        <td>₱<?php echo number_format(isset($booking['total_cost']) ? $booking['total_cost'] : 0, 2); ?></td>
                                        <td><span style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; background: #eee; color: #555; font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars(empty($booking['source']) ? 'guest' : $booking['source']); ?></span></td>
                                        <td><span class="status-badge status-<?php echo empty($booking['status']) ? 'pending_verification' : $booking['status']; ?>"><?php echo empty($booking['status']) ? 'PENDING VERIFICATION' : htmlspecialchars(str_replace('_', ' ', strtoupper($booking['status']))); ?></span></td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="booking_id" value="<?php echo isset($booking['booking_ref']) ? $booking['booking_ref'] : $booking['id']; ?>">
                                                <select name="status">
                                                    <option value="pending" <?php echo (empty($booking['status']) || $booking['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="checked_in" <?php echo in_array($booking['status'], ['checked_in', 'checked-in']) ? 'selected' : ''; ?>>Checked In</option>
                                                    <option value="checked_out" <?php echo in_array($booking['status'], ['checked_out', 'checked-out']) ? 'selected' : ''; ?>>Checked Out</option>
                                                    <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.75rem;" onclick='openBookingDetails(<?php echo json_encode($booking, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>VIEW DETAILS</button>
                                                <button type="submit" name="update_status" class="btn-secondary" style="padding: 6px 12px; font-size: 0.75rem;">SAVE</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_active_pages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_active_pages; $i++): ?>
                                <a href="dashboard.php?tab=reservations&page=<?php echo $i; ?>" class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($current_tab === 'history'): ?>
            <div class="page-header">
                <span class="breadcrumb">Daily Operations &rsaquo; History</span>
                <h1 class="page-title">Booking History</h1>
                <p class="page-subtitle">View past reservations that have been checked out or cancelled.</p>
            </div>
            
            <div class="glassmorphism-card">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Dates</th>
                                <th>Total</th>
                                <th>Check Out Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history_paginated as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_ref'] ?? $booking['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M j', strtotime($booking['check_in'])) . ' - ' . date('M j', strtotime($booking['check_out']))); ?></td>
                                    <td>₱<?php echo number_format(isset($booking['total_cost']) ? $booking['total_cost'] : 0, 2); ?></td>
                                    <td><?php echo htmlspecialchars($booking['checked_out_at'] ?? '-'); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($booking['status']); ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($booking['status']))); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_history_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_history_pages; $i++): ?>
                            <a href="dashboard.php?tab=history&page=<?php echo $i; ?>" class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_tab === 'messages'): ?>
            <div class="page-header">
                <span class="breadcrumb">Daily Operations &rsaquo; Messages</span>
                <h1 class="page-title">Contact Messages</h1>
                <p class="page-subtitle">Inquiries and feedback from guests via the website.</p>
            </div>
            
            <div class="glassmorphism-card">
                <?php if (count($messages_paginated) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <?php foreach ($messages_paginated as $msg): ?>
                            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid var(--admin-border); transition: transform 0.2s, box-shadow 0.2s;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                    <div>
                                        <h3 style="margin: 0 0 5px 0; font-family: 'Playfair Display', serif; color: var(--admin-primary); font-size: 1.25rem;">
                                            <?php echo htmlspecialchars($msg['subject'] ?? 'New Inquiry'); ?>
                                        </h3>
                                        <p style="margin: 0; font-size: 0.85rem; color: var(--admin-secondary);">
                                            <strong><?php echo htmlspecialchars($msg['name']); ?></strong> &lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-size: 0.75rem; color: var(--admin-secondary); background: var(--admin-bg-light); padding: 5px 12px; border-radius: 20px; font-weight: 600; letter-spacing: 0.5px;">
                                            <?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div style="padding: 18px; background: #fafaf9; border-radius: 8px; border-left: 4px solid var(--admin-accent); font-size: 0.95rem; line-height: 1.6; color: #444;">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <div style="margin-top: 20px; text-align: right;">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject'] ?? 'Your Inquiry'); ?>" class="btn-secondary" style="padding: 8px 20px; font-size: 0.8rem; border-width: 2px;">Reply via Email</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 0;">
                        <div style="font-size: 3rem; color: var(--admin-border); margin-bottom: 15px;">📭</div>
                        <p style="color: var(--admin-secondary); font-size: 1.1rem; font-weight: 500;">No messages found.</p>
                        <p style="color: #999; font-size: 0.9rem; margin-top: 5px;">When guests submit the contact form, their messages will appear here.</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($total_messages_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_messages_pages; $i++): ?>
                            <a href="dashboard.php?tab=messages&page=<?php echo $i; ?>" class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
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