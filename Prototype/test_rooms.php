<?php
require 'includes/db_connect.php';

$today_date = date('Y-m-d');
echo "Today is: $today_date\n";

$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
$rooms = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM reservations"); 
$all_sys_bookings = $stmt->fetchAll(); 

foreach ($all_sys_bookings as $b) {
    echo "Booking ID: {$b['id']}, Room ID: {$b['room_id']}, CheckIn: {$b['check_in']}, CheckOut: {$b['check_out']}, Status: {$b['status']}\n";
}

$booked_rooms = 0;
foreach ($rooms as $rm) {
    $qty = $rm['quantity'] ?? 1;
    $is_booked_count = 0;
    foreach ($all_sys_bookings as $b) {
        if ((string)$b['room_id'] === (string)$rm['id'] && !in_array(strtolower($b['status']), ['checked_out', 'checked-out', 'cancelled'])) {
            if ($today_date >= $b['check_in'] && $today_date < $b['check_out']) {
                $is_booked_count++;
            }
        }
    }
    echo "Room {$rm['name']} (ID {$rm['id']}): Qty $qty, Booked $is_booked_count\n";
    $booked_rooms += $is_booked_count;
}

echo "Total Booked Today: $booked_rooms\n";
?>
