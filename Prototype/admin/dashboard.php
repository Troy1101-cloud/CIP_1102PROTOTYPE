<?php
session_start();
// For prototype purposes, if not logged in, we still show the dashboard but ideally this would check session
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Management | Ralmitrokij Hotel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Fixed Top Navigation -->
    <header class="dashboard-header">
        <div class="header-inner">
            <a href="#" class="admin-logo" onclick="navigateTo('overview')">
                RALMITROKIJ <span>INTERNAL MANAGEMENT</span>
            </a>

            <button class="hamburger" onclick="toggleMobileMenu()">☰</button>

            <nav class="nav-menu" id="nav-menu">
                <li class="nav-item"><a href="#overview" class="nav-link active" onclick="navigateTo('overview')">Overview</a></li>
                <li class="nav-item"><a href="#rooms" class="nav-link" onclick="navigateTo('rooms')">Rooms</a></li>
                <li class="nav-item"><a href="#reservations" class="nav-link" onclick="navigateTo('reservations')">Reservations</a></li>
                <li class="nav-item"><a href="#amenities" class="nav-link" onclick="navigateTo('amenities')">Amenities</a></li>
                <li class="nav-item"><a href="#reports" class="nav-link" onclick="navigateTo('reports')">Reports</a></li>
            </nav>

            <div class="user-section">
                <div class="admin-dropdown">
                    <button class="admin-dropdown-toggle">
                        ADMINISTRATOR ▾
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="#" class="dropdown-item">My Profile</a>
                        <a href="#" class="dropdown-item">Account Settings</a>
                        <a href="#" class="dropdown-item">Security</a>
                    </div>
                </div>
                <a href="login.php" class="logout-btn">LOGOUT</a>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        
        <!-- View: Overview -->
        <section id="view-overview" class="dashboard-view">
            <div class="page-header">
                <span class="breadcrumb">Internal &rsaquo; Dashboard</span>
                <h1 class="page-title">Operational Overview</h1>
                <p class="page-subtitle">Real-time metrics and system health monitoring.</p>
            </div>

            <div class="metrics-grid">
                <div class="card">
                    <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Daily Performance</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="background: #f8f9fa; padding: 20px; text-align: center;">
                            <span style="display: block; font-size: 0.7rem; color: #666; margin-bottom: 5px; text-transform: uppercase;">Active Bookings</span>
                            <span style="font-size: 2rem; font-weight: 700; color: var(--admin-accent);">24</span>
                        </div>
                        <div style="background: #f8f9fa; padding: 20px; text-align: center;">
                            <span style="display: block; font-size: 0.7rem; color: #666; margin-bottom: 5px; text-transform: uppercase;">Revenue Today</span>
                            <span style="font-size: 2rem; font-weight: 700; color: var(--admin-accent);">$4,250</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Recent Activity</h3>
                    <ul style="list-style: none; font-size: 0.85rem;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">• New booking for <strong>Deluxe Suite</strong> - 2 mins ago</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">• Payment confirmed for <strong>#BK-1082</strong> - 15 mins ago</li>
                        <li style="padding: 10px 0;">• Maintenance requested for <strong>Room 304</strong> - 1 hour ago</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- View: Reservations (Primary Focus) -->
        <section id="view-reservations" class="dashboard-view" style="display: none;">
            <div class="page-header">
                <span class="breadcrumb">Live Status &rsaquo; Reservations</span>
                <h1 class="page-title">Guest Bookings</h1>
                <p class="page-subtitle">Manage and track all hotel bookings and guest arrivals.</p>
            </div>

            <div class="card">
                <div class="filter-section">
                    <div class="filter-group">
                        <label>FILTER BY STATUS</label>
                        <select id="status-filter" class="filter-select">
                            <option value="all">All Reservations</option>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button class="btn-admin" onclick="applyFilter()">APPLY FILTER</button>
                </div>

                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Accommodation</th>
                                <th>Stay Duration</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reservations-body">
                            <!-- Mock Data -->
                            <tr data-status="Paid">
                                <td>#BK-1085</td>
                                <td><strong>Alexander Pierce</strong></td>
                                <td>Penthouse Suite</td>
                                <td>Jun 12 - Jun 15</td>
                                <td>2 Adults</td>
                                <td><span class="status-badge status-paid">Paid</span></td>
                                <td><button class="btn-outline">View</button></td>
                            </tr>
                            <tr data-status="Pending">
                                <td>#BK-1084</td>
                                <td><strong>Sarah Jenkins</strong></td>
                                <td>Deluxe Ocean Room</td>
                                <td>Jun 14 - Jun 16</td>
                                <td>1 Adult</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td><button class="btn-outline">View</button></td>
                            </tr>
                            <tr data-status="Paid">
                                <td>#BK-1083</td>
                                <td><strong>Robert Fox</strong></td>
                                <td>Executive Twin</td>
                                <td>Jun 10 - Jun 12</td>
                                <td>2 Adults</td>
                                <td><span class="status-badge status-paid">Paid</span></td>
                                <td><button class="btn-outline">View</button></td>
                            </tr>
                            <tr data-status="Cancelled">
                                <td>#BK-1082</td>
                                <td><strong>Emma Wilson</strong></td>
                                <td>Standard Single</td>
                                <td>Jun 20 - Jun 22</td>
                                <td>1 Adult</td>
                                <td><span class="status-badge status-cancelled">Cancelled</span></td>
                                <td><button class="btn-outline">View</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="empty-state" class="empty-state" style="display: none;">
                        No reservations found for the current filter
                    </div>
                </div>
            </div>
        </section>

        <!-- View: Rooms -->
        <section id="view-rooms" class="dashboard-view" style="display: none;">
            <div class="page-header">
                <span class="breadcrumb">Inventory &rsaquo; Rooms</span>
                <h1 class="page-title">Room Management</h1>
                <p class="page-subtitle">Monitor availability and assign room tasks.</p>
            </div>

            <div class="view-toggle">
                <button class="btn-admin" id="toggle-grid" onclick="switchRoomView('grid')">Grid View</button>
                <button class="btn-outline" id="toggle-list" onclick="switchRoomView('list')">List View</button>
            </div>

            <div id="rooms-grid" class="room-grid">
                <!-- Mock Room Cards -->
                <div class="room-card">
                    <span class="room-status status-available">AVAILABLE</span>
                    <span class="room-type">PENTHOUSE</span>
                    <h4>Room 501</h4>
                    <p style="font-size: 0.8rem; color: #666;">King Bed • Ocean View • Private Balcony</p>
                    <div class="room-actions">
                        <button class="btn-outline">Details</button>
                        <button class="btn-outline">Edit</button>
                    </div>
                </div>
                <div class="room-card">
                    <span class="room-status status-occupied">OCCUPIED</span>
                    <span class="room-type">DELUXE</span>
                    <h4>Room 302</h4>
                    <p style="font-size: 0.8rem; color: #666;">Queen Bed • Garden View</p>
                    <div class="room-actions">
                        <button class="btn-outline">Details</button>
                        <button class="btn-outline">Edit</button>
                    </div>
                </div>
                <div class="room-card">
                    <span class="room-status status-maintenance">MAINTENANCE</span>
                    <span class="room-type">EXECUTIVE</span>
                    <h4>Room 405</h4>
                    <p style="font-size: 0.8rem; color: #666;">Double Bed • City View</p>
                    <div class="room-actions">
                        <button class="btn-outline">Details</button>
                        <button class="btn-outline" style="border-color: var(--admin-danger); color: var(--admin-danger);">Assign</button>
                    </div>
                </div>
            </div>

            <div id="rooms-list" class="room-list" style="display: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Room No.</th>
                            <th>Type</th>
                            <th>Features</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>501</td>
                            <td>Penthouse</td>
                            <td>King Bed, Ocean View</td>
                            <td><span class="status-badge status-paid">Available</span></td>
                            <td><button class="btn-outline">Manage</button></td>
                        </tr>
                        <tr>
                            <td>302</td>
                            <td>Deluxe</td>
                            <td>Queen Bed, Garden View</td>
                            <td><span class="status-badge status-pending">Occupied</span></td>
                            <td><button class="btn-outline">Manage</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- View: Amenities -->
        <section id="view-amenities" class="dashboard-view" style="display: none;">
            <div class="page-header">
                <span class="breadcrumb">Facilities &rsaquo; Amenities</span>
                <h1 class="page-title">Service Monitoring</h1>
                <p class="page-subtitle">Track operational status of hotel facilities.</p>
            </div>

            <div class="amenity-grid">
                <div class="amenity-card">
                    <div class="amenity-icon">🍽️</div>
                    <h3>Main Dining</h3>
                    <div class="indicator indicator-green">OPERATIONAL</div>
                    <button class="btn-admin" style="width: 100%;">QUICK EDIT</button>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">🏊</div>
                    <h3>Infinity Pool</h3>
                    <div class="indicator indicator-green">OPERATIONAL</div>
                    <button class="btn-admin" style="width: 100%;">QUICK EDIT</button>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">💪</div>
                    <h3>Fitness Center</h3>
                    <div class="indicator indicator-yellow">LIMITED SERVICE</div>
                    <button class="btn-admin" style="width: 100%;">QUICK EDIT</button>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">🧖</div>
                    <h3>Luxury Spa</h3>
                    <div class="indicator indicator-red">CLOSED</div>
                    <button class="btn-admin" style="width: 100%;">QUICK EDIT</button>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">💼</div>
                    <h3>Business Center</h3>
                    <div class="indicator indicator-green">OPERATIONAL</div>
                    <button class="btn-admin" style="width: 100%;">QUICK EDIT</button>
                </div>
            </div>
        </section>

        <!-- View: Reports -->
        <section id="view-reports" class="dashboard-view" style="display: none;">
            <div class="page-header">
                <span class="breadcrumb">Analytics &rsaquo; Reports</span>
                <h1 class="page-title">Performance Reports</h1>
                <p class="page-subtitle">Visualize key metrics and growth trends.</p>
            </div>

            <div class="metrics-grid">
                <div class="chart-container">
                    <h3 style="font-family: 'Playfair Display', serif;">Booking Volume Trends</h3>
                    <p style="font-size: 0.8rem; color: #666;">Monthly distribution of reservations across all categories.</p>
                    <div class="chart-placeholder">
                        [ Bar Chart: Jan - Jun Booking Data ]
                    </div>
                </div>
                <div class="chart-container">
                    <h3 style="font-family: 'Playfair Display', serif;">Revenue Tracking</h3>
                    <p style="font-size: 0.8rem; color: #666;">Total monthly revenue generated from rooms and services.</p>
                    <div class="chart-placeholder">
                        [ Line Chart: Monthly Revenue Curve ]
                    </div>
                </div>
                <div class="chart-container">
                    <h3 style="font-family: 'Playfair Display', serif;">Room Occupancy</h3>
                    <p style="font-size: 0.8rem; color: #666;">Percentage of occupied vs available rooms.</p>
                    <div class="chart-placeholder">
                        [ Pie Chart: 68% Occupancy Rate ]
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer for Documentation purposes -->
    <footer style="margin-top: 100px; padding: 40px 0; border-top: 1px solid #eee; background: #fff;">
        <div class="dashboard-container">
            <h4 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Developer Handoff Documentation</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; font-size: 0.85rem; line-height: 1.6;">
                <div>
                    <h5 style="color: var(--admin-accent); margin-bottom: 10px; text-transform: uppercase;">HTML Structure</h5>
                    <p>The dashboard uses a <strong>Single Page Application (SPA)</strong> approach. Navigation is handled by JavaScript toggling the display of <code>.dashboard-view</code> sections. All sections follow semantic HTML5 (header, main, section).</p>
                </div>
                <div>
                    <h5 style="color: var(--admin-accent); margin-bottom: 10px; text-transform: uppercase;">PHP Integration Guide</h5>
                    <ol style="padding-left: 15px;">
                        <li>Replace mock data in <code>reservations-body</code> with a PHP <code>foreach</code> loop.</li>
                        <li>Implement a <code>$_GET['view']</code> parameter to handle server-side state if needed.</li>
                        <li>Connect filters to a SQL <code>WHERE</code> clause via AJAX or form submission.</li>
                    </ol>
                </div>
                <div>
                    <h5 style="color: var(--admin-accent); margin-bottom: 10px; text-transform: uppercase;">Database Recommendations</h5>
                    <p>Tables needed: <code>bookings</code> (guest_id, room_id, status, dates), <code>rooms</code> (number, type, status, price), <code>amenities</code> (name, status_id, last_updated).</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Client-side Routing
        function navigateTo(viewId) {
            // Hide all views
            document.querySelectorAll('.dashboard-view').forEach(view => {
                view.style.display = 'none';
            });
            
            // Show target view
            document.getElementById('view-' + viewId).style.display = 'block';
            
            // Update active link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if(link.getAttribute('href') === '#' + viewId) {
                    link.classList.add('active');
                }
            });

            // Close mobile menu if open
            document.getElementById('nav-menu').classList.remove('active');

            // Scroll to top
            window.scrollTo(0, 0);
        }

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('nav-menu').classList.toggle('active');
        }

        // Reservations Filter Logic
        function applyFilter() {
            const status = document.getElementById('status-filter').value;
            const rows = document.querySelectorAll('#reservations-body tr');
            const emptyState = document.getElementById('empty-state');
            const table = document.querySelector('.data-table');
            
            let visibleCount = 0;

            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                table.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                table.style.display = 'table';
                emptyState.style.display = 'none';
            }
        }

        // Room View Toggle Logic
        function switchRoomView(viewType) {
            const grid = document.getElementById('rooms-grid');
            const list = document.getElementById('rooms-list');
            const gridBtn = document.getElementById('toggle-grid');
            const listBtn = document.getElementById('toggle-list');

            if (viewType === 'grid') {
                grid.style.display = 'grid';
                list.style.display = 'none';
                gridBtn.className = 'btn-admin';
                listBtn.className = 'btn-outline';
            } else {
                grid.style.display = 'none';
                list.style.display = 'block';
                gridBtn.className = 'btn-outline';
                listBtn.className = 'btn-admin';
            }
        }

        // Handle URL hash on load
        window.addEventListener('load', () => {
            const hash = window.location.hash.replace('#', '') || 'overview';
            navigateTo(hash);
        });
    </script>
</body>
</html>
