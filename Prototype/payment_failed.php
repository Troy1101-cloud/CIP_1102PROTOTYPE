<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="text-align: center; max-width: 600px;">
        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 40px; border-radius: 8px;">
            <h1 style="color: var(--error-color); font-size: 3rem;">&times;</h1>
            <h2>Payment Failed</h2>
            <p>We're sorry, but your payment could not be processed at this time. This may be due to incorrect card details or a temporary connection issue.</p>
            
            <p style="margin-top: 20px;">Your reservation (ID: #<?php echo $booking_id; ?>) is still pending. You can try again or select a different payment method.</p>
            
            <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: center;">
                <a href="checkout.php?booking_id=<?php echo $booking_id; ?>" class="btn-primary">TRY AGAIN</a>
                <a href="contact.php" class="btn-secondary">CONTACT SUPPORT</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
