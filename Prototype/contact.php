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
    <div class="container" style="max-width: 800px;">
        <div class="section-title">
            <h2>CONTACT US</h2>
            <p>We'd love to hear from you. Please fill out the form below.</p>
        </div>

        <?php if ($success_msg): ?>
            <div style="background: #f0fdf4; color: var(--success-color); padding: 20px; border: 1px solid #bbf7d0; margin-bottom: 30px; border-radius: 4px;">
                <?php echo h($success_msg); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <div class="contact-info">
                <h3>Our Location</h3>
                <p>123 Luxury Avenue, Paradise City</p>
                <p><strong>Phone:</strong> +1 (234) 567-890</p>
                <p><strong>Email:</strong> info@ralmitrokij.com</p>
                
                <div style="background: #eee; height: 200px; margin-top: 30px; display: flex; align-items: center; justify-content: center;">
                    [ MAP PLACEHOLDER ]
                </div>
            </div>
            
            <div class="contact-form">
                <form action="contact.php" method="POST">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="<?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo isset($_POST['name']) ? h($_POST['name']) : ''; ?>">
                        <?php if (isset($errors['name'])): ?><div class="error-message"><?php echo $errors['name']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
                        <?php if (isset($errors['email'])): ?><div class="error-message"><?php echo $errors['email']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" class="<?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>"><?php echo isset($_POST['message']) ? h($_POST['message']) : ''; ?></textarea>
                        <?php if (isset($errors['message'])): ?><div class="error-message"><?php echo $errors['message']; ?></div><?php endif; ?>
                    </div>
                    
                    <button type="submit" name="submit" class="btn-primary" style="width: 100%;">SUBMIT</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
