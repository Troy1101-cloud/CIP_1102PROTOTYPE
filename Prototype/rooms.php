<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$all_rooms = get_all_data('rooms');
$grouped_rooms = [];
foreach ($all_rooms as $room) {
    $key = $room['category'] . '_' . $room['price'];
    if (!isset($grouped_rooms[$key])) {
        // Use the category name as the display name for the group
        $room['display_name'] = $room['category'];
        $grouped_rooms[$key] = $room;
    }
}
$rooms = array_values($grouped_rooms);
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

    if ($weightA === $weightB) {
        return $a['price'] <=> $b['price'];
    }
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
        
        <div class="grid-3">
            <?php foreach($rooms as $room): ?>
                <?php 
                    $image_src = (!empty($room['image']) && file_exists($room['image'])) ? h($room['image']) : get_room_picture($room['name']);
                ?>
                <article class="room-card" style="display: flex; flex-direction: column; border-radius: var(--radius-md, 8px); overflow: hidden;">
                    <div class="room-image" style="height: 250px;">
                        <img src="<?php echo $image_src; ?>" alt="<?php echo h($room['name']); ?> interior" loading="lazy">
                        <span class="category-label" style="top: auto; bottom: 15px; right: 15px; left: auto; background: var(--primary-color); color: var(--white);">
                            <?php echo format_php($room['price']); ?> / NT
                        </span>
                    </div>
                    <div class="room-content" style="flex-grow: 1; display: flex; flex-direction: column;">
                        <h3 style="text-transform: uppercase; letter-spacing: 1px;"><?php echo h($room['display_name']); ?></h3>
                        <p style="flex-grow: 1; margin-bottom: 10px;"><?php echo h($room['description'] ?? 'Experience ultimate comfort and exquisite modern style in our premium luxury living spaces.'); ?></p>
                        <?php 
                            $free_inclusions = get_free_room_amenities($room['amenities'] ?? '');
                            if (!empty($free_inclusions)):
                        ?>
                            <p style="font-size: 0.8rem; color: #b39b56; margin-top: 0; font-weight: 600; letter-spacing: 0.5px;">
                                <i class="fas fa-gift" style="margin-right: 5px;"></i> Complimentary: <?php echo h(implode(', ', $free_inclusions)); ?>
                            </p>
                        <?php endif; ?>
                        <?php 
                            $stats = get_room_group_stats($room['category'], $room['price']);
                            $is_available = $stats['available'] > 0;
                            
                            $badge_html = '';
                            if ($stats['maintenance'] > 0 && $stats['available'] == 0 && $stats['booked'] == 0) {
                                $badge_html = '<p style="color: #e74c3c; font-weight: bold; font-size: 0.8rem; margin-top: 5px;"><i class="fas fa-tools"></i> Under Maintenance</p>';
                                $button_text = 'MAINTENANCE';
                            } elseif ($stats['available'] == 0) {
                                $badge_html = '<p style="color: #e74c3c; font-weight: bold; font-size: 0.8rem; margin-top: 5px;"><i class="fas fa-calendar-times"></i> Fully Booked</p>';
                                $button_text = 'ROOM FULL';
                            } elseif ($stats['maintenance'] > 0 || $stats['total'] > 1) {
                                // Show "X left!" if few rooms are left
                                $badge_html = '';
                                $button_text = 'BOOK NOW';
                            } else {
                                $button_text = 'BOOK NOW';
                            }
                        ?>
                        <?php echo $badge_html; ?>
                        <div style="display: flex; gap: 10px; margin-top: 15px;">
                            <a href="room.php?category=<?php echo urlencode($room['category']); ?>&price=<?php echo $room['price']; ?>" class="btn-secondary" style="flex: 1; padding: 10px; font-size: 0.75rem;" aria-label="View details for <?php echo h($room['display_name']); ?>">DETAILS</a>
                            <?php if ($is_available): ?>
                                <a href="room.php?category=<?php echo urlencode($room['category']); ?>&price=<?php echo $room['price']; ?>#booking" class="btn-primary" style="flex: 1; padding: 10px; font-size: 0.75rem;" aria-label="Book <?php echo h($room['display_name']); ?> now"><?php echo $button_text; ?></a>
                            <?php else: ?>
                                <button class="btn-primary" style="flex: 1; padding: 10px; font-size: 0.75rem; background-color: #ccc; border-color: #ccc; cursor: not-allowed;" disabled><?php echo $button_text; ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>