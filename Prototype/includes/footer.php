    </main>
    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-info">
                <h3>RALMITROKIJ HOTEL</h3>
                <p>123 Luxury Avenue, Paradise City</p>
                <p>Phone: +1 (234) 567-890</p>
                <p>Email: info@ralmitrokij.com</p>
            </div>
            <div class="footer-links">
                <h3>QUICK LINKS</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="rooms.php">Rooms</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="admin/login.php">Admin Login</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h3>FOLLOW US</h3>
                <div class="social-icons">
                    <a href="#" style="color: rgba(255,255,255,0.6); margin-right: 20px; font-size: 0.8rem; letter-spacing: 2px;">FACEBOOK</a> 
                    <a href="#" style="color: rgba(255,255,255,0.6); margin-right: 20px; font-size: 0.8rem; letter-spacing: 2px;">INSTAGRAM</a> 
                    <a href="#" style="color: rgba(255,255,255,0.6); font-size: 0.8rem; letter-spacing: 2px;">TWITTER</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Ralmitrokij Hotel. All rights reserved. | <a href="admin/login.php" style="color: inherit; border-bottom: 1px solid rgba(255,255,255,0.2);">Admin Portal</a></p>
            </div>
        </div>
    </footer>
    <!-- Booking Modal -->
    <div id="booking-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 class="modal-title">Reserve a Room</h2>
            <form action="process_booking.php" method="POST">
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
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="modal_guests">Guests</label>
                    <select id="modal_guests" name="guests">
                        <option value="1">1 Adult</option>
                        <option value="2">2 Adults</option>
                        <option value="3">3 Adults</option>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
