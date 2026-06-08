<?php
/**
 * Persistence Layer - File-based Mock
 * For this prototype, we use JSON files to store bookings and other data.
 */

define('DATA_PATH', __DIR__ . '/../data/');

/**
 * Get all data from a JSON file
 */
function get_all_data($filename) {
    $file = DATA_PATH . $filename . '.json';
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    return json_decode($json, true) ?: [];
}

/**
 * Save data to a JSON file
 */
function save_data($filename, $data) {
    $file = DATA_PATH . $filename . '.json';
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($file, $json);
}

/**
 * Initialize data files if they don't exist
 */
function init_db() {
    if (!file_exists(DATA_PATH)) {
        mkdir(DATA_PATH, 0777, true);
    }
    
    // Initial Rooms Data
    if (!file_exists(DATA_PATH . 'rooms.json')) {
        $rooms = [
            [
                'id' => 1,
                'name' => 'Deluxe Room',
                'category' => 'Premium Accommodation',
                'description' => 'Spacious and elegantly appointed, the Deluxe Room offers sweeping city views with premium furnishings.',
                'price' => 250,
                'image' => 'room-1.jpg',
                'amenities' => ['King Bed', 'City View', 'Mini Bar', 'Wi-Fi']
            ],
            [
                'id' => 2,
                'name' => 'Grand Suite',
                'category' => 'Signature Suite',
                'description' => 'The Grand Suite features a separate living area, a private terrace, and butler service.',
                'price' => 450,
                'image' => 'room-2.jpg',
                'amenities' => ['King Bed', 'Private Terrace', 'Butler Service', 'Living Room']
            ],
            [
                'id' => 3,
                'name' => 'Standard Room',
                'category' => 'Classic Comfort',
                'description' => 'A comfortable and well-appointed room ideal for both business and leisure travelers.',
                'price' => 150,
                'image' => 'room-1.jpg',
                'amenities' => ['Queen Bed', 'Work Desk', 'Wi-Fi']
            ]
        ];
        save_data('rooms', $rooms);
    }

    // Initialize bookings if not exists
    if (!file_exists(DATA_PATH . 'bookings.json')) {
        save_data('bookings', []);
    }
}

// Auto-init for prototype
init_db();
?>
