<?php
require_once 'config.php';

// Initialize database
$pdo = initDatabase();

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$unit_price = (float)$_POST['unit_price'];
$total_amount = (float)$_POST['total_amount'];
$customer_name = trim($_POST['customer_name']);
$customer_email = trim($_POST['customer_email']);
$customer_phone = trim($_POST['customer_phone'] ?? '');

// Get product details
$product = getProductById($product_id, $products);
if (!$product) {
    die('Invalid product selected.');
}

// Validate inputs
if (empty($customer_name) || empty($customer_email) || $quantity < 1 || $total_amount < MIN_AMOUNT) {
    die('Invalid form data submitted.');
}

// Generate order number
$order_number = generateOrderNumber();

try {
    // Insert order into database
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_number, customer_name, customer_email, item_name, item_price, quantity, total_amount, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $order_number, 
        $customer_name, 
        $customer_email, 
        $product['name'], 
        $unit_price, 
        $quantity, 
        $total_amount
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Create Payment Link via PayMongo API
    $data = [
        "data" => [
            "attributes" => [
                "amount" => (int)($total_amount * 100), // Convert to centavos
                "currency" => CURRENCY,
                "description" => "Order #" . $order_number . " - " . $product['name'],
                "remarks" => "Customer: " . $customer_name . " | Email: " . $customer_email,
                "redirect" => [
                    "success" => "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/receipt.php?order=" . $order_number,
                    "failed" => "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/checkout.php?product_id=" . $product_id . "&error=payment_failed"
                ]
            ]
        ]
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYMONGO_API_URL . "/links");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ":")
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("PayMongo API Error: HTTP $httpCode");
    }

    $response = json_decode($result, true);

    if (!isset($response['data']['attributes']['checkout_url'])) {
        throw new Exception("Failed to create payment link: " . json_encode($response));
    }

    // Update order with payment link ID
    $payment_link_id = $response['data']['id'];
    $stmt = $pdo->prepare("UPDATE orders SET payment_id = ? WHERE id = ?");
    $stmt->execute([$payment_link_id, $order_id]);

    // Redirect to PayMongo checkout
    header("Location: " . $response['data']['attributes']['checkout_url']);
    exit();

} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    
    // Redirect back with error
    $error_msg = urlencode("Payment processing failed. Please try again.");
    header("Location: checkout.php?product_id=$product_id&error=$error_msg");
    exit();
}
?>