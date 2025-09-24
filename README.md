# PayMongo E-Commerce System

A complete, modern e-commerce system with PayMongo payment integration, featuring a product catalog, secure checkout, digital receipts, and admin dashboard.

## 🚀 Features

- **Product Catalog**: Beautiful, responsive product display
- **Secure Checkout**: Customer information collection with validation
- **PayMongo Integration**: Secure payment processing with webhooks
- **Digital Receipts**: Professional receipts with print functionality
- **Admin Dashboard**: Order management and sales analytics
- **Mobile Responsive**: Works perfectly on all devices
- **SQLite Database**: Lightweight, no-setup database solution

## 📁 File Structure

```
/
├── config.php              # Configuration and database setup
├── index.php               # Product catalog homepage
├── checkout.php             # Customer checkout form
├── process_payment.php      # Payment processing logic
├── callback.php             # PayMongo webhook handler
├── receipt.php              # Digital receipt page
├── admin_orders.php         # Admin dashboard
└── README.md               # Setup instructions
```

## 🛠️ Setup Instructions

### 1. PayMongo Configuration

1. Sign up for a PayMongo account at [https://paymongo.com](https://paymongo.com)
2. Get your **Secret Key** from the PayMongo dashboard
3. Update the secret key in `config.php`:
   ```php
   define('PAYMONGO_SECRET_KEY', 'your_secret_key_here');
   ```

### 2. Webhook Configuration

1. In your PayMongo dashboard, go to **Webhooks**
2. Create a new webhook with URL: `https://yourdomain.com/callback.php`
3. Select these events:
   - `link.payment.paid`
   - `payment.paid`
   - `payment.failed`

### 3. Server Requirements

- PHP 7.4 or higher
- SQLite support (usually included with PHP)
- cURL extension enabled
- Web server (Apache, Nginx, or similar)

### 4. Installation

1. Upload all files to your web server
2. Ensure proper file permissions (755 for directories, 644 for files)
3. The database will be created automatically on first run
4. Access your site: `https://yourdomain.com`

### 5. Admin Access

- Go to `https://yourdomain.com/admin_orders.php`
- Default password: `admin123`
- **Important**: Change the admin password in production!

## 🎨 Customization

### Adding New Products

Edit the `$products` array in `config.php`:

```php
$products = [
    [
        'id' => 7,
        'name' => 'Your Product Name',
        'price' => 999.00,
        'image' => 'https://your-image-url.com/image.jpg',
        'description' => 'Your product description'
    ],
    // ... more products
];
```

### Styling

- Main styles are embedded in each PHP file for easy customization
- Uses Poppins font from Google Fonts
- Responsive design with CSS Grid and Flexbox
- Font Awesome icons included

### Site Branding

Update these constants in `config.php`:
```php
define('SITE_NAME', 'Your Store Name');
define('CURRENCY', 'PHP');
define('MIN_AMOUNT', 100);
```

## 🔧 Configuration Options

### Database

The system uses SQLite by default. To change the database file location:

```php
define('DB_FILE', 'path/to/your/orders.db');
```

For MySQL/PostgreSQL, modify the `initDatabase()` function in `config.php`.

### Payment Settings

```php
define('PAYMONGO_SECRET_KEY', 'sk_test_...');  // Your PayMongo secret key
define('PAYMONGO_API_URL', 'https://api.paymongo.com/v1');
define('MIN_AMOUNT', 100);  // Minimum order amount in PHP
```

## 📱 User Flow

1. **Browse Products**: Customers view the product catalog on the homepage
2. **Select Product**: Click "Buy Now" to go to checkout
3. **Enter Details**: Fill in customer information and select quantity
4. **Payment**: Redirected to PayMongo secure payment page
5. **Confirmation**: Return to receipt page upon successful payment
6. **Admin Tracking**: All orders tracked in admin dashboard

## 🔐 Security Features

- **Input Validation**: All form inputs are validated and sanitized
- **SQL Injection Protection**: Uses prepared statements
- **XSS Prevention**: All output is properly escaped
- **CSRF Protection**: Form-based security measures
- **Webhook Verification**: PayMongo webhook validation
- **Admin Authentication**: Simple password protection for admin area

## 🎯 Key Features Explained

### Product Management
- Easy product addition via configuration array
- Responsive product grid layout
- High-quality placeholder images
- Detailed product descriptions

### Checkout Process
- Single-page checkout form
- Real-time price calculation
- Quantity controls with validation
- Customer information collection

### Payment Integration
- PayMongo Links API integration
- Automatic payment status updates
- Webhook handling for real-time updates
- Secure payment processing

### Receipt System
- Professional digital receipts
- Print-friendly formatting
- Order tracking information
- Animated success confirmation

### Admin Dashboard
- Sales analytics and statistics
- Order management interface
- Revenue tracking
- Customer information overview

## 🚨 Important Security Notes

### Production Deployment

1. **Change Default Passwords**:
   ```php
   // In admin_orders.php, replace:
   if ($_POST['admin_password'] ?? '' === 'admin123')
   // With a strong password hash verification
   ```

2. **Enable HTTPS**: Always use SSL certificates for payment processing

3. **Environment Variables**: Move sensitive config to environment variables:
   ```php
   define('PAYMONGO_SECRET_KEY', $_ENV['PAYMONGO_SECRET_KEY']);
   ```

4. **File Permissions**: Restrict file permissions appropriately
   ```bash
   chmod 755 /path/to/website
   chmod 644 *.php
   chmod 600 config.php  # Contains sensitive data
   ```

5. **Database Security**: If using external database, use strong credentials

## 📊 Database Schema

The system creates the following table structure:

```sql
CREATE TABLE orders (
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
);
```

## 🛠️ Troubleshooting

### Common Issues

1. **Database Not Created**:
   - Check file permissions
   - Ensure SQLite extension is enabled
   - Verify write permissions in the directory

2. **Payment Webhooks Not Working**:
   - Verify webhook URL is publicly accessible
   - Check PayMongo webhook configuration
   - Review server error logs

3. **PayMongo API Errors**:
   - Verify secret key is correct
   - Check API endpoint URLs
   - Ensure cURL extension is enabled

4. **Admin Login Issues**:
   - Verify session support is enabled
   - Check file permissions for session storage

### Debug Mode

Add this to `config.php` for debugging:
```php
// Enable error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log PayMongo responses
define('DEBUG_MODE', true);
```

## 📈 Performance Optimization

- **Image Optimization**: Use optimized images for faster loading
- **Caching**: Implement PHP caching for better performance
- **CDN**: Use CDN for static assets (CSS, JS, images)
- **Database Indexing**: Add indexes for frequently queried fields

## 🔄 Backup Strategy

### Database Backup
```bash
# SQLite backup
cp orders.db backup_$(date +%Y%m%d).db

# Automated backup script
#!/bin/bash
cp orders.db "backups/orders_$(date +%Y%m%d_%H%M%S).db"
find backups/ -name "orders_*.db" -mtime +30 -delete
```

### File Backup
- Regular backups of PHP files
- Version control with Git
- Separate environment configs

## 📞 Support

For issues related to:
- **PayMongo Integration**: Check PayMongo documentation
- **PHP/Server Issues**: Consult your hosting provider
- **Customization**: Modify the code according to your needs

## 🎉 Congratulations!

You now have a fully functional e-commerce system with:
- ✅ Beautiful product catalog
- ✅ Secure payment processing
- ✅ Professional receipts
- ✅ Admin dashboard
- ✅ Mobile-responsive design
- ✅ Production-ready code

Happy selling! 🛒💰
