<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$rooms = get_all_data('rooms');
?>

<section id="home" class="hero">
    <div class="hero-content">
        <p class="hero-est">&mdash; EST. 2024 &mdash;</p>
        <h1>Welcome to Ralmitrokij Hotel</h1>
        <p>Experience unparalleled luxury and comfort in the heart of the city.</p>
        
        <div class="hero-ctas">
            <a href="rooms.php" class="btn-primary">BOOK A ROOM</a>
            <a href="#amenities" class="btn-secondary">VIEW AMENITIES</a>
        </div>

        <form action="search_rooms.php" method="POST" class="booking-search">
            <div class="form-group">
                <label for="check-in">Check-In</label>
                <input type="date" id="check-in" name="check_in" required>
            </div>
            <div class="form-group">
                <label for="check-out">Check-Out</label>
                <input type="date" id="check-out" name="check_out" required>
            </div>
            <div class="form-group">
                <label for="guests">Guests</label>
                <select id="guests" name="guests">
                    <option value="1">1 Adult</option>
                    <option value="2">2 Adults</option>
                    <option value="3">3 Adults</option>
                    <option value="4">4 Adults</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">SEARCH</button>
        </form>
    </div>
</section>

<section id="rooms" class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>01</span>
            <h2>FEATURED ROOMS</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/standard.jpg" alt="Standard Room">
                    <span class="category-label">COMFORT</span>
                </div>
                <div class="room-content">
                    <h3>Standard Room</h3>
                    <p>A cozy and well-appointed room perfect for short stays and business travelers.</p>
                    <a href="room.php?id=1" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/deluxe.jpg" alt="Deluxe Room">
                    <span class="category-label">LUXURY</span>
                </div>
                <div class="room-content">
                    <h3>Deluxe Room</h3>
                    <p>Spacious accommodations with premium amenities for a truly relaxing experience.</p>
                    <a href="room.php?id=2" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/grandsuite.jpg" alt="Grand Suite">
                    <span class="category-label">PREMIUM</span>
                </div>
                <div class="room-content">
                    <h3>Grand Suite</h3>
                    <p>Our most opulent suite offering separate living areas and breathtaking city views.</p>
                    <a href="room.php?id=3" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section id="dining" class="section-padding bg-light" style="background-color: var(--bg-light);">
    <div class="container">
        <div class="section-title">
            <span>02</span>
            <h2>DINING & CUISINE</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/skylounge.jpg" alt="Signature Restaurant">
                    <span class="category-label">FINE DINING</span>
                </div>
                <div class="room-content">
                    <h3>The Sky Lounge</h3>
                    <p>Exquisite flavors paired with breathtaking panoramic views of the city skyline.</p>
                    <a href="dining.php?id=1" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/cafe.jpg" alt="Café">
                    <span class="category-label">CASUAL</span>
                </div>
                <div class="room-content">
                    <h3>Artisan Café</h3>
                    <p>Freshly brewed coffee and hand-crafted pastries in a relaxed, cozy atmosphere.</p>
                    <a href="dining.php?id=2" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/copperbar.jpg" alt="Bar">
                    <span class="category-label">BAR & GRILL</span>
                </div>
                <div class="room-content">
                    <h3>The Copper Bar</h3>
                    <p>Premium spirits and signature cocktails served in an elegant, sophisticated setting.</p>
                    <a href="dining.php?id=3" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section id="amenities" class="section-padding">
    <div class="container">
        <div class="section-title">
            <span>03</span>
            <h2>HOTEL AMENITIES</h2>
        </div>
        <div class="grid-3">
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/infinity.jpg" alt="Pool">
                    <span class="category-label">RECREATION</span>
                </div>
                <div class="room-content">
                    <h3>Infinity Pool</h3>
                    <p>Relax and rejuvenate in our temperature-controlled rooftop infinity pool.</p>
                    <a href="amenity.php?id=1" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/fitness.jpg" alt="Gym">
                    <span class="category-label">WELLNESS</span>
                </div>
                <div class="room-content">
                    <h3>Fitness Center</h3>
                    <p>State-of-the-art equipment to help you maintain your wellness routine.</p>
                    <a href="amenity.php?id=2" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
            <article class="room-card">
                <div class="room-image">
                    <img src="pictures/spa.jpg" alt="Spa">
                    <span class="category-label">WELLNESS</span>
                </div>
                <div class="room-content">
                    <h3>Serenity Spa</h3>
                    <p>Holistic treatments designed to balance the mind, body, and spirit.</p>
                    <a href="amenity.php?id=3" class="btn-link">VIEW DETAILS &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>