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

$rooms = get_all_data('rooms');
$room = null;
foreach($rooms as $r) {
    if ($r['id'] == $booking['room_id']) {
        $room = $r;
        break;
    }
}

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>CHECKOUT</h2>
            <p>Please review your booking details and select a payment method.</p>
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr;">
            <div class="checkout-main">
                <div class="booking-summary" style="border: 1px solid var(--border-color); padding: 30px; margin-bottom: 30px;">
                    <h3>Booking Summary</h3>
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <p><strong>Room:</strong> <?php echo h($room['name']); ?></p>
                            <p><strong>Guest:</strong> <?php echo h($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo h($booking['email']); ?></p>
                        </div>
                        <div style="flex: 1;">
                            <p><strong>Check-In:</strong> <?php echo h($booking['check_in']); ?></p>
                            <p><strong>Check-Out:</strong> <?php echo h($booking['check_out']); ?></p>
                            <p><strong>Guests:</strong> <?php echo h($booking['guests']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="payment-selection" style="border: 1px solid var(--border-color); padding: 30px;">
                    <h3>Select Payment Method</h3>
                    <form action="process_payment.php" method="POST" id="payment-form">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        
                        <div class="payment-options" style="margin-top: 20px;">
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="credit_card" checked>
                                <strong>Credit Card</strong>
                                <p style="font-size: 0.8rem; color: var(--secondary-color);">Visa, Mastercard, AMEX</p>
                            </label>
                            
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="ewallet">
                                <strong>E-Wallets</strong>
                                <p style="font-size: 0.8rem; color: var(--secondary-color);">GCash, Maya, MariBank</p>
                            </label>
                            
                            <label class="payment-option" style="display: block; padding: 15px; border: 1px solid var(--border-color); margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="cash">
                                <strong>Cash on Arrival</strong>
                                <p style="font-size: 0.8rem; color: var(--secondary-color);">Pay at the front desk upon check-in</p>
                            </label>
                        </div>

                        <div id="card-details" class="card-details" style="margin-top: 20px;">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Cardholder Name</label>
                                <input type="text" name="card_name" placeholder="Name on card">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Card Number</label>
                                <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                            </div>
                            <div style="display: flex; gap: 10px;">
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

                        <button type="submit" name="submit_payment" class="btn-primary" style="width: 100%; margin-top: 30px;">COMPLETE PAYMENT</button>
                    </form>
                </div>
            </div>

            <div class="checkout-sidebar">
                <div class="price-breakdown" style="border: 1px solid var(--border-color); padding: 30px; background: var(--bg-light);">
                    <h3>Total Amount</h3>
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Room Rate</span>
                        <span>$<?php echo h($room['price']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Service Fee</span>
                        <span>$25.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Tax (12%)</span>
                        <span>$<?php echo h($room['price'] * 0.12); ?></span>
                    </div>
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem;">
                        <span>Total</span>
                        <span>$<?php echo h($room['price'] + 25 + ($room['price'] * 0.12)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Simple toggle for payment details
    const paymentForm = document.getElementById('payment-form');
    const cardDetails = document.getElementById('card-details');
    
    paymentForm.addEventListener('change', (e) => {
        if (e.target.name === 'payment_method') {
            if (e.target.value === 'credit_card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
