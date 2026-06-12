<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

check_auth('agency');

$user_id = $_SESSION['user_id'];
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get agency info
$stmt = $pdo->prepare("SELECT ta.* FROM travel_agencies ta WHERE ta.user_id = ?");
$stmt->execute([$user_id]);
$agency = $stmt->fetch();

// Handle booking creation
if (isset($_POST['create_booking'])) {
    $room_id = (int)$_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $guests_adults = (int)$_POST['guests_adults'];
    $guests_children = (int)$_POST['guests_children'];

    // Calculate number of nights
    $check_in_obj = new DateTime($check_in);
    $check_out_obj = new DateTime($check_out);
    $nights = $check_in_obj->diff($check_out_obj)->days;

    // Get room price
    $stmt = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    $room_price = $room['price'];

    // Calculate total (room only, no service/amenities for agency booking)
    $total_cost = $room_price * $nights;
    
    // Generate booking ref
    $booking_ref = 'RM' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, first_name, last_name, email, phone, check_in, check_out, guests_adults, guests_children, booking_ref, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
    $stmt->execute([
        $user_id,
        $room_id,
        $first_name,
        $last_name,
        $email,
        $phone,
        $check_in,
        $check_out,
        $guests_adults,
        $guests_children,
        $booking_ref,
        $total_cost
    ]);

    add_audit_log('agency_booking', "Agency created booking with ref $booking_ref");
}

// Get agency's bookings
$stmt = $pdo->prepare("SELECT r.*, ro.name AS room_name FROM reservations r LEFT JOIN rooms ro ON r.room_id = ro.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Calculate total commission
$total_commission = 0;
$commission_rate = $agency['commission_rate'] / 100;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'checked_out') {
        $total_commission += $booking['total_cost'] * $commission_rate;
    }
}

// Get all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard | Ralmitrokij Hotel</title>
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
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
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
            padding:14px 30px;
            border-radius:8px;
            font-size:0.9rem;
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
            margin-top:20px;
        }
        .form-group {
            margin-bottom:20px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing:1px;
            text-transform:uppercase;
            color: var(--admin-secondary);
            margin-bottom:8px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width:100%;
            padding:12px 16px;
            border:1px solid var(--admin-border);
            border-radius:8px;
            font-size:0.95rem;
            font-family:'Montserrat', sans-serif;
            background: rgba(255,255,255,0.7);
            transition:all 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline:none;
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.15);
        }
        .grid-2 {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
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
                RALMITROKIJ <span>AGENCY DASHBOARD</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="dashboard.php?tab=overview" class="<?php echo $current_tab === 'overview' ? 'active' : ''; ?>">Overview</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=bookings" class="<?php echo $current_tab === 'bookings' ? 'active' : ''; ?>">Bookings</a></li>
                    <li class="nav-item"><a href="dashboard.php?tab=new_booking" class="<?php echo $current_tab === 'new_booking' ? 'active' : ''; ?>">New Booking</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <div class="user-info"><?php echo htmlspecialchars($agency['agency_name'] ?? $_SESSION['full_name']); ?> (Agency)</div>
                <a href="../index.php" class="logout-btn">Back to Site</a>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        <?php if ($current_tab === 'overview'): ?>
            <div class="page-header">
                <span class="breadcrumb">Partner Dashboard &rsaquo; Overview</span>
                <h1 class="page-title">Your Performance</h1>
                <p class="page-subtitle">View your bookings and commission</p>
            </div>

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Bookings</div>
                    <p class="metric-value"><?php echo count($bookings); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Total Earned Commission</div>
                    <p class="metric-value">₱<?php echo number_format($total_commission, 2); ?></p>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Your Commission Rate</div>
                    <p class="metric-value"><?php echo $agency['commission_rate']; ?>%</p>
                </div>
            </div>

            <div class="glassmorphism-card">
                <div class="section-title">
                    <h3>Recent Bookings</h3>
                    <p>Your last 5 bookings</p>
                </div>
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking Ref</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Stay Dates</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['check_in'] . ' - ' . $booking['check_out']); ?></td>
                                    <td>₱<?php echo number_format($booking['total_cost'], 2); ?></td>
                                    <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($current_tab === 'bookings'): ?>
            <div class="page-header">
                <span class="breadcrumb">Partner Dashboard &rsaquo; Bookings</span>
                <h1 class="page-title">Your Bookings</h1>
                <p class="page-subtitle">All bookings made through your agency</p>
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
                                <th>Total</th>
                                <th>Commission (<?php echo $agency['commission_rate']; ?>%)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_ref']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['check_in'] . ' - ' . $booking['check_out']); ?></td>
                                    <td>₱<?php echo number_format($booking['total_cost'], 2); ?></td>
                                    <td>₱<?php echo number_format($booking['total_cost'] * $commission_rate, 2); ?></td>
                                    <td><span class="status-badge status-<?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($current_tab === 'new_booking'): ?>
            <div class="page-header">
                <span class="breadcrumb">Partner Dashboard &rsaquo; New Booking</span>
                <h1 class="page-title">Create a Booking</h1>
                <p class="page-subtitle">Make a reservation for your client</p>
            </div>

            <div class="glassmorphism-card" style="max-width: 800px; margin:0 auto;">
                <form method="POST" action="">
                    <div class="section-title">
                        <h3>Guest Information</h3>
                        <p>Enter your client's details</p>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="section-title" style="margin-top: 40px;">
                        <h3>Stay Details</h3>
                        <p>Select room and dates</p>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="room_id">Select Room</label>
                            <select id="room_id" name="room_id" required>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['name']); ?> - ₱<?php echo number_format($room['price'], 2); ?>/night</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div></div>
                        <div class="form-group">
                            <label for="check_in">Check In Date</label>
                            <input type="date" id="check_in" name="check_in" required>
                        </div>
                        <div class="form-group">
                            <label for="check_out">Check Out Date</label>
                            <input type="date" id="check_out" name="check_out" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="guests_adults">Number of Adults</label>
                            <input type="number" id="guests_adults" name="guests_adults" value="2" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="guests_children">Number of Children</label>
                            <input type="number" id="guests_children" name="guests_children" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="btn-center">
                        <button type="submit" name="create_booking" class="btn-primary">Create Booking</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>