<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch all data
$bookings = get_all_data('bookings');
$rooms = get_all_data('rooms');
$amenities = get_amenities_pricing();
$dining_options = get_dining_pricing();

// Initialize overall counters and accumulators
$total_reservations = count($bookings);
$total_nights_booked = 0;
$total_room_revenue = 0;
$total_amenities_revenue = 0;
$total_dining_revenue = 0;
$total_service_fees = 0;
$overall_subtotal = 0;
$overall_tax = 0;
$grand_total_revenue = 0;

// Track itemized counts for Amenities and Dining
$amenities_counts = [];
$dining_counts = [];

// Process every booking to aggregate totals
foreach($bookings as $booking) {
    // 1. Get room details for current booking
    $room = null;
    foreach($rooms as $r) {
        if ($r['id'] == $booking['room_id']) {
            $room = $r;
            break;
        }
    }

    // 2. Calculate nights
    $check_in = new DateTime($booking['check_in']);
    $check_out = new DateTime($booking['check_out']);
    $interval = $check_in->diff($check_out);
    $nights = $interval->days;
    $total_nights_booked += $nights;

    // 3. Calculate room charges
    $room_rate = $room ? (float)$room['price'] : 0;
    $room_total = $room_rate * $nights;
    $total_room_revenue += $room_total;
    
    $service_fee = 25;
    $total_service_fees += $service_fee;

    // 4. Calculate amenities charges & counts
    $amenities_selected = isset($booking['amenities']) ? $booking['amenities'] : [];
    $total_amenities_revenue += calculate_amenities_total($amenities_selected);
    
    foreach ($amenities_selected as $am_id) {
        if (!isset($amenities_counts[$am_id])) {
            $amenities_counts[$am_id] = 0;
        }
        $amenities_counts[$am_id]++;
    }

    // 5. Calculate dining charges & counts
    $dining_selected = isset($booking['dining']) ? $booking['dining'] : [];
    $total_dining_revenue += calculate_dining_total($dining_selected);
    
    foreach ($dining_selected as $din_id) {
        if (!isset($dining_counts[$din_id])) {
            $dining_counts[$din_id] = 0;
        }
        $dining_counts[$din_id]++;
    }

    // 6. Calculate Subtotal, Tax, and Grand Total per booking
    $booking_subtotal = $room_total + $service_fee + calculate_amenities_total($amenities_selected) + calculate_dining_total($dining_selected);
    $overall_subtotal += $booking_subtotal;
    
    $booking_tax = $booking_subtotal * 0.12;
    $overall_tax += $booking_tax;
    
    $grand_total_revenue += ($booking_subtotal + $booking_tax);
}

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>MANAGEMENT DASHBOARD</span>
            <h2>TOTAL SYSTEM REPORT</h2>
            <p>Aggregate overview of all reservations, revenue, dining, and amenities.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
            <div class="booking-card" style="padding: 30px; text-align: center;">
                <span style="color: var(--secondary-color); font-size: 0.9rem; text-transform: uppercase;">Total Reservations</span>
                <h3 style="font-size: 2.5rem; margin-top: 10px; color: var(--primary-color);"><?php echo $total_reservations; ?></h3>
            </div>
            <div class="booking-card" style="padding: 30px; text-align: center;">
                <span style="color: var(--secondary-color); font-size: 0.9rem; text-transform: uppercase;">Room Nights Booked</span>
                <h3 style="font-size: 2.5rem; margin-top: 10px; color: var(--primary-color);"><?php echo $total_nights_booked; ?></h3>
            </div>
            <div class="booking-card" style="padding: 30px; text-align: center;">
                <span style="color: var(--secondary-color); font-size: 0.9rem; text-transform: uppercase;">Total Add-ons Sold</span>
                <h3 style="font-size: 2.5rem; margin-top: 10px; color: var(--primary-color);"><?php echo array_sum($amenities_counts) + array_sum($dining_counts); ?></h3>
            </div>
            <div class="booking-card" style="padding: 30px; text-align: center; border-bottom: 4px solid var(--accent-color);">
                <span style="color: var(--secondary-color); font-size: 0.9rem; text-transform: uppercase;">Total Revenue</span>
                <h3 style="font-size: 2.5rem; margin-top: 10px; color: var(--accent-color);">$<?php echo number_format($grand_total_revenue, 2); ?></h3>
            </div>
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 60px;">
            <div class="payment-details">
                
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>HOTEL AMENITIES</span>
                        <h3>Amenities Usage Breakdown</h3>
                    </div>
                    <?php if (!empty($amenities_counts)): ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                                <th style="padding-bottom: 15px;">Amenity Name</th>
                                <th style="padding-bottom: 15px; text-align: center;">Times Booked</th>
                                <th style="padding-bottom: 15px; text-align: right;">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($amenities_counts as $am_id => $count): ?>
                                <?php if (isset($amenities[$am_id])): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 15px 0;">
                                        <strong><?php echo h($amenities[$am_id]['name']); ?></strong><br>
                                        <span style="font-size: 0.85rem; color: var(--secondary-color);"><?php echo h($amenities[$am_id]['category']); ?></span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;"><?php echo $count; ?></td>
                                    <td style="text-align: right; color: var(--secondary-color);">
                                        <?php echo $amenities[$am_id]['price'] > 0 ? '$' . number_format($amenities[$am_id]['price'], 2) : '<span style="color: var(--accent-color);">FREE</span>'; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>No amenities have been booked yet.</p>
                    <?php endif; ?>
                </div>

                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>DINING & CUISINE</span>
                        <h3>Dining Options Breakdown</h3>
                    </div>
                    <?php if (!empty($dining_counts)): ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                                <th style="padding-bottom: 15px;">Dining Option</th>
                                <th style="padding-bottom: 15px; text-align: center;">Orders/Bookings</th>
                                <th style="padding-bottom: 15px; text-align: right;">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dining_counts as $din_id => $count): ?>
                                <?php if (isset($dining_options[$din_id])): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 15px 0;">
                                        <strong><?php echo h($dining_options[$din_id]['name']); ?></strong><br>
                                        <span style="font-size: 0.85rem; color: var(--secondary-color);"><?php echo h($dining_options[$din_id]['category']); ?></span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;"><?php echo $count; ?></td>
                                    <td style="text-align: right; color: var(--secondary-color);">$<?php echo number_format($dining_options[$din_id]['price'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>No dining options have been booked yet.</p>
                    <?php endif; ?>
                </div>

            </div>

            <div class="checkout-sidebar">
                <div class="price-estimate" style="text-align: left; padding: 40px; margin: 0; position: sticky; top: 120px; border: 1px solid var(--border-color);">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>FINANCIALS</span>
                        <h3>Revenue Breakdown</h3>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Total Room Charges</span>
                        <span style="font-weight: 600;">$<?php echo number_format($total_room_revenue, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Accumulated Service Fees</span>
                        <span style="font-weight: 600;">$<?php echo number_format($total_service_fees, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Amenities Revenue</span>
                        <span style="font-weight: 600;">$<?php echo number_format($total_amenities_revenue, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Dining Revenue</span>
                        <span style="font-weight: 600;">$<?php echo number_format($total_dining_revenue, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Total Subtotal</span>
                        <span style="font-weight: 600;">$<?php echo number_format($overall_subtotal, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Total Taxes Collected (12%)</span>
                        <span style="font-weight: 600;">$<?php echo number_format($overall_tax, 2); ?></span>
                    </div>

                    <div style="border-top: 2px solid var(--accent-color); padding-top: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <span style="font-family: var(--font-header); font-size: 1.2rem;">Total Revenue</span>
                        <span style="font-family: var(--font-header); font-size: 2rem; color: var(--accent-color);">$<?php echo number_format($grand_total_revenue, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>