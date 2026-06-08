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
            <a href="index.php#dining" class="btn-secondary">BACK TO DINING</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
