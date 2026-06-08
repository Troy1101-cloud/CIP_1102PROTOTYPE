<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$method = isset($_GET['method']) ? $_GET['method'] : '';

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="text-align: center; max-width: 600px;">
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 40px; border-radius: 8px;">
            <h1 style="color: var(--success-color); font-size: 3rem;">&check;</h1>
            <h2>Reservation Confirmed!</h2>
            <p>Thank you for choosing Ralmitrokij Hotel. Your booking (ID: #<?php echo $booking_id; ?>) has been successfully processed.</p>
            
            <?php if ($method === 'cash'): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fffbeb; border: 1px solid #fef3c7;">
                    <p><strong>Note:</strong> You have selected Cash on Arrival. Please be ready to settle your payment at the front desk upon check-in.</p>
                </div>
            <?php endif; ?>

            <p style="margin-top: 20px;">A confirmation email has been sent to your inbox with all the details.</p>
            
            <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: center;">
                <a href="index.php" class="btn-primary">BACK TO HOME</a>
                <a href="payment_success.php?id=<?php echo $booking_id; ?>&action=print" class="btn-secondary" onclick="window.print(); return false;">PRINT RECEIPT</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
