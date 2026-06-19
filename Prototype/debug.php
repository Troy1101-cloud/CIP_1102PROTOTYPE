<?php
require 'includes/db_connect.php';

$out = "Timezone: " . date_default_timezone_get() . "\n";
$out .= "PHP Today: " . date('Y-m-d') . "\n";
$out .= "PHP Now: " . date('Y-m-d H:i:s') . "\n\n";

$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
$rooms = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM reservations"); 
$all_sys_bookings = $stmt->fetchAll(); 

foreach ($all_sys_bookings as $b) {
    $out .= "Booking ID: {$b['id']}, Room ID: {$b['room_id']}, CheckIn: {$b['check_in']}, CheckOut: {$b['check_out']}, Status: {$b['status']}\n";
}

$booked_rooms = 0;
foreach ($rooms as $rm) {
    $qty = $rm['quantity'] ?? 1;
    $is_booked_count = 0;
    foreach ($all_sys_bookings as $b) {
        if ((string)$b['room_id'] === (string)$rm['id'] && !in_array(strtolower($b['status']), ['checked_out', 'checked-out', 'cancelled'])) {
            $today_date = date('Y-m-d');
            if ($today_date >= $b['check_in'] && $today_date < $b['check_out']) {
                $is_booked_count++;
            }
        }
    }
    $out .= "Room {$rm['name']} (ID {$rm['id']}): Qty $qty, Booked $is_booked_count\n";
    $booked_rooms += $is_booked_count;
}

$out .= "Total Booked Today: $booked_rooms\n";

file_put_contents('debug_output.txt', $out);
?>
