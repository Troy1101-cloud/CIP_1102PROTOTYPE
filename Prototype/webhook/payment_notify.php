<?php
/**
 * Mock Webhook Handler
 * Simulates an external payment provider notifying the system of a payment update.
 */
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    
    if (isset($payload['booking_id']) && isset($payload['status'])) {
        $booking_id = (int)$payload['booking_id'];
        $status = $payload['status']; // e.g., 'paid', 'failed'
        
        $db_status = ($status === 'paid') ? 'confirmed' : 'pending';
        
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$db_status, $booking_id]);
        
        if ($stmt->rowCount() > 0) {
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
