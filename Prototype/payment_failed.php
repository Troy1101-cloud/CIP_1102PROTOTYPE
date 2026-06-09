<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="text-align: center; max-width: 700px;">
        <div class="booking-card" style="position: static; padding: 60px; border-color: var(--error-color);">
            <div style="font-size: 4rem; color: var(--error-color); margin-bottom: 20px; font-family: var(--font-header);">&times;</div>
            <div class="section-title">
                <span>ERROR</span>
                <h2>Payment Failed</h2>
            </div>
            <p style="font-size: 1.1rem; color: var(--primary-color); margin-bottom: 30px;">We're sorry, but your payment could not be processed at this time. This may be due to incorrect card details, insufficient funds, or a temporary connection issue.</p>
            
            <div style="margin: 30px 0; padding: 25px; background: var(--bg-light); border-left: 4px solid var(--error-color); text-align: left;">
                <p style="font-size: 0.9rem;"><strong style="text-transform: uppercase; letter-spacing: 1px;">Status:</strong> Your reservation (ID: #<?php echo $booking_id; ?>) is still pending in our system. No charges have been made.</p>
            </div>
            
            <p style="color: var(--secondary-color); font-size: 0.9rem;">You can attempt the payment again or select a different method to secure your stay.</p>
            
            <div style="margin-top: 50px; display: flex; gap: 20px; justify-content: center;">
                <a href="checkout.php?booking_id=<?php echo $booking_id; ?>" class="btn-primary">TRY AGAIN</a>
                <a href="contact.php" class="btn-secondary">CONTACT SUPPORT</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
