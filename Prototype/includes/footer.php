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
    <!-- Booking Modal -->
    <div id="booking-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 class="modal-title">Reserve a Room</h2>
            <form action="<?php echo $base_url; ?>process_booking.php" method="POST">
                <input type="hidden" name="room_id" value="">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div class="form-group">
                        <label for="modal_first_name">First Name</label>
                        <input type="text" id="modal_first_name" name="first_name" required placeholder="First name">
                    </div>
                    <div class="form-group">
                        <label for="modal_last_name">Last Name</label>
                        <input type="text" id="modal_last_name" name="last_name" required placeholder="Last name">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="modal_email">Email Address</label>
                    <input type="email" id="modal_email" name="email" required placeholder="email@example.com">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div class="form-group">
                        <label for="modal_check_in">Check-In</label>
                        <input type="date" id="modal_check_in" name="check_in" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_check_out">Check-Out</label>
                        <input type="date" id="modal_check_out" name="check_out" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="modal_guests_adults">Adults</label>
                    <select id="modal_guests_adults" name="guests_adults">
                        <option value="1">1 Adult</option>
                        <option value="2" selected>2 Adults</option>
                        <option value="3">3 Adults</option>
                        <option value="4">4 Adults</option>
                        <option value="5">5 or more Adults</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="modal_guests_children">Children</label>
                    <select id="modal_guests_children" name="guests_children">
                        <option value="0" selected>0 Children</option>
                        <option value="1">1 Child</option>
                        <option value="2">2 Children</option>
                        <option value="3">3 Children</option>
                        <option value="4">4 or more Children</option>
                    </select>
                </div>

                <div class="price-estimate">
                    <p>Estimated Total: <span style="color: var(--accent-color); font-size: 1.2rem;">[ CALCULATED AT CHECKOUT ]</span></p>
                    <small>NO PAYMENT REQUIRED AT THIS STEP</small>
                </div>
                
                <button type="submit" name="submit" class="btn-primary" style="width: 100%;">CONFIRM RESERVATION</button>
            </form>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
</body>
</html>
