<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? '';
$price = (float)($_GET['price'] ?? 0);
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

if (!$category || !$price || !$check_in || !$check_out) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$stats = get_room_group_stats($category, $price, $check_in, $check_out);
$available = $stats['available'] > 0;

echo json_encode([
    'success' => true,
    'available' => $available,
    'stats' => $stats
]);
