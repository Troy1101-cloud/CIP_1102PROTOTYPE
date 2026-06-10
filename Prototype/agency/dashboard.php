<?php
session_start();
if (!isset($_SESSION['agency_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$bookings = get_all_data('bookings');
$total_bookings = count($bookings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Agency Dashboard | Ralmitrokij Hotel</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="admin-header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 25px;">
                <div class="logo-box" style="border-color: var(--accent-color); color: var(--white); padding: 5px 15px; font-size: 0.9rem;">RALMITROKIJ</div>
                <span style="opacity: 0.6; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 400;">Agency Portal</span>
            </div>
            <div style="display: flex; gap: 30px; align-items: center; font-size: 0.75rem; font-weight: 600; letter-spacing: 1px;">
                <span style="color: var(--accent-color);">TRAVEL AGENCY</span>
                <a href="logout.php" style="color: var(--white); opacity: 0.8; border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; transition: all 0.3s;">LOGOUT</a>
            </div>
        </div>
    </header>

    <main class="container" style="padding: 50px 0;">
        <div class="section-title" style="text-align: left; margin-bottom: 30px;">
            <span>AGENCY DASHBOARD</span>
            <h2>Reservation Access</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div class="booking-card" style="padding: 30px; text-align: center; border: 1px solid var(--border-color);">
                <h3>Total Reservations</h3>
                <p style="font-size: 2rem; color: var(--accent-color); margin-top: 15px; font-weight: 700;"><?php echo $total_bookings; ?></p>
            </div>
            <div class="booking-card" style="padding: 30px; text-align: center; border: 1px solid var(--border-color);">
                <h3>Quick Actions</h3>
                <p style="margin-top: 15px; color: var(--secondary-color);">Review bookings, confirm guest details, or coordinate arrivals with hotel staff.</p>
            </div>
        </div>

        <div class="booking-card" style="padding: 40px;">
            <h3 style="margin-bottom: 20px;">Available Bookings</h3>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($bookings, 0, 6) as $b): ?>
                        <tr>
                            <td>#<?php echo $b['id']; ?></td>
                            <td><?php echo h($b['first_name'] . ' ' . $b['last_name']); ?></td>
                            <td><?php echo h($b['check_in']); ?></td>
                            <td><?php echo h($b['check_out']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
