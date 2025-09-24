<?php
require_once 'config.php';

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$product = getProductById($product_id, $products);

if (!$product) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .back-link {
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .back-link:hover {
            opacity: 1;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .product-summary, .customer-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .product-summary h2, .customer-form h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .product-image {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .quantity-section {
            margin-bottom: 20px;
        }

        .quantity-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background 0.3s;
        }

        .quantity-btn:hover {
            background: #5a67d8;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px;
            font-size: 1rem;
        }

        .price-breakdown {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .price-row.total {
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            font-weight: 600;
            font-size: 1.2rem;
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group input:required:invalid {
            border-color: #e53e3e;
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            padding: 15px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }

        .security-info {
            background: #edf2f7;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .security-info i {
            color: #48bb78;
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        
        <div class="header">
            <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>
        </div>

        <form action="process_payment.php" method="POST" id="checkout-form">
            <div class="checkout-container">
                <div class="product-summary">
                    <h2>Order Summary</h2>
                    <div class="product-image" style="background-image: url('<?php echo $product['image']; ?>')"></div>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                    
                    <div class="quantity-section">
                        <label>Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" class="quantity-input" readonly>
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Unit Price:</span>
                            <span id="unit-price"><?php echo formatCurrency($product['price']); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Quantity:</span>
                            <span id="quantity-display">1</span>
                        </div>
                        <div class="price-row total">
                            <span>Total:</span>
                            <span id="total-price"><?php echo formatCurrency($product['price']); ?></span>
                        </div>
                    </div>

                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="unit_price" value="<?php echo $product['price']; ?>">
                    <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $product['price']; ?>">
                </div>

                <div class="customer-form">
                    <h2>Customer Information</h2>
                    
                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Phone Number</label>
                        <input type="tel" id="customer_phone" name="customer_phone" placeholder="+63 912 345 6789">
                    </div>

                    <button type="submit" class="checkout-btn">
                        <i class="fas fa-credit-card"></i>
                        Proceed to Payment
                    </button>

                    <div class="security-info">
                        <i class="fas fa-lock"></i>
                        Your payment information is secured with 256-bit SSL encryption
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const unitPrice = <?php echo $product['price']; ?>;
        
        function changeQuantity(change) {
            const quantityInput = document.getElementById('quantity');
            const currentQuantity = parseInt(quantityInput.value);
            const newQuantity = Math.max(1, Math.min(10, currentQuantity + change));
            
            quantityInput.value = newQuantity;
            updatePrices(newQuantity);
        }

        function updatePrices(quantity) {
            const totalPrice = unitPrice * quantity;
            
            document.getElementById('quantity-display').textContent = quantity;
            document.getElementById('total-price').textContent = '₱' + totalPrice.toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('total_amount').value = totalPrice;
        }

        // Allow manual quantity input
        document.getElementById('quantity').addEventListener('input', function() {
            const quantity = Math.max(1, Math.min(10, parseInt(this.value) || 1));
            this.value = quantity;
            updatePrices(quantity);
        });
    </script>
</body>
</html>