<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$rooms = get_all_data('rooms');

// 1. Sort the rooms in the order of Standard, Deluxe, and Grand Suite
usort($rooms, function($a, $b) {
    $nameA = strtolower($a['name']);
    $nameB = strtolower($b['name']);
    
    // Assign structural weights for ordering
    $weightA = 99; 
    $weightB = 99;

    if (strpos($nameA, 'standard') !== false) $weightA = 1;
    elseif (strpos($nameA, 'deluxe') !== false) $weightA = 2;
    elseif (strpos($nameA, 'grand') !== false || strpos($nameA, 'suite') !== false) $weightA = 3;

    if (strpos($nameB, 'standard') !== false) $weightB = 1;
    elseif (strpos($nameB, 'deluxe') !== false) $weightB = 2;
    elseif (strpos($nameB, 'grand') !== false || strpos($nameB, 'suite') !== false) $weightB = 3;

    return $weightA <=> $weightB;
});

// 2. Helper function utilizing your specific image paths
function get_room_picture($room_name) {
    $name = strtolower($room_name);
    if (strpos($name, 'grand') !== false || strpos($name, 'suite') !== false) {
        return './pictures/grandsuite.jpg';
    } elseif (strpos($name, 'deluxe') !== false) {
        return './pictures/deluxe.jpg';
    } 
    // Default fallback to Standard Room
    return 'pictures/standard.jpg';
}
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>ACCOMMODATIONS</span>
            <h2>OUR ROOMS & SUITES</h2>
            <p>Choose from our selection of premium accommodations.</p>
        </div>
        
        <div class="grid-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php foreach($rooms as $room): ?>
                <?php 
                    // Use database image if available and valid; otherwise, apply your specific local paths
                    $image_src = (!empty($room['image']) && file_exists($room['image'])) ? h($room['image']) : get_room_picture($room['name']);
                ?>
                
                <div class="booking-card" style="position: relative; padding: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; background: #fff;">
                    
                    <div class="room-card-image" style="height: 240px; width: 100%; overflow: hidden; position: relative;">
                        <img src="<?php echo $image_src; ?>" alt="<?php echo h($room['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;">
                        
                        <div style="position: absolute; bottom: 15px; right: 15px; background: var(--primary-color, #111); color: #fff; padding: 8px 15px; font-family: var(--font-header); font-size: 0.9rem; letter-spacing: 1px; font-weight: 600;">
                            $<?php echo number_format($room['price'], 2); ?> / NT
                        </div>
                    </div>

                    <div class="room-card-details" style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div style="margin-bottom: 20px;">
                            <h3 style="font-family: var(--font-header); font-size: 1.4rem; color: var(--primary-color); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                                <?php echo h($room['name']); ?>
                            </h3>
                            <p style="font-size: 0.9rem; color: var(--secondary-color); line-height: 1.6; margin-bottom: 0;">
                                <?php echo h($room['description'] ?? 'Experience ultimate comfort and exquisite modern style in our premium luxury living spaces.'); ?>
                            </p>
                        </div>
                        
                        <div style="margin-top: auto; display: flex; gap: 10px;">
                            <a href="room.php?id=<?php echo $room['id']; ?>" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem; letter-spacing: 2px;">DETAILS</a>
                            <a href="room.php?id=<?php echo $room['id']; ?>#booking" class="btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem; letter-spacing: 2px;">BOOK NOW</a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>