<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$dining = [
    1 => ['name' => 'The Sky Lounge', 'category' => 'FINE DINING', 'image' => 'skylounge.jpg', 'desc' => 'Exquisite flavors paired with breathtaking panoramic views of the city skyline.'],
    2 => ['name' => 'Artisan Café', 'category' => 'CASUAL', 'image' => 'cafe.jpg', 'desc' => 'Freshly brewed coffee and hand-crafted pastries in a relaxed, cozy atmosphere.'],
    3 => ['name' => 'The Copper Bar', 'category' => 'BAR & GRILL', 'image' => 'copperbar.jpg', 'desc' => 'Premium spirits and signature cocktails served in an elegant, sophisticated setting.']
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
        
        <div class="room-gallery-placeholder" style="padding: 0;">
            <img src="pictures/<?php echo h($item['image']); ?>" alt="<?php echo h($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <div class="content" style="text-align: center; max-width: 700px; margin: 0 auto;">
            <p style="font-size: 1.2rem; font-family: var(--font-header); color: var(--primary-color); margin-bottom: 30px;">
                <?php echo h($item['desc']); ?>
            </p>
            <p>Indulge in a culinary journey at Ralmitrokij Hotel. Our <?php echo strtolower(h($item['name'])); ?> offers a sophisticated atmosphere combined with exceptional service and gourmet creations. We source only the finest ingredients to ensure every meal is a memorable experience for our guests.</p>
            
            <div style="margin-top: 50px;">
                <h3 style="font-family: var(--font-header); margin-bottom: 30px;">Guest Testimonials</h3>
                
                <div class="booking-card" style="text-align: left; margin-bottom: 20px;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"The <?php echo strtolower(h($item['name'])); ?> has amazing food and the staff are incredibly attentive! I had a wonderful dining experience."</p>
                    <p style="font-weight: 600;">— Juan D.</p>
                </div>
                
                <div class="booking-card" style="text-align: left; margin-bottom: 20px;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"Absolutely love the ambiance! The flavors are exquisite and the service is very accommodating. 10/10 would recommend!"</p>
                    <p style="font-weight: 600;">— Maria C.</p>
                </div>
                
                <div class="booking-card" style="text-align: left;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★☆</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"Great food and very clean! The whole experience was super relaxing and enjoyable."</p>
                    <p style="font-weight: 600;">— Pedro S.</p>
                </div>
            </div>

            <div style="margin-top: 50px;">
                <a href="index.php#dining" class="btn-secondary">BACK TO DINING</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
