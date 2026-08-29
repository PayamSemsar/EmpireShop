<?php
/**
 * Zibal Payment Gateway Configuration
 * Sandbox mode for testing
 */

define('ZIBAL_MERCHANT', 'your-merchant-id'); // Replace with your actual merchant ID
define('ZIBAL_SANDBOX', true); // Set to false for production

// API Endpoints
define('ZIBAL_API_URL', ZIBAL_SANDBOX 
    ? 'https://sandbox.zibal.ir' 
    : 'https://api.zibal.ir');

// Callback URL
define('ZIBAL_CALLBACK_URL', 'http://localhost/shop/index.php?page=payment/callback');

// Amount in Tomans (1 Toman = 10 Rials)
define('CURRENCY', 'تومان');
