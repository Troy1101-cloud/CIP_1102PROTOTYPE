<?php
/**
 * Core Functions for Ralmitrokij Hotel
 */

/**
 * Legacy compatibility: Get all data from a table
 */
function get_all_data($table) {
    global $pdo;
    if (!$pdo) return [];
    
    if ($table === 'bookings') $table = 'reservations';
    
    $allowed_tables = ['rooms', 'reservations', 'addons', 'users', 'travel_agencies'];
    if (!in_array($table, $allowed_tables)) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM " . $table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Validate booking data
 * Returns an array of errors (empty if valid)
 */
function validate_booking($data) {
    $errors = [];
    
    if (empty(trim($data['first_name'] ?? ''))) {
        $errors['first_name'] = 'First name is required.';
    }
    
    if (empty(trim($data['last_name'] ?? ''))) {
        $errors['last_name'] = 'Last name is required.';
    }
    
    if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    
    if (empty(trim($data['phone'] ?? ''))) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^(\+63|0)[\s\-]?9\d{2}[\s\-]?\d{3}[\s\-]?\d{4}$/', trim($data['phone']))) {
        $errors['phone'] = 'Phone number must be in the format 09XX-XXX-XXXX or +63-9XX-XXX-XXXX.';
    }
    
    $today = date('Y-m-d');
    
    if (empty($data['check_in'])) {
        $errors['check_in'] = 'Check-in date is required.';
    } elseif ($data['check_in'] < $today) {
        $errors['check_in'] = 'Check-in date cannot be in the past.';
    }
    
    if (empty($data['check_out'])) {
        $errors['check_out'] = 'Check-out date is required.';
    } elseif (!empty($data['check_in']) && $data['check_out'] <= $data['check_in']) {
        $errors['check_out'] = 'Check-out date must be after check-in date.';
    }
    
    return $errors;
}

/**
 * Get room by ID
 */
function get_room($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    return $room ? $room : null;
}

/**
 * Get booking by ID
 */
function get_booking($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    return $booking ? $booking : null;
}

/**
 * Save booking to database
 */
function save_booking($data) {
    global $pdo;

    // Calculate total cost before inserting
    $room = get_room($data['room_id'] ?? 1);
    if ($room) {
        $totals = calculate_booking_total($data, $room);
        $total_cost = $totals['grand_total'];
    } else {
        $total_cost = 0;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reservations 
            (room_id, user_id, source, first_name, last_name, email, phone, check_in, check_out, guests_adults, guests_children, special_requests, amenities, dining, payment_method, booking_ref, total_cost, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Generate a random booking ref
        $booking_ref = strtoupper(substr(uniqid('BK'), 0, 10));
        
        $stmt->execute([
            $data['room_id'] ?? 1,
            $data['user_id'] ?? null,
            $data['source'] ?? 'guest',
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['check_in'],
            $data['check_out'],
            $data['guests_adults'] ?? 1,
            $data['guests_children'] ?? 0,
            $data['special_requests'] ?? null,
            json_encode($data['amenities'] ?? []),
            json_encode($data['dining'] ?? []),
            '', // payment method
            $booking_ref,
            $total_cost,
            'pending_verification'
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("DB Insert Failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if a room is available for the given dates
 */
function is_room_available($pdo, $room_id, $check_in, $check_out) {
    try {
        $stmt = $pdo->prepare("SELECT quantity FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch();
        $quantity = $room['quantity'] ?? 1;

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM reservations 
            WHERE room_id = ? 
            AND status NOT IN ('cancelled', 'checked_out', 'checked-out')
            AND check_in < ? 
            AND check_out > ?
        ");
        $stmt->execute([$room_id, $check_out, $check_in]);
        $result = $stmt->fetch();
        return $result['count'] < $quantity;
    } catch (PDOException $e) {
        error_log("DB Availability Check Failed: " . $e->getMessage());
        return false;
    }
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
                <a href='{$booking_url}' class='btn-secondary'>BOOK</a>
            </div>
        </div>
    </article>
    ";
}

/**
 * Simulate payment processing
 */
function simulate_payment($paymentData) {
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
 * Check if a room category is available for given dates
 */
function is_room_group_available($category, $price, $check_in = null, $check_out = null) {
    $rooms = get_all_data('rooms');
    $bookings = get_all_data('bookings');
    
    if (!$check_in) $check_in = date('Y-m-d');
    if (!$check_out) $check_out = date('Y-m-d', strtotime('+1 day'));
    
    $physical_rooms = array_filter($rooms, function($r) use ($category, $price) {
        return $r['category'] === $category && $r['price'] == $price && ($r['status'] ?? 'Available') === 'Available';
    });
    
    if (empty($physical_rooms)) return false;
    
    foreach ($physical_rooms as $room) {
        $qty = $room['quantity'] ?? 1;
        $booked_count = 0;
        foreach ($bookings as $b) {
            if ((string)$b['room_id'] === (string)$room['id'] && !in_array($b['status'], ['checked_out', 'cancelled'])) {
                // Check date overlap
                if ($check_in < $b['check_out'] && $check_out > $b['check_in']) {
                    $booked_count++;
                }
            }
        }
        if ($booked_count < $qty) return true; // Found at least one available slot in this room type
    }
    
    return false;
}

/**
 * Get detailed stats for a room category and price
 */
function get_room_group_stats($category, $price, $check_in = null, $check_out = null) {
    $rooms = get_all_data('rooms');
    $bookings = get_all_data('bookings');
    
    if (!$check_in) $check_in = date('Y-m-d');
    if (!$check_out) $check_out = date('Y-m-d', strtotime('+1 day'));
    
    $stats = ['total' => 0, 'maintenance' => 0, 'booked' => 0, 'available' => 0];
    
    foreach ($rooms as $room) {
        if ($room['category'] === $category && $room['price'] == $price) {
            $qty = $room['quantity'] ?? 1;
            $stats['total'] += $qty;
            if (($room['status'] ?? 'Available') === 'Maintenance') {
                $stats['maintenance'] += $qty;
            } else {
                $booked_count = 0;
                foreach ($bookings as $b) {
                    if ((string)$b['room_id'] === (string)$room['id'] && !in_array($b['status'], ['checked_out', 'cancelled'])) {
                        if ($check_in < $b['check_out'] && $check_out > $b['check_in']) {
                            $booked_count++;
                        }
                    }
                }
                $stats['booked'] += $booked_count;
                $stats['available'] += max(0, $qty - $booked_count);
            }
        }
    }
    
    return $stats;
}

/**
 * Fetch addons by category
 */
function get_addons_by_category($category) {
    global $pdo;
    $addons_list = [];
    $stmt = $pdo->prepare("SELECT * FROM addons WHERE category = ?");
    $stmt->execute([$category]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $addons_list[$row['id']] = $row;
    }
    return $addons_list;
}

/**
 * Get amenity pricing
 */
function get_amenities_pricing() {
    return get_addons_by_category('amenity');
}

/**
 * Get dining options pricing
 */
function get_dining_pricing() {
    return get_addons_by_category('dining');
}

/**
 * Get free amenities from a room's amenities string or array
 */
function get_free_room_amenities($amenities_input) {
    if (empty($amenities_input)) return [];
    
    if (is_array($amenities_input)) {
        return $amenities_input;
    } else {
        return array_map('trim', explode(',', $amenities_input));
    }
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
