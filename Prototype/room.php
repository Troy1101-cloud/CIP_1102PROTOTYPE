<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$rooms = get_all_data('rooms');
$room = null;

foreach($rooms as $r) {
    if ($r['id'] === $room_id) {
        $room = $r;
        break;
    }
}

if (!$room) {
    header('Location: rooms.php');
    exit;
}

include 'includes/header.php';
?>

<section class="room-detail section-padding">
    <div class="container">
        <div class="room-header">
            <span class="category-label"><?php echo h($room['category']); ?></span>
            <h1><?php echo h($room['name']); ?></h1>
        </div>
        
        <div class="room-gallery-placeholder" style="background: #eee; height: 400px; margin-bottom: 40px; display: flex; align-items: center; justify-content: center;">
            <p>[ LARGE MEDIA PLACEHOLDER: <?php echo h($room['image']); ?> ]</p>
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr;">
            <div class="room-info">
                <h3>Description</h3>
                <p><?php echo h($room['description']); ?></p>
                
                <h3 style="margin-top: 30px;">Amenities</h3>
                <ul class="amenities-list">
                    <?php foreach($room['amenities'] as $amenity): ?>
                        <li>&check; <?php echo h($amenity); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <h3 style="margin-top: 30px;">Price Breakdown</h3>
                <p>Base Rate: $<?php echo h($room['price']); ?> / night</p>
                <p>Service Fee: $25</p>
                <p>Tax: 12%</p>
            </div>
            
            <div id="booking" class="booking-sidebar">
                <div class="booking-card" style="border: 1px solid var(--border-color); padding: 30px; position: sticky; top: 100px;">
                    <h3>Reserve this Room</h3>
                    <p style="font-size: 1.5rem; font-weight: 700; margin-bottom: 20px;">$<?php echo h($room['price']); ?> <span style="font-size: 0.9rem; font-weight: 400;">/ night</span></p>
                    
                    <form action="process_booking.php" method="POST">
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label for="check_in">Check-In</label>
                                <input type="date" id="check_in" name="check_in" required>
                            </div>
                            <div class="form-group">
                                <label for="check_out">Check-Out</label>
                                <input type="date" id="check_out" name="check_out" required>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="guests">Guests</label>
                            <select id="guests" name="guests">
                                <option value="1">1 Adult</option>
                                <option value="2">2 Adults</option>
                                <option value="3">3 Adults</option>
                            </select>
                        </div>

                        <div class="price-estimate" style="background: var(--bg-light); padding: 15px; margin-bottom: 20px; font-size: 0.9rem;">
                            <p>Estimated Total: <span id="total-price" style="font-weight: 700;">$<?php echo h($room['price']); ?></span></p>
                            <small>NO PAYMENT REQUIRED AT THIS STEP</small>
                        </div>
                        
                        <button type="submit" name="submit" class="btn-primary" style="width: 100%;">CONFIRM RESERVATION</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
