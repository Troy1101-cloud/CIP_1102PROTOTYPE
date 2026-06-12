<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success_msg = "";
$errors = [];

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (empty($name)) $errors['name'] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid email is required.";
    if (empty($message)) $errors['message'] = "Message is required.";
    
    if (empty($errors)) {
        // Mock saving contact message
        $success_msg = "Thank you for your message! We will get back to you shortly.";
    }
}

include 'includes/header.php';
?>

<section class="section-padding">
    <div class="container" style="max-width: 1000px;">
        <div class="section-title">
            <span>GET IN TOUCH</span>
            <h2>CONTACT US</h2>
            <p>We'd love to hear from you. Please fill out the form below.</p>
        </div>

        <?php if ($success_msg): ?>
            <div style="background: var(--bg-light); color: var(--accent-color); padding: 30px; border: 1px solid var(--accent-color); margin-bottom: 50px; text-align: center; font-family: var(--font-header); font-size: 1.2rem;">
                <?php echo h($success_msg); ?>
            </div>
        <?php endif; ?>

        <div class="grid-3" style="grid-template-columns: 1fr 1.5fr; gap: 80px;">
            <div class="contact-info">
                <div class="section-title" style="text-align: left; margin-bottom: 30px;">
                    <span>VISIT US</span>
                    <h3>Our Location</h3>
                </div>
                <p style="font-size: 1.1rem; color: var(--primary-color); margin-bottom: 10px;">123 Luxury Avenue, Manila, Philippines</p>
                        <p><strong>Phone:</strong> +63 917 123 4567</p>
                <p><strong>Email:</strong> info@ralmitrokij.com</p>
                
                <div class="room-gallery-placeholder" style="height: 250px; margin-top: 40px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <img src="pictures/mapping.JPG" alt="Resort Location Details" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
            </div>
            
            <div class="contact-form">
                <div class="booking-card" style="position: static; padding: 40px;">
                    <form action="contact.php" method="POST">
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Your full name" class="<?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo isset($_POST['name']) ? h($_POST['name']) : ''; ?>">
                            <?php if (isset($errors['name'])): ?><div style="color: var(--error-color); font-size: 0.7rem; margin-top: 5px;"><?php echo $errors['name']; ?></div><?php endif; ?>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="email@example.com" class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
                            <?php if (isset($errors['email'])): ?><div style="color: var(--error-color); font-size: 0.7rem; margin-top: 5px;"><?php echo $errors['email']; ?></div><?php endif; ?>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 30px;">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="How can we help you?" class="<?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>"><?php echo isset($_POST['message']) ? h($_POST['message']) : ''; ?></textarea>
                            <?php if (isset($errors['message'])): ?><div style="color: var(--error-color); font-size: 0.7rem; margin-top: 5px;"><?php echo $errors['message']; ?></div><?php endif; ?>
                        </div>
                        
                        <button type="submit" name="submit" class="btn-primary" style="width: 100%;">SEND MESSAGE</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>