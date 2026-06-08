<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$amenities = [
    1 => ['name' => 'Infinity Pool', 'category' => 'RECREATION', 'image' => 'amenity-1.jpg', 'desc' => 'Relax and rejuvenate in our temperature-controlled rooftop infinity pool.'],
    2 => ['name' => 'Fitness Center', 'category' => 'WELLNESS', 'image' => 'amenity-1.jpg', 'desc' => 'State-of-the-art equipment to help you maintain your wellness routine.'],
    3 => ['name' => 'Serenity Spa', 'category' => 'WELLNESS', 'image' => 'amenity-1.jpg', 'desc' => 'Holistic treatments designed to balance the mind, body, and spirit.']
];

$item = isset($amenities[$id]) ? $amenities[$id] : $amenities[1];

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 800px;">
        <div class="section-title">
            <span><?php echo h($item['category']); ?></span>
            <h2><?php echo h($item['name']); ?></h2>
        </div>
        
        <div style="background: #eee; height: 400px; margin-bottom: 30px; display: flex; align-items: center; justify-content: center;">
            [ MEDIA PLACEHOLDER: <?php echo h($item['image']); ?> ]
        </div>
        
        <div class="content">
            <p><?php echo h($item['desc']); ?></p>
            <p style="margin-top: 20px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </div>
        
        <div style="margin-top: 40px;">
            <a href="index.php#amenities" class="btn-secondary">BACK TO AMENITIES</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
