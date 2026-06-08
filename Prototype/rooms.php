<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$rooms = get_all_data('rooms');
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>OUR ROOMS & SUITES</h2>
            <p>Choose from our selection of premium accommodations.</p>
        </div>
        
        <div class="grid-3">
            <?php foreach($rooms as $room): ?>
                <?php echo render_room_card($room); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
