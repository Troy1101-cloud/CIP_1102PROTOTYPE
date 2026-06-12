<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

check_auth('staff');

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Handle booking status updates
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);
    
    add_audit_log('booking_updated', "Updated booking ID $booking_id status to $status");
}

// Get today's date
$today = date('Y-m-d');
$current_month = date('Y-m');

// Get metrics
$stmt = $pdo->query("SELECT COALESCE(SUM(total_cost), 0) AS total FROM reservations WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'");
$monthly_revenue = $stmt->fetch()['total'];

// Get today's arrivals
$stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_in = ? ORDER BY r.id DESC");
$stmt->execute([$today]);
$arrivals = $stmt->fetchAll();

// Get today's departures
$stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_out = ? ORDER BY r.id DESC");
$stmt->execute([$today]);
$departures = $stmt->fetchAll();

// Get pending balances (checking out today and status not paid)
$stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.check_out = ? AND r.status != 'checked_out' ORDER BY r.id DESC");
$stmt->execute([$today]);
$pending_balances = $stmt->fetchAll();

// Get all reservations
$stmt = $pdo->query("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id ORDER BY r.created_at DESC");
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Ralmitrokij Hotel</title>
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
            overflow-x:auto;
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
            padding:15px;
            border-bottom:1px solid var(--admin-border);
            font-size:0.9rem;
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
        .status-checked_in { background:#cce5ff; color:#004085; }
        .status-checked_out { background:#e2e3e5; color:#383d41; }
        .status-cancelled { background:#f8d7da; color:#721c24; }
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
        .alert-warning {
            background: rgba(255,193,7,0.1);
            color:#856404;
            padding:15px 20px;
            border-radius:8px;
            margin-bottom:30px;
            border-left:4px solid #ffc107;
        }
        .status-form {
            display:flex;
            gap:10px;
            align-items:center;
        }
        select {
            padding:8px 12px;
            border:1px solid var(--admin-border);
            border-radius:8px;
            background:white;
            font-size:0.85rem;
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
                    <li class="nav-item"><a href="dashboard.php?tab=reservations" class="<?php echo $current_tab === 'reservations' ? 'active' : ''; ?>">Reservations</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Staff'); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</div>
                <a href="../index.php" class="logout-btn">Back to Site</a>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
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

            <?php if (count($pending_balances) > 0): ?>
                <div class="alert-warning">
                    <strong>Attention:</strong> <?php echo count($pending_balances); ?> guest(s) checking out today have pending balances.
                </div>
            <?php endif; ?>

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
                                        <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
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
                                        <td>₱<?php echo number_format($booking['total_cost'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
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
                <h1 class="page-title">Manage Bookings</h1>
                <p class="page-subtitle">Update booking statuses (check in/out guests)</p>
            </div>
            <div class="glassmorphism-card">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Stay Dates</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['check_in'] . ' - ' . $booking['check_out']); ?></td>
                                    <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status">
                                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="checked_in" <?php echo $booking['status'] === 'checked_in' ? 'selected' : ''; ?>>Checked In</option>
                                                <option value="checked_out" <?php echo $booking['status'] === 'checked_out' ? 'selected' : ''; ?>>Checked Out</option>
                                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>