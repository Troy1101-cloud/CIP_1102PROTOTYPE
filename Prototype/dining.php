<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$dining = [
    1 => ['name' => 'The Sky Lounge', 'category' => 'FINE DINING', 'image' => 'dining-1.jpg', 'desc' => 'Exquisite flavors paired with breathtaking panoramic views of the city skyline.'],
    2 => ['name' => 'Artisan Café', 'category' => 'CASUAL', 'image' => 'dining-1.jpg', 'desc' => 'Freshly brewed coffee and hand-crafted pastries in a relaxed, cozy atmosphere.'],
    3 => ['name' => 'The Copper Bar', 'category' => 'BAR & GRILL', 'image' => 'dining-1.jpg', 'desc' => 'Premium spirits and signature cocktails served in an elegant, sophisticated setting.']
];

$item = isset($dining[$id]) ? $dining[$id] : $dining[1];

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 900px;">
        <div class="section-title">
            <span><?php echo h($item['category']); ?></span>
            <h2><?php echo h($item['name']); ?></h2>
        </div>
        
        <div class="room-gallery-placeholder">
            [ MEDIA: <?php echo h($item['image']); ?> ]
        </div>
        
        <div class="content" style="text-align: center; max-width: 700px; margin: 0 auto;">
            <p style="font-size: 1.2rem; font-family: var(--font-header); color: var(--primary-color); margin-bottom: 30px;">
                <?php echo h($item['desc']); ?>
            </p>
            <p>Indulge in a culinary journey at Ralmitrokij Hotel. Our <?php echo strtolower(h($item['name'])); ?> offers a sophisticated atmosphere combined with exceptional service and gourmet creations. We source only the finest ingredients to ensure every meal is a memorable experience for our guests.</p>
            
            <div style="margin-top: 50px;">
                <a href="index.php#dining" class="btn-secondary">BACK TO DINING</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
