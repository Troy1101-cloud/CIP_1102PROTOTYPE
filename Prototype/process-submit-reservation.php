<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (isset($_POST['submit_reservation'])) {
    // Get booking ID and payment method
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $booking_ref = isset($_POST['booking_ref']) ? $_POST['booking_ref'] : '';

    if ($booking_id > 0) {
        $stmt = $pdo->prepare("UPDATE reservations SET payment_method = ?, booking_ref = ?, status = 'confirmed' WHERE id = ?");
        $stmt->execute([$payment_method, $booking_ref, $booking_id]);
        
        if ($stmt->rowCount() > 0) {
            // Redirect to success page
            header("Location: reservation-success.php?booking_ref=" . urlencode($booking_ref));
            exit;
        }
    }
    
    header("Location: rooms.php");
    exit;
} else {
    header("Location: rooms.php");
    exit;
}
?>
