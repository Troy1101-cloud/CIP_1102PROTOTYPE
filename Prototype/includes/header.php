<?php
// Define base URL to handle both root and Prototype paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);
// Check if we are inside Prototype folder or not
if (basename($script_dir) === 'Prototype') {
    $base_url = $protocol . '://' . $host . $script_dir . '/';
} else {
    $base_url = $protocol . '://' . $host . $script_dir . '/Prototype/';
}
$root_index = dirname($base_url) . '/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ralmitrokij Hotel | Prototype</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/styles.css?v=<?php echo time(); ?>">
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
            /* margin-left: auto; <-- Removed to allow it to sit on the left */
        }

        .hamburger-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            padding: 6px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .hamburger-btn:hover {
            background: rgba(201, 168, 76, 0.15);
        }

        .hamburger-btn span {
            display: block;
            width: 22px;
            height: 2px;
            background-color: #c9a84c;
            border-radius: 2px;
            transition: background 0.2s;
        }

        .hamburger-btn:hover span {
            background-color: #a07830;
        }

        .portal-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            left: 0; 
            top: 100%; /* FIX: Changed from calc(100% + 8px) to remove the hover dead zone */
            background: #fff;
            border: 1px solid #e5e0d5;
            border-radius: 6px;
            min-width: 190px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            z-index: 999;
            overflow: hidden;
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

        /* Note: If your text is vanishing, adjust this margin down from 100px */
        .portal-dropdown .dropdown-menu a .portal-icon {
            margin-right: 10px; 
        }
    </style>
</head>
<body>
    <header class="main-header sticky">
        <div class="container header-container">
            
            <div class="portal-dropdown">
                <button class="hamburger-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="dropdown-menu">
                    <a href="<?php echo $base_url; ?>admin/login.php"><span class="portal-icon"></span>Admin</a>
                    <a href="<?php echo $base_url; ?>staff/login.php"><span class="portal-icon"></span>Staff</a>
                    <a href="<?php echo $base_url; ?>agency/login.php"><span class="portal-icon"></span>Travel Agencies</a>
                </div>
            </div>

            <div class="logo">
                <a href="<?php echo $root_index; ?>" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                    <img src="<?php echo $base_url; ?>pictures/logo.jpg" alt="Ralmitrokij Logo">
                    <span class="logo-box">RALMITROKIJ</span>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo $root_index; ?>#home">HOME</a></li>
                    <li><a href="<?php echo $base_url; ?>rooms.php">ROOMS</a></li>
                    <li><a href="<?php echo $root_index; ?>#dining">DINING</a></li>
                    <li><a href="<?php echo $root_index; ?>#amenities">AMENITIES</a></li>
                    <li><a href="<?php echo $base_url; ?>contact.php">CONTACT</a></li>
                    <li><a href="<?php echo $base_url; ?>admin/login.php">ADMIN</a></li>
                </ul>
            </nav>

            <div class="header-cta" style="display: flex; align-items: center; gap: 12px;">
                <a href="<?php echo $base_url; ?>rooms.php" class="btn-primary">BOOK NOW</a>
            </div>

        </div>
    </header>
    <main>
