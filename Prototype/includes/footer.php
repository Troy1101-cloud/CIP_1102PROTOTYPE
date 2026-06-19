<?php
// Define base URL to handle both root and Prototype paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($script_dir === '/' || $script_dir === '\\') {
    $script_dir = '';
}
$base_url = $protocol . '://' . $host . $script_dir . '/';
$root_index = $base_url . 'index.php';
?>
    </main>
    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-info">
                <h3>RALMITROKIJ HOTEL</h3>
                <p>123 Luxury Avenue, Manila, Philippines</p>
                        <p>Phone: +63 917 123 4567</p>
                        <p>Email: info@ralmitrokij.com</p>
            </div>
            <div class="footer-links">
                <h3>QUICK LINKS</h3>
                <ul>
                    <li><a href="<?php echo $root_index; ?>">Home</a></li>
                    <li><a href="<?php echo $base_url; ?>rooms.php">Rooms</a></li>
                    <li><a href="<?php echo $base_url; ?>contact.php">Contact Us</a></li>
                    <li><a href="<?php echo $base_url; ?>admin/login.php">Admin Login</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h3>FOLLOW US</h3>
                <div class="social-icons">
                    <a href="#">FACEBOOK</a> 
                    <a href="#">INSTAGRAM</a> 
                    <a href="#">TWITTER</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Ralmitrokij Hotel. All rights reserved. | <a href="<?php echo $base_url; ?>admin/login.php" class="admin-link">Admin Portal</a></p>
            </div>
        </div>
    </footer>



</body>
</html>
