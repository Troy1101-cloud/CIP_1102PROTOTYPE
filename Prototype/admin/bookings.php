<?php
require_once '../includes/session_init.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

check_auth('admin');

$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll();
$room_names = [];
foreach($rooms as $r) {
    $room_names[$r['id']] = $r['name'];
}

$stmt = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC");
$db_reservations = $stmt->fetchAll();

$bookings = [];
foreach($db_reservations as $r) {
    $bookings[] = [
        'id' => str_pad($r['id'], 4, '0', STR_PAD_LEFT),
        'first_name' => $r['first_name'],
        'last_name' => $r['last_name'],
        'email' => $r['email'],
        'phone' => $r['phone'],
        'special_requests' => $r['special_requests'],
        'room_id' => $r['room_id'],
        'check_in' => $r['check_in'],
        'check_out' => $r['check_out'],
        'guests_adults' => $r['guests_adults'],
        'guests_children' => $r['guests_children'],
        'guests' => $r['guests_adults'] . ($r['guests_children'] > 0 ? " (+{$r['guests_children']})" : ""),
        'payment_status' => $r['status'] === 'confirmed' ? 'paid' : ($r['status'] === 'pending' ? 'pending' : 'unpaid')
    ];
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
    <link rel="icon" type="image/png" href="../pictures/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
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
                <a href="../index.php" style="color: var(--white); opacity: 0.8; text-decoration: none;">BACK TO SITE</a>
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
                                    <?php $b['room_name'] = $room_names[$b['room_id']] ?? 'Unknown'; ?>
                                    <button type="button" class="btn-link" style="font-size: 0.7rem; border-bottom: 1px solid var(--accent-color); background:none; border:none; cursor:pointer;" onclick='openBookingDetails(<?php echo json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>DETAILS</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
