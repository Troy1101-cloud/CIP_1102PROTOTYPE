<?php
require_once 'includes/session_init.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare booking data
    $booking_data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'check_in' => $_POST['check_in'] ?? '',
        'check_out' => $_POST['check_out'] ?? '',
        'category' => $_POST['category'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'guests_adults' => $_POST['guests_adults'] ?? 2,
        'guests_children' => $_POST['guests_children'] ?? 0,
        'special_requests' => $_POST['special_requests'] ?? '',
        'amenities' => $_POST['addons'] ?? [],
        'dining' => $_POST['dining'] ?? [],
        'user_id' => $_SESSION['user_id'] ?? null,
        'source' => $_SESSION['role'] ?? 'guest'
    ];
    
    $errors = validate_booking($booking_data);
    
    if (empty($errors)) {
        // Find available room
        $rooms = get_all_data('rooms');
        $bookings = get_all_data('bookings');
        $category = $booking_data['category'];
        $price = $booking_data['price'];
        
        $available_room_id = null;
        $physical_rooms = array_filter($rooms, function($r) use ($category, $price) {
            return $r['category'] === $category && $r['price'] == $price && ($r['status'] ?? 'Available') === 'Available';
        });
        
        foreach ($physical_rooms as $r) {
            if (is_room_available($pdo, $r['id'], $booking_data['check_in'], $booking_data['check_out'])) {
                $available_room_id = $r['id'];
                break;
            }
        }
        
        if (!$available_room_id) {
            $_SESSION['booking_errors'] = ['Room Full. Please select another room category or different dates.'];
            header("Location: room.php?category=" . urlencode($category) . "&price=" . urlencode($price) . "#booking");
            exit;
        }
        
        $booking_data['room_id'] = $available_room_id;
        // Save booking and get ID
        $booking_id = save_booking($booking_data);
        
        // Redirect to checkout for payment
        header("Location: checkout.php?booking_id=" . $booking_id);
        exit;
    } else {
        $_SESSION['booking_errors'] = $errors;
        $_SESSION['booking_data'] = $booking_data;
        if (!empty($_POST['category'])) {
            header("Location: room.php?category=" . urlencode($_POST['category']) . "&price=" . urlencode($_POST['price']) . "#booking");
        } else {
            header("Location: rooms.php");
        }
        exit;
    }
} else {
    header("Location: rooms.php");
    exit;
}
?>
