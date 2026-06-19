<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ralmitrokij Hotel | Prototype</title>
    <link rel="icon" type="image/png" href="pictures/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        .logo img {
            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #c9a84c;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .portal-dropdown {
            position: relative;
            margin-left: px;
            /* margin-left: auto; <-- Removed to allow it to sit on the left */
        }

        .portal-btn {
            background: transparent;
            border: 1px solid #c9a84c;
            color: #c9a84c;
            padding: 8px 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .portal-btn:hover {
            background: #c9a84c;
            color: white;
        }

        .portal-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            left: 50%; 
            transform: translateX(-50%);
            top: 100%; 
            background: #fff;
            border: 1px solid #e5e0d5;
            border-radius: 6px;
            min-width: 190px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            z-index: 999;
            overflow: hidden;
            /* margin-top removed to fix the hover dead zone */
        }

        /* Bridge the gap so hover doesn't drop */
        .portal-dropdown::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 15px;
        }

        .portal-dropdown:hover .dropdown-menu {
            display: block;
        }

        .portal-dropdown .dropdown-menu a {
            display: block;
            padding: 12px 18px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #333;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .portal-dropdown .dropdown-menu a:hover {
            background: #c9a84c;
            color: #fff;
        }

        /* Checkbox Hack Elements */
        .mobile-menu-toggle {
            display: none;
        }

        .mobile-menu-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            padding: 10px;
            z-index: 2000;
        }

        .mobile-menu-btn span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: var(--primary-color, #1a1a1a);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .mobile-only-link {
            display: none;
        }

        .header-actions-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>
<body>
    <header class="main-header sticky">
        <div class="container header-container">
            
            <input type="checkbox" id="mobile-menu-toggle" class="mobile-menu-toggle">
            
            <label for="mobile-menu-toggle" class="mobile-menu-btn">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <div class="logo">
                <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                    <img src="pictures/logo.jpg" alt="Ralmitrokij Logo">
                    <span class="logo-box">RALMITROKIJ</span>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="index.php#home">HOME</a></li>
                    <li><a href="rooms.php">ROOMS</a></li>
                    <li><a href="index.php#dining">DINING</a></li>
                    <li><a href="index.php#amenities">AMENITIES</a></li>
                    <li><a href="contact.php">CONTACT</a></li>
                    <li class="mobile-only-link"><a href="admin/login.php">Admin Portal</a></li>
                    <li class="mobile-only-link"><a href="staff/login.php">Staff Portal</a></li>
                    <li class="mobile-only-link"><a href="agency/login.php">Travel Agencies</a></li>
                </ul>
            </nav>

            <div class="header-actions-group desktop-only-group">
                <div class="header-cta">
                    <a href="rooms.php" class="btn-primary">BOOK NOW</a>
                </div>

                <div class="portal-dropdown">
                    <button class="portal-btn">
                        PORTALS &#9662;
                    </button>
                    <div class="dropdown-menu">
                        <a href="admin/login.php">Admin</a>
                        <a href="staff/login.php">Staff</a>
                        <a href="agency/login.php">Travel Agencies</a>
                    </div>
                </div>
            </div>

        </div>
    </header>
    <main>
    </main>
</body>
</html>