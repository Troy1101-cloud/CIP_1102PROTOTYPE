<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$booking = get_booking($booking_id);

if (!$booking) {
    header('Location: index.php');
    exit;
}

$room = get_room($booking['room_id']);

if (!$room) {
    header('Location: index.php');
    exit;
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
$service_fee = 500;
$subtotal = $room_total + $service_fee;

// Calculate amenities charges
$amenities_selected = isset($booking['amenities']) ? (is_string($booking['amenities']) ? json_decode($booking['amenities'], true) : $booking['amenities']) : [];
$amenities_total = calculate_amenities_total($amenities_selected);

// Calculate dining charges
$dining_selected = isset($booking['dining']) ? (is_string($booking['dining']) ? json_decode($booking['dining'], true) : $booking['dining']) : [];
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
            <span>FINAL STEP</span>
            <h2>CHECKOUT</h2>
            <p>Please review your booking details and select a payment method.</p>
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 60px;">
            <div class="checkout-main">
                <div class="booking-card" style="position: static; padding: 40px; margin-bottom: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>RESERVATION</span>
                        <h3>Booking Summary</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div>
                            <p style="margin-bottom: 10px;"><strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Room:</strong><br><span style="font-family: var(--font-header); font-size: 1.2rem; color: var(--primary-color);"><?php echo h($room['name']); ?></span></p>
                            <p style="margin-bottom: 10px;"><strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Guest:</strong><br><?php echo h($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                            <p><strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Email:</strong><br><?php echo h($booking['email']); ?></p>
                        </div>
                        <div>
                            <p style="margin-bottom: 10px;"><strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Stay:</strong><br><?php echo h($booking['check_in']); ?> &mdash; <?php echo h($booking['check_out']); ?></p>
                            <p><strong style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Occupancy:</strong><br><?php echo h($booking['guests_adults'] ?? 0); ?> Adult(s), <?php echo h($booking['guests_children'] ?? 0); ?> Children</p>
                        </div>
                    </div>
                </div>

                <div class="booking-card" style="position: static; padding: 40px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>PAYMENT</span>
                        <h3>Select Payment Method</h3>
                    </div>
                    <form action="process_payment.php" method="POST" id="payment-form">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        
                        <div class="payment-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="visa" checked>
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">Visa</strong>
                            </label>
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="mastercard">
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">Mastercard</strong>
                            </label>
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="gcash">
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">GCash</strong>
                            </label>
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="maya">
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">Maya</strong>
                            </label>
                            <label class="payment-option" style="grid-column: 1 / -1; display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="bank_transfer">
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">Bank Transfer</strong>
                            </label>
                            <label class="payment-option" style="grid-column: 1 / -1; display: block; padding: 15px; border: 1px solid var(--border-color); cursor: pointer; border-radius: 8px;">
                                <input type="radio" name="payment_method" value="pay_at_hotel">
                                <strong style="margin-left: 10px; font-family: var(--font-header); font-size: 0.9rem;">Pay at Hotel upon Arrival</strong>
                            </label>
                        </div>

                        <style>
                            /* Zero-JS toggle for card details */
                            #card-details { display: none; margin-top: 30px; background: var(--bg-light); padding: 30px; border: 1px solid var(--border-color); }
                            #payment-form:has(input[value="visa"]:checked, input[value="mastercard"]:checked) #card-details { display: block; }
                        </style>
                        <div id="card-details" class="card-details">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label>Cardholder Name</label>
                                <input type="text" name="card_name" placeholder="Name on card">
                            </div>
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label>Card Number</label>
                                <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="text" name="card_expiry" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label>CVC</label>
                                    <input type="text" name="card_cvc" placeholder="XXX">
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="submit_payment" class="btn-primary" style="width: 100%; margin-top: 40px;">COMPLETE RESERVATION</button>
                    </form>
                </div>
            </div>

            <div class="checkout-sidebar">
                <div class="price-estimate" style="text-align: left; padding: 40px; margin: 0; position: sticky; top: 120px; border: 1px solid var(--border-color);">
                    <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                        <span>SETTLEMENT</span>
                        <h3>Total Amount</h3>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Room (<?php echo $nights; ?> night<?php echo $nights !== 1 ? 's' : ''; ?>)</span>
                        <span style="font-weight: 600;"><?php echo format_php($room_total); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Service Fee</span>
                        <span style="font-weight: 600;"><?php echo format_php($service_fee); ?></span>
                    </div>
                    <?php if ($amenities_total > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Amenities</span>
                        <span style="font-weight: 600;"><?php echo format_php($amenities_total); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($dining_total > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Dining</span>
                        <span style="font-weight: 600;"><?php echo format_php($dining_total); ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.9rem; color: var(--secondary-color);">Tax (12%)</span>
                        <span style="font-weight: 600;"><?php echo format_php($tax); ?></span>
                    </div>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 25px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-family: var(--font-header); font-size: 1.2rem;">Total</span>
                        <span style="font-family: var(--font-header); font-size: 1.8rem; color: var(--accent-color);"><?php echo format_php($grand_total); ?></span>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--secondary-color); margin-top: 20px; text-align: center;">
                        Thank you for choosing Ralmitrokij Hotel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>



<?php include 'includes/footer.php'; ?>
