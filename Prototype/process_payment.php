<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    $payment_method = $_POST['payment_method'];
    $is_simulation = isset($_POST['simulation_complete']) ? true : false;
    
    $online_methods = ['visa', 'mastercard', 'gcash', 'maya', 'bank_transfer'];
    $is_online = in_array($payment_method, $online_methods);
    
    // Simulate processing for online payments
    if ($is_online && !$is_simulation) {
        // Redirect to simulation page first to mimic the wireframe process
        header("Location: simulate_redirect.php?booking_id=" . $booking_id . "&method=" . $payment_method);
        exit;
    }

    if ($is_online) {
        $result = simulate_payment($_POST);
        
        if ($result['success']) {
            // Update booking status
            $stmt = $pdo->prepare("UPDATE reservations SET payment_method = ?, status = 'confirmed' WHERE id = ?");
            $stmt->execute([$payment_method, $booking_id]);
            
            header("Location: payment_success.php?id=" . $booking_id);
            exit;
        } else {
            header("Location: payment_failed.php?id=" . $booking_id);
            exit;
        }
    } else {
        // Pay at Hotel
        $stmt = $pdo->prepare("UPDATE reservations SET payment_method = 'pay_at_hotel', status = 'confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        header("Location: payment_success.php?id=" . $booking_id . "&method=cash");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
