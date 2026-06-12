<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_ref = isset($_GET['booking_ref']) ? $_GET['booking_ref'] : '';

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 800px; text-align: center;">
        <div style="padding: 60px 40px; background: var(--bg-light); border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
            <h2 style="font-family: var(--font-header); margin-bottom: 10px;">Reservation Confirmed!</h2>
            <p style="font-size: 1.1rem; color: var(--secondary-color); margin-bottom: 30px;">
                Thank you for your reservation at Ralmitrokij Hotel!
            </p>
            
            <?php if ($booking_ref): ?>
                <div style="background: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; display: inline-block;">
                    <p style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-color); margin-bottom: 5px;">Booking Reference</p>
                    <p style="font-size: 2rem; font-family: var(--font-header); color: var(--accent-color);"><?php echo h($booking_ref); ?></p>
                </div>
            <?php endif; ?>

            <p style="margin-bottom: 30px;">
                A confirmation email has been sent to your email address. Please save your booking reference number for your records.
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn-primary" style="text-decoration: none; padding: 12px 30px;">Back to Home</a>
                <a href="rooms.php" class="btn-secondary" style="text-decoration: none; padding: 12px 30px;">Book Another Room</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
