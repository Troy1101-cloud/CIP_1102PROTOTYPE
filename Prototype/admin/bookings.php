<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$bookings = get_all_data('bookings');
$rooms = get_all_data('rooms');

// Mock room lookup
$room_names = [];
foreach($rooms as $r) {
    $room_names[$r['id']] = $r['name'];
}

// Handle Filter
$filter_status = isset($_POST['status']) ? $_POST['status'] : 'all';
if ($filter_status !== 'all') {
    $bookings = array_filter($bookings, function($b) use ($filter_status) {
        return $b['payment_status'] === $filter_status;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .admin-header { background: #1a1a1a; color: #fff; padding: 15px 0; }
        .admin-nav { border-bottom: 1px solid #ddd; margin-bottom: 30px; }
        .admin-nav ul { display: flex; gap: 30px; }
        .admin-nav li { padding: 15px 0; cursor: pointer; border-bottom: 3px solid transparent; }
        .admin-nav li.active { border-bottom-color: #000; font-weight: 700; }
        .status-badge { padding: 4px 8px; font-size: 0.7rem; font-weight: 700; border-radius: 3px; text-transform: uppercase; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="logo-box" style="border-color: #fff; color: #fff; padding: 2px 10px; font-size: 0.8rem;">RALMITROKIJ</div>
                <span style="opacity: 0.5; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">| Admin Dashboard</span>
            </div>
            <div style="display: flex; gap: 20px; align-items: center; font-size: 0.8rem;">
                <span>Admin User</span>
                <a href="logout.php" style="opacity: 0.7;">&rarr; LOGOUT</a>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 20px; font-size: 0.8rem; color: var(--secondary-color);">
        DASHBOARD &rsaquo; Reservations
    </div>

    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li>OVERVIEW</li>
                <li>ROOMS</li>
                <li class="active">RESERVATIONS</li>
                <li>USERS</li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
            <div>
                <h2>Reservations</h2>
                <p>Manage and track all hotel bookings.</p>
            </div>
            
            <form action="bookings.php" method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="status" style="padding: 8px;">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="paid" <?php echo $filter_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="unpaid" <?php echo $filter_status === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 0.7rem;">FILTER</button>
            </form>
        </div>

        <table class="bookings-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest Name</th>
                    <th>Room Type</th>
                    <th>Dates</th>
                    <th>Guests</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 40px;">No reservations found.</td></tr>
                <?php else: ?>
                    <?php foreach($bookings as $b): ?>
                        <tr>
                            <td>#<?php echo $b['id']; ?></td>
                            <td><?php echo h($b['first_name'] . ' ' . $b['last_name']); ?></td>
                            <td><?php echo h($room_names[$b['room_id']] ?? 'Unknown'); ?></td>
                            <td><?php echo h($b['check_in'] . ' to ' . $b['check_out']); ?></td>
                            <td><?php echo h($b['guests']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo h($b['payment_status']); ?>">
                                    <?php echo h($b['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" style="text-decoration: underline; font-weight: 700;">VIEW</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
