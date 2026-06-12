<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Helper function utilizing your specific image paths based on room name
function get_room_picture($room_name) {
    $name = strtolower($room_name);
    if (strpos($name, 'grand') !== false || strpos($name, 'suite') !== false) {
        return './pictures/grandsuite.jpg';
    } elseif (strpos($name, 'deluxe') !== false) {
        return './pictures/deluxe.jpg';
    } 
    // Default fallback to Standard Room
    return './pictures/standard.jpg';
}

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

// Determine image path: Checks if database value exists, otherwise applies your specific local paths
$image_src = (!empty($room['image']) && file_exists($room['image'])) ? h($room['image']) : get_room_picture($room['name']);

include 'includes/header.php';
?>

<section class="room-detail section-padding">
    <div class="container">
        <div class="room-header">
            <span class="category-label"><?php echo h($room['category'] ?? 'Luxury'); ?></span>
            <h1><?php echo h($room['name']); ?></h1>
        </div>
        
        <div class="room-gallery-placeholder" style="padding: 0; height: 400px; border-radius: 8px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo $image_src; ?>" alt="<?php echo h($room['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        </div>

        <div class="grid-3" style="display: grid; grid-template-columns: 2fr 1fr; gap: 60px;">
            <div class="room-info">
                <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                    <span>ROOM DETAILS</span>
                    <h2>Description</h2>
                </div>
                <p style="line-height: 1.8; color: var(--secondary-color);"><?php echo h($room['description']); ?></p>
                
                <div class="section-title" style="text-align: left; margin-bottom: 30px; margin-top: 50px;">
                    <span>COMFORTS</span>
                    <h2>Amenities</h2>
                </div>
                <ul class="amenities-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; list-style: none; padding: 0;">
                    <?php if(!empty($room['amenities']) && is_array($room['amenities'])): ?>
                        <?php foreach($room['amenities'] as $amenity): ?>
                            <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> <?php echo h($amenity); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Premium Wi-Fi</li>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Room Service</li>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Flat Screen TV</li>
                    <?php endif; ?>
                </ul>
                

            </div>
            
            <div id="booking" class="booking-sidebar">
                <div class="booking-card" style="position: sticky; top: 100px; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h3 style="text-align: center; margin-bottom: 15px; font-family: var(--font-header);">Reserve this Room</h3>
                    <p style="font-size: 2rem; font-family: var(--font-header); text-align: center; margin-bottom: 30px; color: var(--primary-color);"><?php echo format_php($room['price']); ?> <span style="font-size: 0.9rem; font-family: var(--font-body); font-weight: 400; color: var(--secondary-color);">/ night</span></p>
                    
                    <form action="process_booking.php" method="POST">
                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="first_name" style="display: block; margin-bottom: 8px; font-weight: 600;">First Name</label>
                            <input type="text" id="first_name" name="first_name" required placeholder="Your first name" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="last_name" style="display: block; margin-bottom: 8px; font-weight: 600;">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required placeholder="Your last name" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="email@example.com" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="+63 917 123 4567" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label for="check_in" style="display: block; margin-bottom: 8px; font-weight: 600;">Check-In</label>
                                <input type="date" id="check_in" name="check_in" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="check_out" style="display: block; margin-bottom: 8px; font-weight: 600;">Check-Out</label>
                                <input type="date" id="check_out" name="check_out" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 30px;">
                            <label for="guests_adults" style="display: block; margin-bottom: 8px; font-weight: 600;">Adults</label>
                            <select id="guests_adults" name="guests_adults" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px;">
                                <option value="1">1 Adult</option>
                                <option value="2" selected>2 Adults</option>
                                <option value="3">3 Adults</option>
                                <option value="4">4 Adults</option>
                                <option value="5">5 or more Adults</option>
                            </select>
                            <label for="guests_children" style="display: block; margin-bottom: 8px; font-weight: 600;">Children</label>
                            <select id="guests_children" name="guests_children" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="0" selected>0 Children</option>
                                <option value="1">1 Child</option>
                                <option value="2">2 Children</option>
                                <option value="3">3 Children</option>
                                <option value="4">4 or more Children</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 25px; padding: 20px; background: #f9f9f9; border: 1px solid #eaeaea; border-radius: 4px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 15px;">Add Amenities</label>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="amenity_1" name="amenities[]" value="1" style="margin-right: 10px; cursor: pointer;">
                                    <label for="amenity_1" style="cursor: pointer; margin: 0;">
                                        <strong>Infinity Pool</strong>
                                    </label>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="amenity_2" name="amenities[]" value="2" style="margin-right: 10px; cursor: pointer;">
                                    <label for="amenity_2" style="cursor: pointer; margin: 0;">
                                        <strong>Fitness Center</strong>
                                    </label>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="amenity_3" name="amenities[]" value="3" style="margin-right: 10px; cursor: pointer;">
                                    <label for="amenity_3" style="cursor: pointer; margin: 0;">
                                        <strong>Serenity Spa</strong>
                                    </label>
                                </div>
                                <span style="color: var(--accent-color); font-weight: 600;">+<?php echo format_php(2500); ?></span>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #eaeaea; border-radius: 4px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 15px;">Add Dining Options</label>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="dining_1" name="dining[]" value="1" style="margin-right: 10px; cursor: pointer;">
                                    <label for="dining_1" style="cursor: pointer; margin: 0;">
                                        <strong>The Sky Lounge</strong>
                                    </label>
                                </div>
                                <span style="color: var(--accent-color); font-weight: 600;">+<?php echo format_php(1500); ?></span>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="dining_2" name="dining[]" value="2" style="margin-right: 10px; cursor: pointer;">
                                    <label for="dining_2" style="cursor: pointer; margin: 0;">
                                        <strong>Artisan Café</strong>
                                    </label>
                                </div>
                                <span style="color: var(--accent-color); font-weight: 600;">+<?php echo format_php(800); ?></span>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" id="dining_3" name="dining[]" value="3" style="margin-right: 10px; cursor: pointer;">
                                    <label for="dining_3" style="cursor: pointer; margin: 0;">
                                        <strong>The Copper Bar</strong>
                                    </label>
                                </div>
                                <span style="color: var(--accent-color); font-weight: 600;">+<?php echo format_php(1000); ?></span>
                            </div>
                        </div>

                        <div class="price-estimate" style="text-align: center; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid #eaeaea;">
                            <p style="font-weight: 600; margin-bottom: 5px;">Estimated Total: <span id="total-price" style="color: var(--accent-color); font-size: 1.4rem;"><?php echo format_php($room['price']); ?></span></p>
                            <small style="color: var(--secondary-color); font-size: 0.8rem;">NO PAYMENT REQUIRED AT THIS STEP</small>
                        </div>
                        
                        <button type="submit" name="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1rem; font-weight: 600; letter-spacing: 1px; border: none; cursor: pointer;">CONFIRM RESERVATION</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>