<?php
require_once 'includes/db.php';
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

$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, we'd filter rooms based on availability for these dates
    // For the prototype, we just return all rooms to simulate a "search result"
    $search_results = get_all_data('rooms');
    
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

        return $weightA <=> $weightB;
    });
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
                            
                            <div style="margin-top: auto;">
                                <a href="room.php?id=<?php echo $room['id']; ?>" class="btn-primary" style="display: block; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem; letter-spacing: 2px;">VIEW DETAILS</a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; font-size: 1.1rem; color: var(--secondary-color); padding: 40px 0;">No rooms found for your criteria. Please try different dates.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>