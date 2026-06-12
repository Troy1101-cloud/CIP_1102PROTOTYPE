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
    $img_path = "pictures/" . $room['image'];
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

/**
 * Format Philippine Peso (₱)
 */
function format_php($amount) {
    return '₱' . number_format($amount, 0, '.', ',');
}

/**
 * Get amenity pricing
 */
function get_amenities_pricing() {
    return [
        1 => ['name' => 'Infinity Pool', 'price' => 0, 'category' => 'FREE'],
        2 => ['name' => 'Fitness Center', 'price' => 0, 'category' => 'FREE'],
        3 => ['name' => 'Serenity Spa', 'price' => 2500, 'category' => 'WELLNESS'],
    ];
}

/**
 * Get dining options pricing
 */
function get_dining_pricing() {
    return [
        1 => ['name' => 'The Sky Lounge', 'price' => 1500, 'category' => 'FINE DINING'],
        2 => ['name' => 'Artisan Café', 'price' => 800, 'category' => 'CASUAL'],
        3 => ['name' => 'The Copper Bar', 'price' => 1000, 'category' => 'BAR & GRILL'],
    ];
}

/**
 * Calculate total amenities cost
 */
function calculate_amenities_total($amenities_selected) {
    $amenities = get_amenities_pricing();
    $total = 0;
    if (is_array($amenities_selected)) {
        foreach($amenities_selected as $am_id) {
            if (isset($amenities[$am_id])) {
                $total += $amenities[$am_id]['price'];
            }
        }
    }
    return $total;
}

/**
 * Calculate total dining cost
 */
function calculate_dining_total($dining_selected) {
    $dining = get_dining_pricing();
    $total = 0;
    if (is_array($dining_selected)) {
        foreach($dining_selected as $din_id) {
            if (isset($dining[$din_id])) {
                $total += $dining[$din_id]['price'];
            }
        }
    }
    return $total;
}

/**
 * Calculate booking grand total
 */
function calculate_booking_total($booking, $room) {
    // Calculate number of nights
    $check_in = new DateTime($booking['check_in']);
    $check_out = new DateTime($booking['check_out']);
    $interval = $check_in->diff($check_out);
    $nights = $interval->days;
    
    // Calculate room charges
    $room_rate = (float)$room['price'];
    $room_total = $room_rate * $nights;
    $service_fee = 500;
    $subtotal = $room_total + $service_fee;
    
    // Calculate amenities and dining charges
    $amenities_selected = isset($booking['amenities']) ? $booking['amenities'] : [];
    $dining_selected = isset($booking['dining']) ? $booking['dining'] : [];
    
    $amenities_total = calculate_amenities_total($amenities_selected);
    $dining_total = calculate_dining_total($dining_selected);
    
    // Calculate tax
    $taxable_amount = $subtotal + $amenities_total + $dining_total;
    $tax = $taxable_amount * 0.12;
    
    // Grand total
    $grand_total = $taxable_amount + $tax;
    
    return [
        'nights' => $nights,
        'room_total' => $room_total,
        'service_fee' => $service_fee,
        'subtotal' => $subtotal,
        'amenities_total' => $amenities_total,
        'dining_total' => $dining_total,
        'taxable_amount' => $taxable_amount,
        'tax' => $tax,
        'grand_total' => $grand_total
    ];
}
?>
