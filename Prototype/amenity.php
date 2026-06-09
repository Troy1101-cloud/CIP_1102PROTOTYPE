<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$amenities = [
    1 => ['name' => 'Infinity Pool', 'category' => 'RECREATION', 'image' => 'infinity.jpg', 'desc' => 'Relax and rejuvenate in our temperature-controlled rooftop infinity pool.'],
    2 => ['name' => 'Fitness Center', 'category' => 'WELLNESS', 'image' => 'fitness.jpg', 'desc' => 'State-of-the-art equipment to help you maintain your wellness routine.'],
    3 => ['name' => 'Serenity Spa', 'category' => 'WELLNESS', 'image' => 'spa.jpg', 'desc' => 'Holistic treatments designed to balance the mind, body, and spirit.']
];

$item = isset($amenities[$id]) ? $amenities[$id] : $amenities[1];

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 900px;">
        <div class="section-title">
            <span><?php echo h($item['category']); ?></span>
            <h2><?php echo h($item['name']); ?></h2>
        </div>
        
        <div class="room-gallery-placeholder" style="padding: 0;">
            <img src="pictures/<?php echo h($item['image']); ?>" alt="<?php echo h($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <div class="content" style="text-align: center; max-width: 700px; margin: 0 auto;">
            <p style="font-size: 1.2rem; font-family: var(--font-header); color: var(--primary-color); margin-bottom: 30px;">
                <?php echo h($item['desc']); ?>
            </p>
            <p>Experience the epitome of luxury at Ralmitrokij Hotel. Our <?php echo strtolower(h($item['name'])); ?> is designed to provide an unparalleled experience of comfort and sophistication. Whether you are seeking relaxation or a rigorous workout, our facilities are curated to meet the highest standards of our esteemed guests.</p>
            
            <div style="margin-top: 50px;">
                <a href="index.php#amenities" class="btn-secondary">BACK TO AMENITIES</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
