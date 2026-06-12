<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get booking ID
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$bookings = get_all_data('bookings');
$booking = null;
$rooms = get_all_data('rooms');
$room = null;

// Find booking
foreach ($bookings as $b) {
    if ($b['id'] === $booking_id) {
        $booking = $b;
        break;
    }
}

// Find room
if ($booking) {
    foreach ($rooms as $r) {
        if ($r['id'] === $booking['room_id']) {
            $room = $r;
            break;
        }
    }
}

// Redirect if booking not found
if (!$booking || !$room) {
    header("Location: index.php");
    exit;
}

// Calculate booking details
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;

// Calculate pricing
$room_rate = (float)$room['price'];
$room_total = $room_rate * $nights;
$service_fee = 500;
$amenities_total = calculate_amenities_total($booking['amenities']);
$dining_total = calculate_dining_total($booking['dining']);
$subtotal = $room_total + $service_fee + $amenities_total + $dining_total;
// Discounts/Promo (we'll add a field for this later, for now 0)
$discount = 0;
$subtotal_after_discount = $subtotal - $discount;
$vat = $subtotal_after_discount * 0.12;
$total = $subtotal_after_discount + $vat;

// Generate booking reference number
$booking_ref = 'RM' . date('Ymd') . str_pad($booking_id, 4, '0', STR_PAD_LEFT);

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 1200px;">
        <div class="section-title" style="text-align: center; margin-bottom: 40px;">
            <span>CONFIRM YOUR BOOKING</span>
            <h2>Total Payment Report</h2>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <!-- Left Column -->
            <div>
                <!-- 1. Guest Information -->
                <div class="booking-card" style="padding: 30px; margin-bottom: 30px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                        <span>GUEST DETAILS</span>
                        <h3 style="margin-top: 5px;">Guest Information</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p><strong>Full Name:</strong><br><?php echo h($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                        <p><strong>Email Address:</strong><br><?php echo h($booking['email']); ?></p>
                        <p><strong>Mobile Number:</strong><br><?php echo h($booking['phone']); ?></p>
                        <p>
                            <strong>Number of Guests:</strong><br>
                            <?php echo h($booking['guests_adults'] ?? 0); ?> Adult(s), 
                            <?php echo h($booking['guests_children'] ?? 0); ?> Children
                        </p>
                        <p><strong>Check-in Date:</strong><br><?php echo h($booking['check_in']); ?></p>
                        <p><strong>Check-out Date:</strong><br><?php echo h($booking['check_out']); ?></p>
                    </div>
                    <?php if (!empty($booking['special_requests'])): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            <p><strong>Special Requests:</strong><br><?php echo h($booking['special_requests']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Room Information -->
                <div class="booking-card" style="padding: 30px; margin-bottom: 30px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                        <span>ROOM DETAILS</span>
                        <h3 style="margin-top: 5px;">Room Information</h3>
                    </div>
                    <p><strong>Room Type:</strong> <?php echo h($room['name']); ?></p>
                    <p><strong>Bed Type:</strong> <?php echo h(($room['name'] === 'Standard Room') ? 'Queen Bed' : 'King Bed'); ?></p>
                    <p><strong>Room Amenities:</strong></p>
                    <ul style="margin: 10px 0 10px 20px;">
                        <?php if (!empty($room['amenities']) && is_array($room['amenities'])): ?>
                            <?php foreach ($room['amenities'] as $amenity): ?>
                                <li><?php echo h($amenity); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Premium Wi-Fi</li>
                            <li>Room Service</li>
                            <li>Flat Screen TV</li>
                        <?php endif; ?>
                    </ul>
                    <p><strong>Price Per Night:</strong> <?php echo format_php($room['price']); ?></p>
                </div>

                <!-- 3. Payment Methods -->
                <div class="booking-card" style="padding: 30px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                        <span>PAYMENT</span>
                        <h3 style="margin-top: 5px;">Payment Methods</h3>
                    </div>
                    <form id="payment-methods-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="payment_method" value="gcash" checked>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/GCash_logo.svg/1200px-GCash_logo.svg.png" alt="GCash" style="width: 60px; height: auto;">
                            <strong>GCash</strong>
                        </label>
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="payment_method" value="maya">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Maya_logo.svg/1200px-Maya_logo.svg.png" alt="Maya" style="width: 60px; height: auto;">
                            <strong>Maya</strong>
                        </label>
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="payment_method" value="visa">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/1200px-Visa_Inc._logo.svg.png" alt="Visa" style="width: 60px; height: auto;">
                            <strong>Visa</strong>
                        </label>
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="payment_method" value="mastercard">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1200px-Mastercard-logo.svg.png" alt="Mastercard" style="width: 60px; height: auto;">
                            <strong>Mastercard</strong>
                        </label>
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; grid-column: 1 / -1;">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <strong>Bank Transfer</strong>
                        </label>
                        <label class="payment-option-card" style="padding: 15px; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; grid-column: 1 / -1;">
                            <input type="radio" name="payment_method" value="pay_at_hotel">
                            <strong>Pay at Hotel</strong>
                        </label>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- 4. Booking Price Summary -->
                <div class="booking-card" style="padding: 30px; margin-bottom: 30px; position: sticky; top: 100px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                        <span>SUMMARY</span>
                        <h3 style="margin-top: 5px;">Booking Price Summary</h3>
                    </div>
                    <div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                        <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Room Rate (<?php echo $nights; ?> night<?php echo $nights !== 1 ? 's' : ''; ?>)</span>
                            <span style="font-weight: 600;"><?php echo format_php($room_total); ?></span>
                        </p>
                        <?php if ($amenities_total > 0): ?>
                            <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Amenities</span>
                                <span style="font-weight: 600;"><?php echo format_php($amenities_total); ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if ($dining_total > 0): ?>
                            <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Dining</span>
                                <span style="font-weight: 600;"><?php echo format_php($dining_total); ?></span>
                            </p>
                        <?php endif; ?>
                        <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Service Fee</span>
                            <span style="font-weight: 600;"><?php echo format_php($service_fee); ?></span>
                        </p>
                        <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Subtotal</span>
                            <span style="font-weight: 600;"><?php echo format_php($subtotal); ?></span>
                        </p>
                        <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Discounts/Promo Codes</span>
                            <span style="font-weight: 600; color: #28a745;">-<?php echo format_php($discount); ?></span>
                        </p>
                        <p style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>VAT (12%)</span>
                            <span style="font-weight: 600;"><?php echo format_php($vat); ?></span>
                        </p>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <span style="font-size: 1.2rem; font-family: var(--font-header);">Total Amount</span>
                        <span style="font-size: 1.6rem; font-family: var(--font-header); color: var(--accent-color); font-weight: 600;"><?php echo format_php($total); ?></span>
                    </div>
                    <div style="background: var(--bg-light); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="font-weight: 600; margin-bottom: 5px;">Booking Reference Number</p>
                        <p style="font-size: 1.2rem; color: var(--primary-color);"><?php echo $booking_ref; ?></p>
                    </div>
                    <form action="process-submit-reservation.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="booking_ref" value="<?php echo $booking_ref; ?>">
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="gcash">
                        <div style="text-align: center;">
                            <button type="submit" name="submit_reservation" class="btn-primary" style="width: 100%; padding: 18px; font-size: 1.1rem;">
                                Submit Reservation
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 5. Booking Policies -->
                <div class="booking-card" style="padding: 30px;">
                    <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                        <span>POLICIES</span>
                        <h3 style="margin-top: 5px;">Booking Policies</h3>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <h4 style="margin-bottom: 8px; font-weight: 600;">Refund Policy</h4>
                        <p style="font-size: 0.9rem; color: var(--secondary-color);">Full refund if cancellation is made 7 days prior to check-in. 50% refund if cancelled 3-6 days before check-in. No refund within 2 days of check-in.</p>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <h4 style="margin-bottom: 8px; font-weight: 600;">Cancellation Policy</h4>
                        <p style="font-size: 0.9rem; color: var(--secondary-color);">Cancellations must be made via email or phone. Please include your booking reference number.</p>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <h4 style="margin-bottom: 8px; font-weight: 600;">No-show Policy</h4>
                        <p style="font-size: 0.9rem; color: var(--secondary-color);">Guests who do not arrive on check-in date without prior notice will be charged the full amount of the first night's stay.</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 8px; font-weight: 600;">Hotel House Rules</h4>
                        <ul style="font-size: 0.9rem; color: var(--secondary-color); margin-left: 20px;">
                            <li>Check-in time: 2:00 PM</li>
                            <li>Check-out time: 12:00 PM</li>
                            <li>Quiet hours: 10:00 PM - 6:00 AM</li>
                            <li>Smoking is only allowed in designated areas</li>
                            <li>Valid ID is required upon check-in</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .payment-option-card:has(input:checked) {
        border-color: var(--accent-color);
        background: rgba(201, 168, 76, 0.1);
    }
</style>

<script>
    // Update selected payment method in hidden field
    document.getElementById('payment-methods-form').addEventListener('change', function(e) {
        if (e.target.name === 'payment_method') {
            document.getElementById('selected_payment_method').value = e.target.value;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
