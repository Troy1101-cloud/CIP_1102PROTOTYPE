<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_POST['submit'])) {
    // Prepare booking data
    $booking_data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'check_in' => $_POST['check_in'],
        'check_out' => $_POST['check_out'],
        'room_id' => $_POST['room_id'],
        'guests_adults' => isset($_POST['guests_adults']) ? $_POST['guests_adults'] : 2,
        'guests_children' => isset($_POST['guests_children']) ? $_POST['guests_children'] : 0,
        'special_requests' => isset($_POST['special_requests']) ? $_POST['special_requests'] : '',
        'amenities' => isset($_POST['amenities']) ? $_POST['amenities'] : [],
        'dining' => isset($_POST['dining']) ? $_POST['dining'] : []
    ];
    
    $errors = validate_booking($booking_data);
    
    if (empty($errors)) {
        // Save booking and get ID
        $booking_id = save_booking($booking_data);
        
        // Redirect to checkout for payment
        header("Location: checkout.php?booking_id=" . $booking_id);
        exit;
    } else {
        // In a real app, we'd pass errors back to the form
        die("Validation errors: " . implode(', ', $errors));
    }
} else {
    header("Location: rooms.php");
    exit;
}
?>
