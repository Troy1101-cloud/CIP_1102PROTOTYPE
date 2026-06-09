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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .admin-header { background: var(--primary-color); color: #fff; padding: 25px 0; border-bottom: 1px solid var(--accent-color); }
        .admin-nav { border-bottom: 1px solid var(--border-color); margin-bottom: 40px; background: var(--white); }
        .admin-nav ul { display: flex; gap: 40px; }
        .admin-nav li { padding: 20px 0; cursor: pointer; border-bottom: 2px solid transparent; font-size: 0.75rem; font-weight: 700; letter-spacing: 2px; color: var(--secondary-color); transition: all 0.3s; }
        .admin-nav li:hover { color: var(--accent-color); }
        .admin-nav li.active { border-bottom-color: var(--accent-color); color: var(--primary-color); }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 25px;">
                <div class="logo-box" style="border-color: var(--accent-color); color: var(--white); padding: 5px 15px; font-size: 0.9rem;">RALMITROKIJ</div>
                <span style="opacity: 0.6; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 400;">Internal Management</span>
            </div>
            <div style="display: flex; gap: 30px; align-items: center; font-size: 0.75rem; font-weight: 600; letter-spacing: 1px;">
                <span style="color: var(--accent-color);">ADMINISTRATOR</span>
                <a href="logout.php" style="color: var(--white); opacity: 0.8; border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; transition: all 0.3s;">LOGOUT</a>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 25px; font-size: 0.7rem; color: var(--secondary-color); letter-spacing: 1px; text-transform: uppercase;">
        <span style="opacity: 0.5;">Management</span> &rsaquo; Reservations
    </div>

    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li>OVERVIEW</li>
                <li>ROOMS</li>
                <li class="active">RESERVATIONS</li>
                <li>AMENITIES</li>
                <li>REPORTS</li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px;">
            <div>
                <div class="section-title" style="text-align: left; margin-bottom: 0;">
                    <span>LIVE STATUS</span>
                    <h2>Reservations</h2>
                </div>
                <p style="margin-top: 10px; font-size: 0.9rem; color: var(--secondary-color);">Manage and track all hotel bookings and guest arrivals.</p>
            </div>
            
            <form action="bookings.php" method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.6rem; letter-spacing: 1px;">FILTER BY STATUS</label>
                    <select name="status" style="padding: 10px 15px; border: 1px solid var(--border-color); font-size: 0.8rem; font-family: var(--font-body);">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Reservations</option>
                        <option value="paid" <?php echo $filter_status === 'paid' ? 'selected' : ''; ?>>Confirmed Paid</option>
                        <option value="unpaid" <?php echo $filter_status === 'unpaid' ? 'selected' : ''; ?>>Pending Payment</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Awaiting Review</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="padding: 12px 25px; font-size: 0.7rem; letter-spacing: 1px;">APPLY FILTER</button>
            </form>
        </div>

        <div class="booking-card" style="position: static; padding: 0; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>GUEST NAME</th>
                        <th>ACCOMMODATION</th>
                        <th>STAY DURATION</th>
                        <th>GUESTS</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 60px; font-family: var(--font-header); font-size: 1.2rem; color: var(--secondary-color);">No reservations found for the current filter.</td></tr>
                    <?php else: ?>
                        <?php foreach($bookings as $b): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--accent-color);">#<?php echo $b['id']; ?></td>
                                <td style="font-weight: 600;"><?php echo h($b['first_name'] . ' ' . $b['last_name']); ?></td>
                                <td style="font-family: var(--font-header);"><?php echo h($room_names[$b['room_id']] ?? 'Unknown'); ?></td>
                                <td style="font-size: 0.8rem;"><?php echo h($b['check_in'] . ' — ' . $b['check_out']); ?></td>
                                <td><?php echo h($b['guests']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo h($b['payment_status']); ?>">
                                        <?php echo h($b['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn-link" style="font-size: 0.7rem; border-bottom: 1px solid var(--accent-color);">DETAILS</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
