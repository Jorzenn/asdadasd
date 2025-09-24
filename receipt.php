<?php
require_once 'config.php';

$pdo = initDatabase();
$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    header('Location: index.php');
    exit;
}

// Get order details
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: index.php');
        exit;
    }
    
    // Update payment status to completed if it's still pending
    if ($order['payment_status'] === 'pending') {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $order['payment_status'] = 'completed';
    }
    
} catch (Exception $e) {
    error_log("Receipt error: " . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo htmlspecialchars($order_number); ?></title>
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
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .success-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            animation: fadeInDown 0.8s ease;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: bounce 1s ease;
        }

        .success-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .receipt {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            animation: fadeInUp 0.8s ease;
        }

        .receipt::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 0 10px #48bb78;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .receipt-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .receipt-header .tagline {
            color: #666;
            font-style: italic;
        }

        .order-info {
            margin-bottom: 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #4a5568;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
        }

        .order-number {
            color: #48bb78 !important;
            font-family: 'Courier New', monospace;
        }

        .payment-status {
            background: #48bb78;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .item-details {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .item-details h3 {
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .item-details h3 i {
            margin-right: 10px;
            color: #48bb78;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .price-breakdown {
            border-top: 2px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 30px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .price-row.total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #48bb78;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .footer-note {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #e2e8f0;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 768px) {
            .receipt {
                padding: 20px;
            }
            
            .success-header h1 {
                font-size: 2rem;
            }
            
            .success-icon {
                font-size: 3rem;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }

        @media print {
            body {
                background: white;
            }
            
            .success-header, .actions {
                display: none;
            }
            
            .receipt {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Thank you for your purchase</p>
        </div>

        <div class="receipt">
            <div class="receipt-header">
                <h2><i class="fas fa-store"></i> <?php echo SITE_NAME; ?></h2>
                <p class="tagline">Premium Tech Products</p>
            </div>

            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date & Time:</span>
                    <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Status:</span>
                    <span class="payment-status">
                        <i class="fas fa-check"></i> <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </div>
            </div>

            <div class="item-details">
                <h3><i class="fas fa-box"></i> Item Details</h3>
                <div class="item-name"><?php echo htmlspecialchars($order['item_name']); ?></div>
                <div style="color: #666; font-size: 0.9rem;">
                    Unit Price: <?php echo formatCurrency($order['item_price']); ?> × <?php echo $order['quantity']; ?>
                </div>
            </div>

            <div class="price-breakdown">
                <div class="price-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
                <div class="price-row">
                    <span>Tax:</span>
                    <span>₱0.00</span>
                </div>
                <div class="price-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="price-row total">
                    <span>Total Paid:</span>
                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                </div>
            </div>

            <div class="actions">
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
            </div>

            <div class="footer-note">
                <p><i class="fas fa-info-circle"></i> This is your official receipt for the transaction.</p>
                <p>For any questions or concerns, please contact our support team.</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to receipt after page loads
        window.addEventListener('load', function() {
            document.querySelector('.receipt').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        });

        // Add some confetti effect (optional)
        function createConfetti() {
            const colors = ['#48bb78', '#667eea', '#764ba2', '#ed8936'];
            const confettiCount = 50;

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: fixed;
                    top: -10px;
                    left: ${Math.random() * 100}vw;
                    width: 10px;
                    height: 10px;
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    border-radius: 50%;
                    animation: fall ${Math.random() * 3 + 2}s linear forwards;
                    z-index: 1000;
                `;
                
                document.body.appendChild(confetti);
                
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }

        // Add CSS for confetti animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);

        // Trigger confetti on load
        setTimeout(createConfetti, 500);
    </script>
</body>
</html>