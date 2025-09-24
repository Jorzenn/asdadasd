<?php
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents("php://input");
$event = json_decode($input, true);

// Log all webhook events for debugging
error_log("PayMongo Webhook: " . $input);

// Verify this is a valid PayMongo webhook
if (!$event || !isset($event['data']['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook data']);
    exit;
}

try {
    $pdo = initDatabase();
    
    // Handle different event types
    switch ($event['data']['type']) {
        case 'link':
            handleLinkEvent($event, $pdo);
            break;
            
        case 'payment':
            handlePaymentEvent($event, $pdo);
            break;
            
        default:
            error_log("Unhandled webhook event type: " . $event['data']['type']);
    }
    
    // Always respond with 200 OK for successful webhook processing
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log("Webhook processing error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}

function handleLinkEvent($event, $pdo) {
    $linkData = $event['data']['attributes'];
    $linkId = $event['data']['id'];
    $status = $linkData['status'] ?? 'unknown';
    
    // Update order status based on link status
    if ($status === 'paid') {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'completed', updated_at = CURRENT_TIMESTAMP 
            WHERE payment_id = ?
        ");
        $stmt->execute([$linkId]);
        
        error_log("Payment completed for link ID: $linkId");
    }
}

function handlePaymentEvent($event, $pdo) {
    $paymentData = $event['data']['attributes'];
    $paymentId = $event['data']['id'];
    $status = $paymentData['status'] ?? 'unknown';
    
    // You can add additional payment processing logic here
    error_log("Payment event received - ID: $paymentId, Status: $status");
    
    // If you want to track individual payments, you could create a payments table
    // and store payment details here
}
?>