<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_POST['submit'])) {
    $errors = validate_booking($_POST);
    
    if (empty($errors)) {
        // Save booking and get ID
        $booking_id = save_booking($_POST);
        
        // Redirect to checkout page
        header("Location: checkout.php?booking_id=" . $booking_id);
        exit;
    } else {
        // In a real app, we'd pass errors back to the form
        // For this prototype, we'll just show a simple error list or redirect back
        die("Validation errors: " . implode(', ', $errors));
    }
} else {
    header("Location: rooms.php");
    exit;
}
?>
