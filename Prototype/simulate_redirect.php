<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$method = isset($_GET['method']) ? $_GET['method'] : 'ewallet';

// Map method to display name
$display_name = "External Payment Gateway";
if ($method === 'ewallet') $display_name = "GCash / Maya Portal";
if ($method === 'credit_card') $display_name = "Bank Secure Pay";

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="text-align: center; max-width: 600px;">
        <div class="booking-card" style="padding: 60px; border-style: dashed;">
            <div class="spinner" style="width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid var(--accent-color); border-radius: 50%; margin: 0 auto 30px; animation: spin 1s linear infinite;"></div>
            
            <div class="section-title">
                <span>SIMULATION</span>
                <h2>Redirecting to <?php echo $display_name; ?></h2>
            </div>
            
            <p style="color: var(--secondary-color); margin-bottom: 20px;">We are safely transferring you to our partner's secure payment page to complete your transaction.</p>
            
            <div style="background: var(--bg-light); padding: 15px; border-radius: 4px; font-size: 0.8rem; color: var(--secondary-color); margin-bottom: 30px;">
                <p><strong>Booking Reference:</strong> #<?php echo $booking_id; ?></p>
                <p><strong>Amount to Pay:</strong> [ CALCULATING SECURELY ]</p>
            </div>

            <p id="status-text" style="font-weight: 600; color: var(--primary-color);">Please do not refresh this page...</p>
            
            <form id="auto-form" action="process_payment.php" method="POST" style="display: none;">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="payment_method" value="<?php echo $method; ?>">
                <input type="hidden" name="simulation_complete" value="1">
                <input type="hidden" name="submit_payment" value="1">
            </form>
        </div>
    </div>
</section>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
    // Simulate the "Redirect" delay
    setTimeout(() => {
        document.getElementById('status-text').innerText = "Processing transaction...";
        setTimeout(() => {
            document.getElementById('auto-form').submit();
        }, 1500);
    }, 2000);
</script>

<?php include 'includes/footer.php'; ?>
