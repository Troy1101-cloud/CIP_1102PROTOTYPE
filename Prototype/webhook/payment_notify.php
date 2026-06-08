<?php
/**
 * Mock Webhook Handler
 * Simulates an external payment provider notifying the system of a payment update.
 */
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    
    if (isset($payload['booking_id']) && isset($payload['status'])) {
        $booking_id = (int)$payload['booking_id'];
        $status = $payload['status']; // e.g., 'paid', 'failed'
        
        $bookings = get_all_data('bookings');
        $updated = false;
        
        foreach ($bookings as &$b) {
            if ($b['id'] === $booking_id) {
                $b['payment_status'] = $status;
                if ($status === 'paid') {
                    $b['status'] = 'confirmed';
                }
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            save_data('bookings', $bookings);
            http_response_code(200);
            echo json_encode(['message' => 'Booking updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Booking not found']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid payload']);
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>
