<?php
// PayMongo Configuration
define('PAYMONGO_SECRET_KEY', 'sk_test_uMtHKMvgws6uszzcSbVMJd1Z');
define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');

// Database Configuration (SQLite for simplicity)
define('DB_FILE', 'orders.db');

// Site Configuration
define('SITE_NAME', 'SHABU');
define('CURRENCY', 'PHP');
define('MIN_AMOUNT', 100);

// Initialize Database
function initDatabase() {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create orders table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number VARCHAR(50) UNIQUE,
            customer_name VARCHAR(100),
            customer_email VARCHAR(100),
            item_name VARCHAR(100),
            item_price DECIMAL(10,2),
            quantity INTEGER,
            total_amount DECIMAL(10,2),
            payment_id VARCHAR(100),
            payment_status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    return $pdo;
}

// Available Products
$products = [
    [
        'id' => 1,
        'name' => 'Wireless Bluetooth Headphones',
        'price' => 2500.00,
        'image' => 'https://via.placeholder.com/200x150/007bff/ffffff?text=Headphones',
        'description' => 'Premium quality wireless headphones with noise cancellation'
    ],
    [
        'id' => 2,
        'name' => 'Smartphone Stand',
        'price' => 450.00,
        'image' => 'https://via.placeholder.com/200x150/28a745/ffffff?text=Phone+Stand',
        'description' => 'Adjustable smartphone stand for desk and bedside use'
    ],
    [
        'id' => 3,
        'name' => 'USB-C Fast Charger',
        'price' => 1200.00,
        'image' => 'https://via.placeholder.com/200x150/dc3545/ffffff?text=Charger',
        'description' => '65W USB-C fast charger compatible with most devices'
    ],
    [
        'id' => 4,
        'name' => 'Wireless Mouse',
        'price' => 800.00,
        'image' => 'https://via.placeholder.com/200x150/6f42c1/ffffff?text=Mouse',
        'description' => 'Ergonomic wireless mouse with long battery life'
    ],
    [
        'id' => 5,
        'name' => 'Bluetooth Speaker',
        'price' => 1800.00,
        'image' => 'https://via.placeholder.com/200x150/fd7e14/ffffff?text=Speaker',
        'description' => 'Portable Bluetooth speaker with excellent sound quality'
    ],
    [
        'id' => 6,
        'name' => 'Power Bank 20000mAh',
        'price' => 1500.00,
        'image' => 'https://via.placeholder.com/200x150/20c997/ffffff?text=Power+Bank',
        'description' => 'High capacity power bank with fast charging support'
    ]
];

// Helper Functions
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getProductById($id, $products) {
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
}
?>