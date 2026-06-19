<?php
date_default_timezone_set('Asia/Manila');

// Database configuration for InfinityFree
// Replace these with the credentials from your InfinityFree Control Panel (MySQL Databases section)
$host = 'sql210.infinityfree.com'; // e.g., sql123.epizy.com
$dbname = 'if0_42173123_hotel'; // e.g., epiz_12345678_ralmitrokij_hotel
$username = 'if0_42173123'; // e.g., epiz_12345678
$password = 'zPORwedMclPwN'; // Your vPanel password

$pdo = null;

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
