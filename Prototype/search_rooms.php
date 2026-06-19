<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Wrapped in function_exists to prevent PHP fatal errors across different pages
if (!function_exists('get_room_picture')) {
    function get_room_picture($room_name) {
        $name = strtolower($room_name);
        if (strpos($name, 'grand') !== false || strpos($name, 'suite') !== false) {
            return 'pictures/grandsuite.jpg';
        } elseif (strpos($name, 'deluxe') !== false) {
            return 'pictures/deluxe.jpg';
        } 
        // Default fallback to Standard Room
        return 'pictures/standard.jpg';
    }
}

// Always fetch and sort rooms so they display on GET requests too
$all_rooms = get_all_data('rooms');
$grouped_rooms = [];
foreach ($all_rooms as $room) {
    $key = $room['category'] . '_' . $room['price'];
    if (!isset($grouped_rooms[$key])) {
        $room['display_name'] = $room['category'];
        $grouped_rooms[$key] = $room;
    }
}
$search_results = array_values($grouped_rooms);

// Sort the search results in the exact order: Standard, Deluxe, and Grand Suite
usort($search_results, function($a, $b) {
    $nameA = strtolower($a['name']);
    $nameB = strtolower($b['name']);
    
    $weightA = 99; 
    $weightB = 99;

    if (strpos($nameA, 'standard') !== false) $weightA = 1;
    elseif (strpos($nameA, 'deluxe') !== false) $weightA = 2;
    elseif (strpos($nameA, 'grand') !== false || strpos($nameA, 'suite') !== false) $weightA = 3;

    if (strpos($nameB, 'standard') !== false) $weightB = 1;
    elseif (strpos($nameB, 'deluxe') !== false) $weightB = 2;
    elseif (strpos($nameB, 'grand') !== false || strpos($nameB, 'suite') !== false) $weightB = 3;

    if ($weightA === $weightB) {
        return $a['price'] <=> $b['price'];
    }
    return $weightA <=> $weightB;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, we'd filter rooms based on availability for these dates
    // For the prototype, all rooms are returned, but we will filter by price_range if specified
    
    if (isset($_POST['price_range']) && trim($_POST['price_range']) !== '' && trim($_POST['price_range']) !== 'any') {
        $max_price = (float)$_POST['price_range'];
        $search_results = array_filter($search_results, function($room) use ($max_price) {
            return (float)$room['price'] <= $max_price;
        });
    }
}

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>CURATED SELECTION</span>
            <h2>SEARCH RESULTS</h2>
            <p>Available rooms for your selected dates.</p>
        </div>
        
        <div class="grid-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php if (!empty($search_results)): ?>
                <?php foreach($search_results as $room): ?>
                    <?php 
                        // FORCED LOCAL PICTURES: Bypasses database entirely to ensure cross-host consistency
                        $image_src = get_room_picture($room['name']);
                    ?>
                    
                    <div class="booking-card" style="position: relative; padding: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; background: #fff;">
                        
                        <div class="room-card-image" style="height: 240px; width: 100%; overflow: hidden; position: relative;">
                            <img src="<?php echo $image_src; ?>" alt="<?php echo h($room['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;">
                            
                            <div style="position: absolute; bottom: 15px; right: 15px; background: var(--primary-color, #111); color: #fff; padding: 8px 15px; font-family: var(--font-header); font-size: 0.9rem; letter-spacing: 1px; font-weight: 600;">
                                <?php echo format_php($room['price']); ?> / NT
                            </div>
                        </div>

                        <div class="room-card-details" style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div style="margin-bottom: 20px;">
                                <h3 style="font-family: var(--font-header); font-size: 1.4rem; color: var(--primary-color); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                                    <?php echo h($room['display_name']); ?>
                                </h3>
                                <p style="font-size: 0.9rem; color: var(--secondary-color); line-height: 1.6; margin-bottom: 10px;">
                                    <?php echo h($room['description'] ?? 'Experience ultimate comfort and exquisite modern style in our premium luxury living spaces.'); ?>
                                </p>
                                <?php 
                                    $free_inclusions = get_free_room_amenities($room['amenities'] ?? '');
                                    if (!empty($free_inclusions)):
                                ?>
                                    <p style="font-size: 0.8rem; color: #b39b56; margin-top: 0; font-weight: 600; letter-spacing: 0.5px;">
                                        <i class="fas fa-gift" style="margin-right: 5px;"></i> Complimentary: <?php echo h(implode(', ', $free_inclusions)); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: auto;">
                                <?php 
                                    $check_in_date = $_POST['check_in'] ?? null;
                                    $check_out_date = $_POST['check_out'] ?? null;
                                    $stats = get_room_group_stats($room['category'], $room['price'], $check_in_date, $check_out_date);
                                    $is_available = $stats['available'] > 0;
                                    
                                    $badge_html = '';
                                    if ($stats['maintenance'] > 0 && $stats['available'] == 0 && $stats['booked'] == 0) {
                                        $badge_html = '<p style="color: #e74c3c; font-weight: bold; font-size: 0.8rem; margin-top: 5px;"><i class="fas fa-tools"></i> Under Maintenance</p>';
                                        $button_text = 'MAINTENANCE';
                                    } elseif ($stats['available'] == 0) {
                                        $badge_html = '<p style="color: #e74c3c; font-weight: bold; font-size: 0.8rem; margin-top: 5px;"><i class="fas fa-calendar-times"></i> Fully Booked</p>';
                                        $button_text = 'ROOM FULL';
                                    } elseif ($stats['maintenance'] > 0 || $stats['total'] > 1) {
                                        $badge_html = '';
                                        $button_text = 'BOOK NOW';
                                    } else {
                                        $button_text = 'BOOK NOW';
                                    }
                                ?>
                                <?php echo $badge_html; ?>
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <a href="room.php?category=<?php echo urlencode($room['category']); ?>&price=<?php echo $room['price']; ?>&check_in=<?php echo urlencode($check_in_date ?? ''); ?>&check_out=<?php echo urlencode($check_out_date ?? ''); ?>" class="btn-secondary" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem; letter-spacing: 1px;">VIEW DETAILS</a>
                                    <?php if ($is_available): ?>
                                        <a href="room.php?category=<?php echo urlencode($room['category']); ?>&price=<?php echo $room['price']; ?>&check_in=<?php echo urlencode($check_in_date ?? ''); ?>&check_out=<?php echo urlencode($check_out_date ?? ''); ?>#booking" class="btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem; letter-spacing: 1px;"><?php echo $button_text; ?></a>
                                    <?php else: ?>
                                        <button class="btn-primary" style="flex: 1; padding: 12px; font-size: 0.85rem; letter-spacing: 1px; background-color: #ccc; border-color: #ccc; cursor: not-allowed;" disabled><?php echo $button_text; ?></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; font-size: 1.1rem; color: var(--secondary-color); padding: 40px 0;">No room found based on price range.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>