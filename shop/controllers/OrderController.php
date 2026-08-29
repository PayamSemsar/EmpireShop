<?php
/**
 * Order Controller
 * Handles order processing and payment
 */

class OrderController {
    private Order $orderModel;
    private Cart $cartModel;
    private Product $productModel;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Show checkout page
     */
    public function showCheckout(): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $items = $this->cartModel->getItems($userId);
        
        if (empty($items)) {
            setFlashMessage('error', 'سبد خرید شما خالی است');
            redirect('index.php?page=products');
        }
        
        // Validate stock availability
        $stockIssues = $this->cartModel->validateStock($userId);
        if (!empty($stockIssues)) {
            setFlashMessage('error', 'موجودی برخی محصولات کافی نیست');
            redirect('index.php?page=cart');
        }
        
        $total = $this->cartModel->getTotal($userId);
        $user = (new User())->findById($userId);
        
        require_once __DIR__ . '/../views/public/checkout.php';
    }
    
    /**
     * Process checkout and initiate payment
     */
    public function processCheckout(array $data): void {
        requireLogin();
        
        $errors = [];
        $userId = $_SESSION['user_id'];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی');
            redirect('index.php?page=checkout');
        }
        
        $items = $this->cartModel->getItems($userId);
        
        if (empty($items)) {
            setFlashMessage('error', 'سبد خرید شما خالی است');
            redirect('index.php?page=products');
        }
        
        // Get shipping address
        $shippingAddress = sanitizeInput($data['address'] ?? '');
        
        if (empty($shippingAddress)) {
            $errors[] = 'آدرس ارسال الزامی است';
        }
        
        // Validate stock again
        $stockIssues = $this->cartModel->validateStock($userId);
        if (!empty($stockIssues)) {
            setFlashMessage('error', 'موجودی برخی محصولات کافی نیست');
            redirect('index.php?page=cart');
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=checkout');
        }
        
        // Calculate total
        $total = $this->cartModel->getTotal($userId);
        
        // Create order
        $orderId = $this->orderModel->create($userId, $total, $shippingAddress);
        
        if (!$orderId) {
            setFlashMessage('error', 'خطا در ایجاد سفارش');
            redirect('index.php?page=checkout');
        }
        
        // Add order items and decrease stock
        foreach ($items as $item) {
            $this->orderModel->addOrderItem(
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            
            $this->productModel->decreaseStock($item['product_id'], $item['quantity']);
        }
        
        // Clear cart
        $this->cartModel->clearCart($userId);
        
        // Initiate Zibal payment
        $this->initiatePayment($orderId, $total);
    }
    
    /**
     * Initiate Zibal payment
     */
    private function initiatePayment(int $orderId, float $amount): void {
        $order = $this->orderModel->findById($orderId);
        
        // Prepare payment request
        $paymentData = [
            'merchant' => ZIBAL_MERCHANT,
            'amount' => (int)$amount, // Amount in Tomans
            'callbackUrl' => ZIBAL_CALLBACK_URL,
            'orderId' => $orderId,
            'mobile' => null, // Optional
            'description' => 'سفارش #' . $order['tracking_code'],
        ];
        
        // Send request to Zibal
        $ch = curl_init(ZIBAL_API_URL . '/v1/request');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            setFlashMessage('error', 'خطا در ارتباط با درگاه پرداخت');
            redirect('index.php?page=orders/' . $orderId);
        }
        
        $result = json_decode($response, true);
        
        if ($result['result'] !== 1) {
            setFlashMessage('error', 'خطا در شروع پرداخت: ' . ($result['message'] ?? 'نامشخص'));
            redirect('index.php?page=orders/' . $orderId);
        }
        
        // Update order with track ID
        $this->orderModel->updatePaymentStatus($orderId, 'unpaid', $result['trackId']);
        
        // Redirect to Zibal gateway
        redirect(ZIBAL_API_URL . '/startPay/' . $result['trackId']);
    }
    
    /**
     * Payment callback from Zibal
     */
    public function paymentCallback(): void {
        $trackId = $_GET['trackId'] ?? null;
        
        if (!$trackId) {
            setFlashMessage('error', 'اطلاعات پرداخت نامعتبر است');
            redirect('index.php?page=login');
        }
        
        // Verify payment with Zibal
        $ch = curl_init(ZIBAL_API_URL . '/v1/verify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'merchant' => ZIBAL_MERCHANT,
            'trackId' => $trackId
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        // Find order by track ID
        $order = $this->orderModel->queryOne("SELECT * FROM orders WHERE zibal_track_id = ?", [$trackId]);
        
        if (!$order) {
            setFlashMessage('error', 'سفارش یافت نشد');
            redirect('index.php?page=login');
        }
        
        $orderId = $order['id'];
        
        if ($result['result'] === 1 && $result['status'] === 1) {
            // Payment successful
            $this->orderModel->updatePaymentStatus($orderId, 'paid', $trackId);
            setFlashMessage('success', 'پرداخت با موفقیت انجام شد');
            redirect('index.php?page=orders/' . $orderId);
        } else {
            // Payment failed
            $this->orderModel->updatePaymentStatus($orderId, 'failed', $trackId);
            setFlashMessage('error', 'پرداخت ناموفق بود');
            redirect('index.php?page=orders/' . $orderId);
        }
    }
    
    /**
     * Show user orders
     */
    public function showUserOrders(): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        
        $orders = $this->orderModel->getUserOrders($userId, $page, $perPage);
        $totalCount = $this->orderModel->queryOne(
            "SELECT COUNT(*) as count FROM orders WHERE user_id = ?",
            [$userId]
        )['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        require_once __DIR__ . '/../views/public/orders.php';
    }
    
    /**
     * Show order detail
     */
    public function showOrderDetail(int $orderId): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $order = $this->orderModel->findById($orderId);
        
        if (!$order || $order['user_id'] !== $userId) {
            setFlashMessage('error', 'سفارش یافت نشد');
            redirect('index.php?page=orders');
        }
        
        $items = $this->orderModel->getOrderItems($orderId);
        
        require_once __DIR__ . '/../views/public/order-detail.php';
    }
    
    /**
     * Admin: Show all orders
     */
    public function adminIndex(): void {
        requireAdmin();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'payment_status' => $_GET['payment_status'] ?? null
        ];
        
        $orders = $this->orderModel->getAllOrders($page, $perPage, $filters);
        $totalCount = $this->orderModel->getTotalCount($filters);
        $totalPages = ceil($totalCount / $perPage);
        
        require_once __DIR__ . '/../views/admin/orders.php';
    }
    
    /**
     * Admin: Update order status
     */
    public function adminUpdateStatus(int $orderId, string $status): void {
        requireAdmin();
        
        // Validate CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی');
            redirect('index.php?page=admin/orders');
        }
        
        $order = $this->orderModel->findById($orderId);
        
        if (!$order) {
            setFlashMessage('error', 'سفارش یافت نشد');
            redirect('index.php?page=admin/orders');
        }
        
        $success = $this->orderModel->updateStatus($orderId, $status);
        
        if ($success) {
            setFlashMessage('success', 'وضعیت سفارش به‌روزرسانی شد');
        } else {
            setFlashMessage('error', 'خطا در به‌روزرسانی وضعیت');
        }
        
        redirect('index.php?page=admin/orders');
    }
    
    /**
     * Admin: Show order detail
     */
    public function adminShowOrder(int $orderId): void {
        requireAdmin();
        
        $order = $this->orderModel->findById($orderId);
        
        if (!$order) {
            setFlashMessage('error', 'سفارش یافت نشد');
            redirect('index.php?page=admin/orders');
        }
        
        $items = $this->orderModel->getOrderItems($orderId);
        
        require_once __DIR__ . '/../views/admin/order-detail.php';
    }
}
