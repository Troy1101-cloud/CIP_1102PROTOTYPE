<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_POST['submit_payment'])) {
    $booking_id = (int)$_POST['booking_id'];
    $payment_method = $_POST['payment_method'];
    $is_simulation = isset($_POST['simulation_complete']) ? true : false;
    
    // Simulate processing for credit card and e-wallet
    if (($payment_method === 'credit_card' || $payment_method === 'ewallet') && !$is_simulation) {
        // Redirect to simulation page first to mimic the wireframe process
        header("Location: simulate_redirect.php?booking_id=" . $booking_id . "&method=" . $payment_method);
        exit;
    }

    if ($payment_method === 'credit_card' || $payment_method === 'ewallet') {
        $result = process_payment_mock($_POST);
        
        if ($result['success']) {
            // Update booking status
            $bookings = get_all_data('bookings');
            foreach ($bookings as &$b) {
                if ($b['id'] === $booking_id) {
                    $b['payment_status'] = 'paid';
                    $b['payment_method'] = $payment_method;
                    $b['transaction_id'] = $result['transaction_id'];
                    $b['status'] = 'confirmed';
                    break;
                }
            }
            save_data('bookings', $bookings);
            
            header("Location: payment_success.php?id=" . $booking_id);
            exit;
        } else {
            header("Location: payment_failed.php?id=" . $booking_id);
            exit;
        }
    } else {
        // Cash payment
        $bookings = get_all_data('bookings');
        foreach ($bookings as &$b) {
            if ($b['id'] === $booking_id) {
                $b['payment_status'] = 'pending';
                $b['payment_method'] = 'cash';
                $b['status'] = 'pending_arrival';
                break;
            }
        }
        save_data('bookings', $bookings);
        
        header("Location: payment_success.php?id=" . $booking_id . "&method=cash");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
