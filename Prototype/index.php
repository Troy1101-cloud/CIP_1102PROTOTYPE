<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$rooms = get_all_data('rooms');
?>

<section id="home" class="hero">
    <div class="hero-content">
        <p class="hero-est" aria-hidden="true">&mdash; EST. 2026 &mdash;</p>
        <h1>Welcome to Ralmitrokij Hotel</h1>
        <p>Experience unparalleled luxury and comfort in the heart of the city.</p>
        
        <div class="hero-ctas">
            <a href="rooms.php" class="btn-primary">BOOK A ROOM</a>
            <a href="#amenities" class="btn-secondary">VIEW AMENITIES</a>
        </div>

        <form action="search_rooms.php" method="POST" class="booking-search" aria-label="Room Search" onsubmit="if(new Date(this.check_out.value) <= new Date(this.check_in.value)) { alert('Check-Out date must be after Check-In date.'); return false; } return true;">
            <div class="form-group">
                <label for="check-in">Check-In</label>
                <input type="date" id="check-in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="check-out">Check-Out</label>
                <input type="date" id="check-out" name="check_out" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="guests_adults">Adults</label>
                <select id="guests_adults" name="guests_adults">
                    <option value="1">1 Adult</option>
                    <option value="2" selected>2 Adults</option>
                    <option value="3">3 Adults</option>
                    <option value="4">4 Adults</option>
                    <option value="5">5 or more Adults</option>
                </select>
            </div>
            <div class="form-group">
                <label for="guests_children">Children</label>
                <select id="guests_children" name="guests_children">
                    <option value="0" selected>0 Children</option>
                    <option value="1">1 Child</option>
                    <option value="2">2 Children</option>
                    <option value="3">3 Children</option>
                    <option value="4">4 or more Children</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price_range">Max Price/Night (₱)</label>
                <input type="number" id="price_range" name="price_range" placeholder="Any Price" min="0" step="1" oninput="if(this.value < 0) this.value = Math.abs(this.value);">
            </div>
            <button type="submit" class="btn-primary">SEARCH</button>
        </form>
    </div>
</section>

<section id="rooms" class="section-padding">
    <div class="container">
        <div class="section-title">
            <span aria-hidden="true">01</span>
            <h2>FEATURED ROOMS</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/standard.jpg" alt="Standard Room interior with cozy setup" loading="lazy">
                    <span class="category-label">COMFORT</span>
                </div>
                <div class="room-content">
                    <h3>Standard Room</h3>
                    <p>A cozy and well-appointed room perfect for short stays and business travelers.</p>
                    <a href="room.php?id=1" class="btn-link" aria-label="View details for Standard Room">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="./pictures/deluxe.jpg" alt="Deluxe Room spacious interior" loading="lazy">
                    <span class="category-label">LUXURY</span>
                </div>
                <div class="room-content">
                    <h3>Deluxe Room</h3>
                    <p>Spacious accommodations with premium amenities for a truly relaxing experience.</p>
                    <a href="room.php?id=2" class="btn-link" aria-label="View details for Deluxe Room">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="./pictures/grandsuite.jpg" alt="Grand Suite with city views" loading="lazy">
                    <span class="category-label">PREMIUM</span>
                </div>
                <div class="room-content">
                    <h3>Grand Suite</h3>
                    <p>Our most opulent suite offering separate living areas and breathtaking city views.</p>
                    <a href="room.php?id=3" class="btn-link" aria-label="View details for Grand Suite">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section id="dining" class="section-padding bg-light" style="background-color: var(--bg-light);">
    <div class="container">
        <div class="section-title">
            <span aria-hidden="true">02</span>
            <h2>DINING & CUISINE</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/skylounge.jpg" alt="The Sky Lounge fine dining area" loading="lazy">
                    <span class="category-label">FINE DINING</span>
                </div>
                <div class="room-content">
                    <h3>The Sky Lounge</h3>
                    <p>Exquisite flavors paired with breathtaking panoramic views of the city skyline.</p>
                    <a href="dining.php?id=1" class="btn-link" aria-label="View details for The Sky Lounge">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/cafe.jpg" alt="Artisan Café with fresh pastries" loading="lazy">
                    <span class="category-label">CASUAL</span>
                </div>
                <div class="room-content">
                    <h3>Artisan Café</h3>
                    <p>Freshly brewed coffee and hand-crafted pastries in a relaxed, cozy atmosphere.</p>
                    <a href="dining.php?id=2" class="btn-link" aria-label="View details for Artisan Café">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/copperbar.jpg" alt="The Copper Bar elegant setup" loading="lazy">
                    <span class="category-label">BAR & GRILL</span>
                </div>
                <div class="room-content">
                    <h3>The Copper Bar</h3>
                    <p>Premium spirits and signature cocktails served in an elegant, sophisticated setting.</p>
                    <a href="dining.php?id=3" class="btn-link" aria-label="View details for The Copper Bar">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section id="amenities" class="section-padding">
    <div class="container">
        <div class="section-title">
            <span aria-hidden="true">03</span>
            <h2>HOTEL AMENITIES</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/infinity.jpg" alt="Infinity Pool with city view" loading="lazy">
                    <span class="category-label">RECREATION</span>
                </div>
                <div class="room-content">
                    <h3>Infinity Pool</h3>
                    <p>Relax and rejuvenate in our temperature-controlled rooftop infinity pool.</p>
                    <p style="font-size: 0.9rem; color: var(--accent-color); margin-top: 10px;"><strong>Hours:</strong> 6:00 AM - 10:00 PM</p>
                    <a href="amenity.php?id=1" class="btn-link" aria-label="View details for Infinity Pool">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/fitness.jpg" alt="Fitness Center with state-of-the-art equipment" loading="lazy">
                    <span class="category-label">WELLNESS</span>
                </div>
                <div class="room-content">
                    <h3>Fitness Center</h3>
                    <p>State-of-the-art equipment to help you maintain your wellness routine.</p>
                    <p style="font-size: 0.9rem; color: var(--accent-color); margin-top: 10px;"><strong>Hours:</strong> 5:00 AM - 11:00 PM</p>
                    <a href="amenity.php?id=2" class="btn-link" aria-label="View details for Fitness Center">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/spa.jpg" alt="Serenity Spa holistic treatment room" loading="lazy">
                    <span class="category-label">WELLNESS</span>
                </div>
                <div class="room-content">
                    <h3>Serenity Spa</h3>
                    <p>Holistic treatments designed to balance the mind, body, and spirit.</p>
                    <p style="font-size: 0.9rem; color: var(--accent-color); margin-top: 10px;"><strong>Hours:</strong> 9:00 AM - 9:00 PM</p>
                    <a href="amenity.php?id=3" class="btn-link" aria-label="View details for Serenity Spa">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>