<?php
require_once 'includes/session_init.php';
require_once 'includes/db_connect.php';
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

$category = isset($_GET['category']) ? $_GET['category'] : 'Standard Room';
$price = isset($_GET['price']) ? (float)$_GET['price'] : 4500;
$check_in_date = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out_date = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));

$rooms = get_all_data('rooms');
$room = null;

foreach($rooms as $r) {
    if ($r['category'] === $category && (float)$r['price'] === $price) {
        $room = $r;
        $room['display_name'] = $room['category'];
        break;
    }
}

if (!$room) {
    header('Location: rooms.php');
    exit;
}

$is_available = is_room_group_available($room['category'], $room['price'], $check_in_date, $check_out_date);

// Determine image path: Checks if database value exists, otherwise applies your specific local paths
$image_src = (!empty($room['image']) && file_exists($room['image'])) ? h($room['image']) : get_room_picture($room['category']);

include 'includes/header.php';
?>

<section class="room-detail section-padding">
    <div class="container">
        <div class="room-header">
            <span class="category-label"><?php echo h($room['category'] ?? 'Luxury'); ?></span>
            <h1><?php echo h($room['display_name']); ?></h1>
        </div>
        
        <div class="room-gallery-placeholder" style="padding: 0; height: 400px; border-radius: 8px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo $image_src; ?>" alt="<?php echo h($room['display_name']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        </div>

        <div class="room-content-wrapper" style="max-width: 900px; margin: 0 auto;">
            <div class="room-info">
                <div class="section-title" style="text-align: left; margin-bottom: 20px;">
                    <span>ROOM DETAILS</span>
                    <h2>Description</h2>
                </div>
                <p style="line-height: 1.8; color: var(--secondary-color);"><?php echo h($room['description']); ?></p>
                
                <div class="section-title" style="text-align: left; margin-bottom: 20px; margin-top: 30px;">
                    <span>COMFORTS</span>
                    <h2>Amenities</h2>
                </div>
                <ul class="amenities-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; list-style: none; padding: 0;">
                    <?php if(!empty($room['amenities'])): ?>
                        <?php 
                        $amenities_list = is_array($room['amenities']) ? $room['amenities'] : array_map('trim', explode(',', $room['amenities']));
                        foreach($amenities_list as $amenity): 
                            if(empty($amenity)) continue;
                        ?>
                            <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> <?php echo h($amenity); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Premium Wi-Fi</li>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Room Service</li>
                        <li style="color: var(--secondary-color);"><strong style="color: var(--accent-color); margin-right: 10px;">&check;</strong> Flat Screen TV</li>
                    <?php endif; ?>
                </ul>
                
                
                <div class="section-title" style="text-align: left; margin-bottom: 20px; margin-top: 30px;">
                    <span>FEEDBACK</span>
                    <h2>Guest Testimonials</h2>
                </div>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="booking-card" style="text-align: left;">
                        <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                        <p style="font-style: italic; color: var(--secondary-color); margin-bottom: 10px;">"Absolutely wonderful stay! The room was spacious and perfectly accommodating for our family. Highly recommended!"</p>
                        <p style="font-weight: 600;">— The Smith Family</p>
                    </div>
                    
                    <div class="booking-card" style="text-align: left;">
                        <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                        <p style="font-style: italic; color: var(--secondary-color); margin-bottom: 10px;">"A luxurious and comfortable experience. The amenities were top-notch and the staff was incredibly welcoming."</p>
                        <p style="font-weight: 600;">— Michael T.</p>
                    </div>
                </div>

            </div>
            
            <div id="booking" class="booking-section" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border-color);">
                <div class="booking-card" style="padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h2 style="text-align: center; margin-bottom: 15px; font-family: var(--font-header);">Reserve this Room</h2>
                    <p style="font-size: 2.2rem; font-family: var(--font-header); text-align: center; margin-bottom: 40px; color: var(--primary-color);"><?php echo format_php($room['price']); ?> <span style="font-size: 1rem; font-family: var(--font-body); font-weight: 400; color: var(--secondary-color);">/ night</span></p>
                    
                    <?php if (isset($_SESSION['booking_errors'])): ?>
                        <div style="background: rgba(255, 0, 0, 0.1); color: #e74c3c; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #e74c3c; font-size: 0.9rem;">
                            <strong>Please fix the following errors:</strong>
                            <ul style="margin-top: 10px; padding-left: 20px;">
                                <?php foreach ($_SESSION['booking_errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php 
                        unset($_SESSION['booking_errors']);
                        unset($_SESSION['booking_data']); // clear data for now
                    endif; 
                    ?>
                    
                    <div id="js-booking-error" style="display: none; background: rgba(255, 0, 0, 0.1); color: #e74c3c; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #e74c3c; font-size: 0.9rem;">
                        <strong>Room Full!</strong> The room is fully booked for these dates. Please select different dates.
                    </div>
                    
                    <form action="process_booking.php" method="POST" aria-label="Room Booking Form" id="booking-form">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($room['category']); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($room['price']); ?>">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required placeholder="Your first name">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required placeholder="Your last name">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required placeholder="email@example.com">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required placeholder="+63 917 123 4567">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <?php 
                                $get_check_in = isset($_GET['check_in']) && !empty($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : '';
                                $get_check_out = isset($_GET['check_out']) && !empty($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : '';
                            ?>
                            <div class="form-group">
                                <label for="check_in">Check-In</label>
                                <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $get_check_in; ?>">
                            </div>
                            <div class="form-group">
                                <label for="check_out">Check-Out</label>
                                <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $get_check_out; ?>">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                            <div class="form-group">
                                <label for="guests_adults">Adults</label>
                                <select id="guests_adults" name="guests_adults">
                                    <option value="1">1 Adult</option>
                                    <option value="2" selected>2 Adults</option>
                                    <option value="3">3 Adults</option>
                                    <option value="4">4 Adults</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="guests_children">Children</label>
                                <select id="guests_children" name="guests_children">
                                    <option value="0" selected>0 Children</option>
                                    <option value="1">1 Child</option>
                                    <option value="2">2 Children</option>
                                    <option value="3">3 Children</option>
                                    <option value="4">4 or more Children</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 30px; padding: 20px; background: var(--bg-light); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <label style="margin-bottom: 15px; display: block;">Add Amenities</label>
                            <?php 
                                $free_inclusions = get_free_room_amenities($room['amenities'] ?? '');
                                $stmt = $pdo->query("SELECT * FROM addons");
                                $all_addons = $stmt->fetchAll();
                                
                                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">';
                                foreach ($all_addons as $addon):
                                    if ($addon['category'] !== 'room_category'):
                                        $is_included = in_array($addon['name'], $free_inclusions) || (float)$addon['price'] === 0.0;
                            ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fdfdfd; border: 1px solid #eee; border-radius: 6px;">
                                    <div style="display: flex; align-items: center;">
                                        <?php if ($is_included): ?>
                                            <i class="fas fa-check" style="color: #2ecc71; margin-right: 10px;"></i>
                                            <span style="font-size: 0.9rem; font-weight: 500;"><?php echo h($addon['name']); ?></span>
                                        <?php else: ?>
                                            <input type="checkbox" id="addon_<?php echo $addon['id']; ?>" name="addons[]" value="<?php echo htmlspecialchars($addon['name']); ?>" data-price="<?php echo htmlspecialchars($addon['price']); ?>" class="price-modifier" style="width: auto; height: auto; margin-right: 10px; cursor: pointer;">
                                            <label for="addon_<?php echo $addon['id']; ?>" style="font-size: 0.9rem; cursor: pointer; margin: 0; text-transform: none; font-weight: 500;"><?php echo h($addon['name']); ?></label>
                                        <?php endif; ?>
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 600; color: <?php echo $is_included ? '#2ecc71' : 'var(--primary-color)'; ?>;">
                                        <?php echo $is_included ? 'INCLUDED' : '+₱' . number_format($addon['price'], 0); ?>
                                    </span>
                                </div>
                            <?php 
                                    endif;
                                endforeach;
                                echo '</div>';
                            ?>
                        </div>

                        <div class="form-group" style="margin-bottom: 30px; padding: 20px; background: var(--bg-light); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <label style="margin-bottom: 15px;">Add Dining Options</label>
                            <?php 
                            $global_dining = get_dining_pricing();
                            foreach($global_dining as $id => $addon): 
                            ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <?php if((float)$addon['price'] > 0): ?>
                                    <div style="display: flex; align-items: center;">
                                        <input type="checkbox" id="dining_<?php echo $id; ?>" name="dining[]" value="<?php echo $id; ?>" data-price="<?php echo htmlspecialchars($addon['price']); ?>" class="price-modifier" style="width: auto; min-height: 24px; margin-right: 10px; cursor: pointer;">
                                        <label for="dining_<?php echo $id; ?>" style="cursor: pointer; margin: 0; text-transform: none; letter-spacing: normal;"><?php echo h($addon['name']); ?></label>
                                    </div>
                                    <span style="color: var(--accent-color); font-weight: 600;">+<?php echo format_php($addon['price']); ?></span>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #2ecc71; margin-right: 10px; font-weight:bold;">&check;</span>
                                        <label style="margin: 0; text-transform: none; letter-spacing: normal;"><?php echo h($addon['name']); ?></label>
                                    </div>
                                    <span style="color: #2ecc71; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px;">COMPLIMENTARY</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="special_requests">Special Requests (Optional)</label>
                            <textarea id="special_requests" name="special_requests" rows="3" placeholder="Any special requests (e.g., extra bed, early check-in)..."></textarea>
                        </div>

                        <div class="price-estimate" style="text-align: center; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <p style="font-weight: 600; margin-bottom: 5px;">Estimated Total: <span id="total-price" style="color: var(--accent-color); font-size: 1.4rem;"><?php echo format_php($room['price']); ?></span></p>
                            <small style="color: var(--secondary-color); font-size: 0.8rem;" aria-hidden="true">Proceed to payment selection on the next step</small>
                        </div>
                        
                        <button type="submit" name="submit" class="btn-primary" style="width: 100%;">CONFIRM RESERVATION</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const basePrice = <?php echo (float)$room['price']; ?>;
    const category = '<?php echo htmlspecialchars($room['category'], ENT_QUOTES); ?>';
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const modifiers = document.querySelectorAll('.price-modifier');
    const totalPriceDisplay = document.getElementById('total-price');
    const errorDiv = document.getElementById('js-booking-error');
    const submitBtn = document.querySelector('button[name="submit"]');
    
    function calculateTotal() {
        let inDate = new Date(checkInInput.value);
        let outDate = new Date(checkOutInput.value);
        let nights = 1; // Default
        
        if (!isNaN(inDate.getTime()) && !isNaN(outDate.getTime()) && outDate > inDate) {
            const diffTime = Math.abs(outDate - inDate);
            nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }
        
        let total = basePrice * nights;
        
        modifiers.forEach(modifier => {
            if (modifier.checked) {
                total += parseFloat(modifier.getAttribute('data-price') || 0);
            }
        });
        
        // Format as PHP Currency (₱)
        totalPriceDisplay.innerHTML = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function checkAvailability() {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        
        if (checkIn && checkOut) {
            if (new Date(checkOut) <= new Date(checkIn)) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<strong>Error:</strong> Check-out date must be after check-in date.';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                return;
            }

            fetch(`api_check_availability.php?category=${encodeURIComponent(category)}&price=${encodeURIComponent(basePrice)}&check_in=${encodeURIComponent(checkIn)}&check_out=${encodeURIComponent(checkOut)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && !data.available) {
                        errorDiv.style.display = 'block';
                        errorDiv.innerHTML = '<strong>Room Full!</strong> The room is fully booked for these dates. Please select different dates.';
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.5';
                        submitBtn.style.cursor = 'not-allowed';
                    } else {
                        errorDiv.style.display = 'none';
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }
                })
                .catch(error => console.error('Error checking availability:', error));
        }
    }
    
    checkInInput.addEventListener('change', () => { calculateTotal(); checkAvailability(); });
    checkOutInput.addEventListener('change', () => { calculateTotal(); checkAvailability(); });
    
    modifiers.forEach(modifier => {
        modifier.addEventListener('change', calculateTotal);
    });
    
    // Initial calculation and check
    calculateTotal();
    if (checkInInput.value && checkOutInput.value) {
        checkAvailability();
    }
});
</script>

<?php include 'includes/footer.php'; ?>