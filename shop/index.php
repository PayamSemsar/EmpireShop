<?php
/**
 * Main Entry Point / Router
 * Accessories Shop - PHP 8.x + MySQL
 */

// Start session
session_start();

// Set default timezone
date_default_timezone_set('Asia/Tehran');

// Autoload configuration and core files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/zibal.php';
require_once __DIR__ . '/includes/security.php';

// Autoload models
spl_autoload_register(function ($class) {
    $modelFile = __DIR__ . '/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
});

// Autoload controllers
spl_autoload_register(function ($class) {
    $controllerFile = __DIR__ . '/controllers/' . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
    }
});

// Get page parameter
$page = $_GET['page'] ?? 'home';

// Route handling
try {
    switch ($page) {
        // Public Pages
        case 'home':
            $productController = new ProductController();
            $productController->index();
            break;
            
        case 'products':
            $productController = new ProductController();
            $productController->showProducts();
            break;
            
        case 'product':
            $productController = new ProductController();
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $productController->showProduct($id);
            } else {
                redirect('index.php?page=products');
            }
            break;
            
        case 'login':
            $userController = new UserController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userController->login($_POST);
            } else {
                $userController->showLogin();
            }
            break;
            
        case 'register':
            $userController = new UserController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userController->register($_POST);
            } else {
                $userController->showRegister();
            }
            break;
            
        case 'logout':
            $userController = new UserController();
            $userController->logout();
            break;
            
        case 'dashboard':
            $userController = new UserController();
            $userController->showDashboard();
            break;
            
        case 'cart':
            $cartController = new CartController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'update':
                            $cartController->updateQuantity((int)$_POST['cart_item_id'], (int)$_POST['quantity']);
                            break;
                        case 'remove':
                            $cartController->removeItem((int)$_POST['cart_item_id']);
                            break;
                        case 'clear':
                            $cartController->clearCart();
                            break;
                    }
                }
            } else {
                $cartController->showCart();
            }
            break;
            
        case 'add-to-cart':
            $cartController = new CartController();
            $productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            if ($productId > 0) {
                $cartController->addToCart($productId, $quantity);
            } else {
                redirect('index.php?page=products');
            }
            break;
            
        case 'checkout':
            $orderController = new OrderController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $orderController->processCheckout($_POST);
            } else {
                $orderController->showCheckout();
            }
            break;
            
        case 'payment/callback':
            $orderController = new OrderController();
            $orderController->paymentCallback();
            break;
            
        case 'orders':
            $orderController = new OrderController();
            if (isset($_GET['id'])) {
                $orderController->showOrderDetail((int)$_GET['id']);
            } else {
                $orderController->showUserOrders();
            }
            break;
            
        // Admin Pages
        case 'admin/dashboard':
            $adminController = new AdminController();
            $adminController->dashboard();
            break;
            
        case 'admin/products':
            $productController = new ProductController();
            $productController->adminIndex();
            break;
            
        case 'admin/products/create':
            $productController = new ProductController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $productController->adminStore($_POST, $_FILES);
            } else {
                $productController->adminCreate();
            }
            break;
            
        case 'admin/products/edit':
            $productController = new ProductController();
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $productController->adminUpdate($id, $_POST, $_FILES);
                } else {
                    $productController->adminEdit($id);
                }
            } else {
                redirect('index.php?page=admin/products');
            }
            break;
            
        case 'admin/products/delete':
            $productController = new ProductController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $productController->adminDelete($id);
                }
            }
            redirect('index.php?page=admin/products');
            break;
            
        case 'admin/orders':
            $orderController = new OrderController();
            $orderController->adminIndex();
            break;
            
        case 'admin/orders/update-status':
            $orderController = new OrderController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $status = $_POST['status'] ?? 'pending';
                if ($orderId > 0) {
                    $orderController->adminUpdateStatus($orderId, $status);
                }
            }
            redirect('index.php?page=admin/orders');
            break;
            
        case 'admin/orders/detail':
            $orderController = new OrderController();
            $orderId = (int)($_GET['id'] ?? 0);
            if ($orderId > 0) {
                $orderController->adminShowOrder($orderId);
            } else {
                redirect('index.php?page=admin/orders');
            }
            break;
            
        default:
            // 404 Page
            http_response_code(404);
            $pageTitle = 'صفحه یافت نشد';
            ob_start();
            ?>
            <div class="container mx-auto px-4 py-20 text-center">
                <h1 class="text-6xl font-bold text-primary mb-4">404</h1>
                <p class="text-gray-400 text-xl mb-8">صفحه مورد نظر یافت نشد</p>
                <a href="index.php" class="bg-primary text-dark-950 px-6 py-3 rounded-lg font-medium hover:bg-amber-500 transition inline-block">
                    بازگشت به صفحه اصلی
                </a>
            </div>
            <?php
            $content = ob_get_clean();
            require_once __DIR__ . '/views/layouts/header.php';
    }
} catch (Exception $e) {
    // Error handling
    error_log($e->getMessage());
    
    if (defined('DEBUG') && DEBUG) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<div class="container mx-auto px-4 py-20 text-center">
            <h1 class="text-4xl font-bold text-red-500 mb-4">خطای سرور</h1>
            <p class="text-gray-400">لطفاً بعداً مجدداً تلاش کنید</p>
            <a href="index.php" class="mt-6 inline-block bg-primary text-dark-950 px-6 py-3 rounded-lg">بازگشت به صفحه اصلی</a>
        </div>';
    }
}
