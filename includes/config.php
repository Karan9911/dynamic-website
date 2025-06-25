<?php
// Database configuration - UPDATE THESE WITH YOUR MYSQL DETAILS
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password
define('DB_NAME', 'spa_booking');

// Application configuration
define('SITE_URL', 'http://localhost/ai');
define('ADMIN_URL', SITE_URL . '/admin/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Image upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'webp']);

// Razorpay configuration
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_ID'); // Replace with your Razorpay Key ID
define('RAZORPAY_KEY_SECRET', 'YOUR_KEY_SECRET'); // Replace with your Razorpay Key Secret
define('RAZORPAY_ENABLED', true); // Set to false to disable payments

// Email configuration (for future use)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@spa.com');
define('SMTP_PASS', '');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'spa_csrf_token');

// Location API settings
define('LOCATION_API_URL', 'http://ip-api.com/json/');
define('LOCATION_FALLBACK_CITY', 'Delhi');

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>