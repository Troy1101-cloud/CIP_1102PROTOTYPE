<?php
/**
 * Core Functions for Ralmitrokij Hotel
 */

/**
 * Validate booking data
 * Returns an array of errors (empty if valid)
 */
function validate_booking($data) {
    $errors = [];
    
    if (empty(trim($data['first_name']))) {
        $errors['first_name'] = 'First name is required.';
    }
    
    if (empty(trim($data['last_name']))) {
        $errors['last_name'] = 'Last name is required.';
    }
    
    if (empty(trim($data['email'])) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    
    if (empty(trim($data['phone']))) {
        $errors['phone'] = 'Phone number is required.';
    }
    
    if (empty($data['check_in'])) {
        $errors['check_in'] = 'Check-in date is required.';
    }
    
    if (empty($data['check_out'])) {
        $errors['check_out'] = 'Check-out date is required.';
    }
    
    return $errors;
}

/**
 * Save booking to mock database
 */
function save_booking($data) {
    $bookings = get_all_data('bookings');
    $id = count($bookings) + 1;
    $data['id'] = $id;
    $data['status'] = 'pending';
    $data['payment_status'] = 'unpaid';
    $data['created_at'] = date('Y-m-d H:i:s');
    $bookings[] = $data;
    save_data('bookings', $bookings);
    return $id;
}

/**
 * Render a room card HTML
 */
function render_room_card($room) {
    $img_path = "assets/images/" . $room['image'];
    $detail_url = "room.php?id=" . $room['id'];
    $booking_url = "room.php?id=" . $room['id'] . "#booking";
    
    return "
    <article class='room-card'>
        <div class='room-image'>
            <img src='{$img_path}' alt='{$room['name']}'>
            <span class='category-label'>{$room['category']}</span>
        </div>
        <div class='room-content'>
            <h3>{$room['name']}</h3>
            <p>" . substr($room['description'], 0, 100) . "...</p>
            <div class='room-actions'>
                <a href='{$detail_url}' class='btn-link'>VIEW DETAILS &rarr;</a>
                <button type='button' class='btn-secondary' onclick=\"openBookingModal('{$room['id']}', '{$room['name']}')\">BOOK</button>
            </div>
        </div>
    </article>
    ";
}

/**
 * Process mock payment
 */
function process_payment_mock($paymentData) {
    // Simulate processing delay
    // In a real app, this would call a payment gateway API
    
    // Simulate 90% success rate for prototype
    $is_success = (rand(1, 10) > 1);
    
    return [
        'success' => $is_success,
        'transaction_id' => $is_success ? 'TXN' . strtoupper(uniqid()) : null,
        'message' => $is_success ? 'Payment processed successfully.' : 'Payment failed. Please try again.'
    ];
}

/**
 * Sanitize output
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
