<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$method = isset($_GET['method']) ? $_GET['method'] : '';

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="text-align: center; max-width: 700px;">
        <div class="booking-card" style="position: static; padding: 60px; border-color: var(--accent-color);">
            <div style="font-size: 4rem; color: var(--accent-color); margin-bottom: 20px; font-family: var(--font-header);">&check;</div>
            <div class="section-title">
                <span>SUCCESS</span>
                <h2>Reservation Confirmed!</h2>
            </div>
            <p style="font-size: 1.1rem; color: var(--primary-color); margin-bottom: 30px;">Thank you for choosing Ralmitrokij Hotel. Your booking (ID: #<?php echo $booking_id; ?>) has been successfully processed and our team is preparing for your arrival.</p>
            
            <?php if ($method === 'cash'): ?>
                <div style="margin: 30px 0; padding: 25px; background: var(--bg-light); border-left: 4px solid var(--accent-color); text-align: left;">
                    <p style="font-size: 0.9rem;"><strong style="text-transform: uppercase; letter-spacing: 1px;">Note:</strong> You have selected Cash on Arrival. Please be ready to settle your payment at the front desk upon check-in.</p>
                </div>
            <?php endif; ?>

            <p style="color: var(--secondary-color); font-size: 0.9rem;">A confirmation email has been sent to your inbox with all the details and your digital check-in pass.</p>
            
            <div style="margin-top: 50px; display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="totalpaymentpage.php?booking_id=<?php echo $booking_id; ?>" class="btn-secondary">VIEW PAYMENT SUMMARY</a>
                <a href="index.php" class="btn-primary">BACK TO HOME</a>
                <a href="payment_success.php?id=<?php echo $booking_id; ?>&action=print" class="btn-secondary" onclick="window.print(); return false;">PRINT RECEIPT</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
