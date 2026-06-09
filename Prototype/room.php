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
        
        <div class="room-gallery-placeholder" style="padding: 0;">
            <img src="pictures/<?php echo h($room['image']); ?>" alt="<?php echo h($room['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 60px;">
            <div class="room-info">
                <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                    <span>ROOM DETAILS</span>
                    <h2>Description</h2>
                </div>
                <p><?php echo h($room['description']); ?></p>
                
                <div class="section-title" style="text-align: left; margin-bottom: 30px; margin-top: 50px;">
                    <span>COMFORTS</span>
                    <h2>Amenities</h2>
                </div>
                <ul class="amenities-list">
                    <?php foreach($room['amenities'] as $amenity): ?>
                        <li>&check; <?php echo h($amenity); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="section-title" style="text-align: left; margin-bottom: 30px; margin-top: 50px;">
                    <span>INVESTMENT</span>
                    <h2>Price Breakdown</h2>
                </div>
                <p>Base Rate: $<?php echo h($room['price']); ?> / night</p>
                <p>Service Fee: $25</p>
                <p>Tax: 12%</p>
            </div>
            
            <div id="booking" class="booking-sidebar">
                <div class="booking-card">
                    <h3>Reserve this Room</h3>
                    <p style="font-size: 2rem; font-family: var(--font-header); text-align: center; margin-bottom: 30px;">$<?php echo h($room['price']); ?> <span style="font-size: 0.9rem; font-family: var(--font-body); font-weight: 400; color: var(--secondary-color);">/ night</span></p>
                    
                    <form action="process_booking.php" method="POST">
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required placeholder="Your first name">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required placeholder="Your last name">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="email@example.com">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="+1 (555) 000-0000">
                        </div>
                        
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="check_in">Check-In</label>
                                <input type="date" id="check_in" name="check_in" required>
                            </div>
                            <div class="form-group">
                                <label for="check_out">Check-Out</label>
                                <input type="date" id="check_out" name="check_out" required>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 30px;">
                            <label for="guests">Guests</label>
                            <select id="guests" name="guests">
                                <option value="1">1 Adult</option>
                                <option value="2">2 Adults</option>
                                <option value="3">3 Adults</option>
                            </select>
                        </div>

                        <div class="price-estimate">
                            <p>Estimated Total: <span id="total-price" style="color: var(--accent-color); font-size: 1.2rem;">$<?php echo h($room['price']); ?></span></p>
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
