<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$bookings = get_all_data('bookings');
$booking = null;

foreach($bookings as $b) {
    if ($b['id'] === $booking_id) {
        $booking = $b;
        break;
    }
}

if (!$booking) {
    header('Location: index.php');
    exit;
}

// Get room details
$rooms = get_all_data('rooms');
$room = null;
foreach($rooms as $r) {
    if ($r['id'] == $booking['room_id']) {
        $room = $r;
        break;
    }
}

// Define amenities and dining with prices
$amenities = get_amenities_pricing();
$dining_options = get_dining_pricing();

// Calculate number of nights
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;

// Calculate room charges
$room_rate = (float)$room['price'];
$room_total = $room_rate * $nights;
$service_fee = 25;
$subtotal = $room_total + $service_fee;

// Calculate amenities charges
$amenities_selected = isset($booking['amenities']) ? $booking['amenities'] : [];
$amenities_total = calculate_amenities_total($amenities_selected);

// Calculate dining charges
$dining_selected = isset($booking['dining']) ? $booking['dining'] : [];
$dining_total = calculate_dining_total($dining_selected);

// Calculate tax (12% on subtotal + amenities + dining)
$taxable_amount = $subtotal + $amenities_total + $dining_total;
$tax = $taxable_amount * 0.12;

// Grand total
$grand_total = $taxable_amount + $tax;

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>PAYMENT SUMMARY</span>
            <h2>TOTAL PAYMENT BREAKDOWN</h2>
            <p>Complete overview of all charges for your reservation.</p>
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 60px;">
            <div class="payment-details">
                <!-- Guest Information -->
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>GUEST INFORMATION</span>
                        <h3>Reservation Details</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div>
                            <p style="margin-bottom: 15px;">
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Guest Name:</strong><br>
                                <span style="font-family: var(--font-header); font-size: 1.1rem; color: var(--primary-color);"><?php echo h($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                            </p>
                            <p style="margin-bottom: 15px;">
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Email:</strong><br>
                                <?php echo h($booking['email']); ?>
                            </p>
                            <p>
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Phone:</strong><br>
                                <?php echo h($booking['phone']); ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin-bottom: 15px;">
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Room:</strong><br>
                                <span style="font-family: var(--font-header); font-size: 1.1rem; color: var(--primary-color);"><?php echo h($room['name']); ?></span>
                            </p>
                            <p style="margin-bottom: 15px;">
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Check-In:</strong><br>
                                <?php echo h($booking['check_in']); ?>
                            </p>
                            <p>
                                <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Check-Out:</strong><br>
                                <?php echo h($booking['check_out']); ?>
                            </p>
                        </div>
                    </div>
                    <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid var(--border-color);">
                        <p>
                            <strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Duration:</strong><br>
                            <span style="font-size: 1rem; font-weight: 600;"><?php echo $nights; ?> Night<?php echo $nights !== 1 ? 's' : ''; ?></span>
                        </p>
                    </div>
                </div>

                <!-- Room Charges -->
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>ACCOMMODATION</span>
                        <h3>Room Charges</h3>
                    </div>
                    <table style="width: 100%;">
                        <tr style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                            <td style="color: var(--secondary-color);">
                                <strong><?php echo h($room['name']); ?></strong><br>
                                <span style="font-size: 0.85rem;">$<?php echo number_format($room_rate, 2); ?> × <?php echo $nights; ?> night<?php echo $nights !== 1 ? 's' : ''; ?></span>
                            </td>
                            <td style="text-align: right; font-weight: 600;">$<?php echo number_format($room_total, 2); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Amenities -->
                <?php if (!empty($amenities_selected)): ?>
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>EXTRAS</span>
                        <h3>Selected Amenities</h3>
                    </div>
                    <table style="width: 100%;">
                        <?php foreach($amenities_selected as $am_id): ?>
                            <?php if (isset($amenities[$am_id])): ?>
                            <tr style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                                <td>
                                    <strong><?php echo h($amenities[$am_id]['name']); ?></strong><br>
                                    <span style="font-size: 0.85rem; color: var(--secondary-color);"><?php echo h($amenities[$am_id]['category']); ?></span>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    <?php if ($amenities[$am_id]['price'] > 0): ?>
                                        $<?php echo number_format($amenities[$am_id]['price'], 2); ?>
                                    <?php else: ?>
                                        <span style="color: var(--accent-color);">FREE</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Dining -->
                <?php if (!empty($dining_selected)): ?>
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>DINING</span>
                        <h3>Selected Dining Options</h3>
                    </div>
                    <table style="width: 100%;">
                        <?php foreach($dining_selected as $din_id): ?>
                            <?php if (isset($dining_options[$din_id])): ?>
                            <tr style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                                <td>
                                    <strong><?php echo h($dining_options[$din_id]['name']); ?></strong><br>
                                    <span style="font-size: 0.85rem; color: var(--secondary-color);"><?php echo h($dining_options[$din_id]['category']); ?></span>
                                </td>
                                <td style="text-align: right; font-weight: 600;">$<?php echo number_format($dining_options[$din_id]['price'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Summary -->
            <div class="checkout-sidebar">
                <div class="price-estimate" style="text-align: left; padding: 40px; margin: 0; position: sticky; top: 120px; border: 1px solid var(--border-color);">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>SETTLEMENT</span>
                        <h3>Total Amount Due</h3>
                    </div>

                    <!-- Room subtotal -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Room (<?php echo $nights; ?> night<?php echo $nights !== 1 ? 's' : ''; ?>)</span>
                        <span style="font-weight: 600;">$<?php echo number_format($room_total, 2); ?></span>
                    </div>

                    <!-- Service Fee -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Service Fee</span>
                        <span style="font-weight: 600;">$<?php echo number_format($service_fee, 2); ?></span>
                    </div>

                    <!-- Amenities total -->
                    <?php if ($amenities_total > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Amenities</span>
                        <span style="font-weight: 600;">$<?php echo number_format($amenities_total, 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Dining total -->
                    <?php if ($dining_total > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Dining</span>
                        <span style="font-weight: 600;">$<?php echo number_format($dining_total, 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Subtotal -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Subtotal</span>
                        <span style="font-weight: 600;">$<?php echo number_format($subtotal + $amenities_total + $dining_total, 2); ?></span>
                    </div>

                    <!-- Tax -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Tax (12%)</span>
                        <span style="font-weight: 600;">$<?php echo number_format($tax, 2); ?></span>
                    </div>

                    <!-- Grand Total -->
                    <div style="border-top: 2px solid var(--accent-color); padding-top: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <span style="font-family: var(--font-header); font-size: 1.2rem;">Grand Total</span>
                        <span style="font-family: var(--font-header); font-size: 2rem; color: var(--accent-color);">$<?php echo number_format($grand_total, 2); ?></span>
                    </div>

                    <!-- Action Buttons -->
                    <a href="checkout.php?booking_id=<?php echo $booking_id; ?>" class="btn-primary" style="display: block; text-align: center; width: 100%; padding: 15px; margin-bottom: 15px; text-decoration: none;">PROCEED TO CHECKOUT</a>
                    <a href="room.php?id=<?php echo $booking['room_id']; ?>" class="btn-secondary" style="display: block; text-align: center; width: 100%; padding: 15px; text-decoration: none;">EDIT RESERVATION</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
