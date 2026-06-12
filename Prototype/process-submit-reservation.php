<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_POST['submit_reservation'])) {
    // Get booking ID and payment method
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $booking_ref = isset($_POST['booking_ref']) ? $_POST['booking_ref'] : '';

    // Get all bookings
    $bookings = get_all_data('bookings');
    $booking_found = false;

    // Update booking
    foreach ($bookings as &$booking) {
        if ($booking['id'] === $booking_id) {
            $booking['payment_method'] = $payment_method;
            $booking['booking_ref'] = $booking_ref;
            $booking['status'] = 'confirmed';
            $booking_found = true;
            break;
        }
    }

    // Save updated bookings
    if ($booking_found) {
        save_data('bookings', $bookings);
        
        // Redirect to success page
        header("Location: reservation-success.php?booking_ref=" . urlencode($booking_ref));
        exit;
    } else {
        header("Location: rooms.php");
        exit;
    }
} else {
    header("Location: rooms.php");
    exit;
}
?>
