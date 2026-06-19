<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$method = isset($_GET['method']) ? $_GET['method'] : '';

include 'includes/header.php';
?>

<section class="section-padding">
<?php
$booking = get_booking($booking_id);
$room = $booking ? get_room($booking['room_id']) : null;

if (!$booking || !$room) {
    echo "<p>Booking details not found.</p>";
} else {
?>
    <div class="container" style="text-align: center; max-width: 800px;">
        <div class="booking-card" id="receipt-card" style="position: static; padding: 50px; border-top: 5px solid var(--accent-color); text-align: left;">
            <div style="text-align: center; margin-bottom: 40px;">
                <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 10px; font-family: var(--font-header);">&check;</div>
                <div class="section-title" style="margin-bottom: 10px;">
                    <span>SUCCESS</span>
                    <h2>Reservation Confirmed!</h2>
                </div>
                <p style="color: var(--secondary-color); font-size: 0.95rem;">Thank you for choosing Ralmitrokij Hotel. Your booking has been successfully processed.</p>
            </div>

            <div style="background: var(--bg-light); padding: 30px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 30px;">
                <h3 style="font-family: var(--font-header); margin-bottom: 20px; font-size: 1.5rem; text-transform: uppercase; letter-spacing: 2px; text-align: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">Official Receipt</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div>
                        <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Booking Reference</strong><br><span style="font-family: var(--font-header); font-size: 1.1rem; color: var(--primary-color);"><?php echo htmlspecialchars($booking['booking_ref']); ?></span></p>
                        <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Guest Name</strong><br><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                        <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Email</strong><br><?php echo htmlspecialchars($booking['email']); ?></p>
                    </div>
                    <div>
                        <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Date & Time Issued</strong><br><?php echo date('F j, Y, g:i a', strtotime($booking['created_at'])); ?></p>
                        <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Payment Method</strong><br><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($booking['payment_method'] ?? 'N/A'))); ?></p>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-bottom: 20px;">
                    <h4 style="font-family: var(--font-header); margin-bottom: 15px; font-size: 1.1rem;">Stay Details</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Room Type</strong><br><?php echo htmlspecialchars($room['name']); ?></p>
                            <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Occupancy</strong><br><?php echo htmlspecialchars($booking['guests_adults']); ?> Adult(s), <?php echo htmlspecialchars($booking['guests_children']); ?> Children</p>
                        </div>
                        <div>
                            <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Check-In</strong><br><?php echo date('F j, Y', strtotime($booking['check_in'])); ?> (2:00 PM)</p>
                            <p style="margin-bottom: 8px;"><strong style="color: var(--secondary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Check-Out</strong><br><?php echo date('F j, Y', strtotime($booking['check_out'])); ?> (12:00 NN)</p>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px dashed var(--border-color); padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-family: var(--font-header); font-size: 1.3rem; text-transform: uppercase; letter-spacing: 1px;">Total Amount Paid</strong>
                    <strong style="font-family: var(--font-header); font-size: 1.8rem; color: var(--accent-color);">₱<?php echo number_format($booking['total_cost'], 2); ?></strong>
                </div>
            </div>
            
            <?php if ($method === 'cash' || $method === 'pay_at_hotel' || $booking['payment_method'] === 'pay_at_hotel'): ?>
                <div style="margin: 0 0 30px 0; padding: 20px; background: #fff3cd; color: #856404; border-left: 4px solid #ffeeba; text-align: left; border-radius: 4px;">
                    <p style="font-size: 0.9rem; margin: 0;"><strong style="text-transform: uppercase; letter-spacing: 1px;">Note:</strong> You have selected Pay at Hotel. Please present this receipt and settle your payment at the front desk upon check-in to fully confirm your stay.</p>
                </div>
            <?php endif; ?>

            <p style="color: var(--secondary-color); font-size: 0.85rem; text-align: center; margin-bottom: 40px;">A confirmation email has been sent to your inbox with these details.</p>
            
            <div style="display: flex; gap: 20px; justify-content: center;" class="no-print">
                <a href="index.php" class="btn-primary">BACK TO HOME</a>
                <button onclick="window.print();" class="btn-secondary" style="cursor: pointer;">PRINT RECEIPT</button>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-card, #receipt-card * {
                visibility: visible;
            }
            #receipt-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                border: none !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
<?php } ?>
</section>

<?php include 'includes/footer.php'; ?>
