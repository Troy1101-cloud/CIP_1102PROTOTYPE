<?php
require_once 'includes/db_connect.php';
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
            
            <!-- Schedule -->
            <div style="margin-top: 40px; padding: 20px; background: var(--bg-light); border-radius: 8px;">
                <h4 style="font-family: var(--font-header); margin-bottom: 10px;">Operating Hours</h4>
                <?php if ($id == 1): ?>
                    <p style="font-size: 1.1rem; color: var(--accent-color);">6:00 AM - 10:00 PM</p>
                <?php elseif ($id == 2): ?>
                    <p style="font-size: 1.1rem; color: var(--accent-color);">5:00 AM - 11:00 PM</p>
                <?php elseif ($id == 3): ?>
                    <p style="font-size: 1.1rem; color: var(--accent-color);">9:00 AM - 9:00 PM</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 50px;">
                <h3 style="font-family: var(--font-header); margin-bottom: 30px;">Guest Testimonials</h3>
                
                <div class="booking-card" style="text-align: left; margin-bottom: 20px;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"The <?php echo strtolower(h($item['name'])); ?> has top-notch equipment and the staff are incredibly helpful! I had a wonderful experience."</p>
                    <p style="font-weight: 600;">— Juan D.</p>
                </div>
                
                <div class="booking-card" style="text-align: left; margin-bottom: 20px;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★★</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"Absolutely love it! The facilities are well-maintained and the service is very accommodating. 10/10 would recommend!"</p>
                    <p style="font-weight: 600;">— Maria C.</p>
                </div>
                
                <div class="booking-card" style="text-align: left;">
                    <div style="color: #FFD700; font-size: 1.2rem; margin-bottom: 10px;">★★★★☆</div>
                    <p style="font-style: italic; margin-bottom: 10px;">"Great facility and very clean! The whole experience was super relaxing and enjoyable."</p>
                    <p style="font-weight: 600;">— Pedro S.</p>
                </div>
            </div>
            
            <div style="margin-top: 50px;">
                <a href="index.php#amenities" class="btn-secondary">BACK TO AMENITIES</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
