<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, we'd filter rooms based on availability for these dates
    // For the prototype, we just return all rooms to simulate a "search result"
    $search_results = get_all_data('rooms');
}

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Search Results</h2>
            <p>Available rooms for your selected dates.</p>
        </div>
        
        <div class="grid-3">
            <?php if (!empty($search_results)): ?>
                <?php foreach($search_results as $room): ?>
                    <?php echo render_room_card($room); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center;">No rooms found for your criteria. Please try different dates.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
