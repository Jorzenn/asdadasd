<?php
require_once 'config.php';

$pdo = initDatabase();

// Simple authentication (you should implement proper admin authentication)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['admin_password'] ?? '' === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login</title>
            <style>
                body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
                .login-form { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
                input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
                button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
            </style>
        </head>
        <body>
            <form method="POST" class="login-form">
                <h2>Admin Login</h2>
                <input type="password" name="admin_password" placeholder="Admin Password" required>
                <button type="submit">Login</button>
                <p style="font-size: 12px; color: #666; margin-top: 15px;">Default password: admin123</p>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// Get all orders
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_orders = count($orders);
$completed_orders = count(array_filter($orders, fn($o) => $o['payment_status'] === 'completed'));
$total_revenue = array_sum(array_column(array_filter($orders, fn($o) => $o['payment_status'] === 'completed'), 'total_amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Management</title>
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
            background: #f8fafc;
            color: #2d3748;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card.orders i { color: #667eea; }
        .stat-card.completed i { color: #48bb78; }
        .stat-card.revenue i { color: #ed8936; }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        .orders-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .section-header h2 i {
            margin-right: 10px;
            color: #667eea;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .orders-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .orders-table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-completed {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-pending {
            background: #fed7c3;
            color: #c05621;
        }

        .order-number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #667eea;
        }

        .actions {
            text-align: center;
            padding: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-logout {
            background: #e53e3e;
            color: white;
        }

        .btn-refresh {
            background: #667eea;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .orders-table {
                font-size: 0.9rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 10px 8px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1><i class="fas fa-cogs"></i> Admin Dashboard</h1>
            <p>Manage orders and track sales performance</p>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card orders">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card completed">
                <i class="fas fa-check-circle"></i>
                <div class="stat-number"><?php echo $completed_orders; ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
            <div class="stat-card revenue">
                <i class="fas fa-peso-sign"></i>
                <div class="stat-number"><?php echo formatCurrency($total_revenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <div class="orders-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Recent Orders</h2>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Orders Yet</h3>
                    <p>Orders will appear here once customers start making purchases.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div style="font-size: 0.8rem; color: #666;">
                                            <?php echo htmlspecialchars($order['customer_email']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                                    <td><?php echo $order['quantity']; ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="?refresh=1" class="btn btn-refresh">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
            <a href="index.php" class="btn btn-refresh">
                <i class="fas fa-store"></i> View Store
            </a>
            <a href="?logout=1" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <?php
    // Handle logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: admin_orders.php');
        exit;
    }
    ?>
</body>
</html>