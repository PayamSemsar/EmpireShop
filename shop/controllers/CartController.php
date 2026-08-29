<?php
/**
 * Cart Controller
 * Handles shopping cart operations
 */

class CartController {
    private Cart $cartModel;
    private Product $productModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Show cart page
     */
    public function showCart(): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $items = $this->cartModel->getItems($userId);
        $total = $this->cartModel->getTotal($userId);
        
        require_once __DIR__ . '/../views/public/cart.php';
    }
    
    /**
     * Add item to cart
     */
    public function addToCart(int $productId, int $quantity = 1): void {
        requireLogin();
        
        // Validate CSRF for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'خطای امنیتی');
                redirect('index.php?page=products');
            }
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check product exists and is active
        $product = $this->productModel->findById($productId);
        
        if (!$product || !$product['is_active']) {
            setFlashMessage('error', 'محصول یافت نشد');
            redirect('index.php?page=products');
        }
        
        // Check stock availability
        if ($product['stock'] < $quantity) {
            setFlashMessage('error', 'موجودی محصول کافی نیست');
            redirect('index.php?page=product&id=' . $productId);
        }
        
        // Add to cart
        $success = $this->cartModel->addItem($userId, $productId, $quantity);
        
        if ($success) {
            setFlashMessage('success', 'محصول به سبد خرید اضافه شد');
        } else {
            setFlashMessage('error', 'خطا در افزودن به سبد خرید');
        }
        
        // Redirect back to product page or referer
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=products';
        redirect($referer);
    }
    
    /**
     * Update cart item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): void {
        requireLogin();
        
        // Validate CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی');
            redirect('index.php?page=cart');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verify item belongs to user
        $items = $this->cartModel->getItems($userId);
        $itemExists = false;
        
        foreach ($items as $item) {
            if ($item['id'] === $cartItemId) {
                $itemExists = true;
                
                // Check stock if increasing quantity
                if ($quantity > $item['quantity'] && $item['stock'] < $quantity) {
                    setFlashMessage('error', 'موجودی محصول کافی نیست');
                    redirect('index.php?page=cart');
                    return;
                }
                
                break;
            }
        }
        
        if (!$itemExists) {
            setFlashMessage('error', 'آیتم در سبد خرید یافت نشد');
            redirect('index.php?page=cart');
        }
        
        $success = $this->cartModel->updateQuantity($cartItemId, $quantity);
        
        if ($success) {
            setFlashMessage('success', 'سبد خرید به‌روزرسانی شد');
        } else {
            setFlashMessage('error', 'خطا در به‌روزرسانی سبد خرید');
        }
        
        redirect('index.php?page=cart');
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): void {
        requireLogin();
        
        // Validate CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی');
            redirect('index.php?page=cart');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verify item belongs to user
        $items = $this->cartModel->getItems($userId);
        $itemExists = false;
        
        foreach ($items as $item) {
            if ($item['id'] === $cartItemId) {
                $itemExists = true;
                break;
            }
        }
        
        if (!$itemExists) {
            setFlashMessage('error', 'آیتم در سبد خرید یافت نشد');
            redirect('index.php?page=cart');
        }
        
        $success = $this->cartModel->removeItem($cartItemId);
        
        if ($success) {
            setFlashMessage('success', 'آیتم از سبد خرید حذف شد');
        } else {
            setFlashMessage('error', 'خطا در حذف آیتم');
        }
        
        redirect('index.php?page=cart');
    }
    
    /**
     * Clear cart
     */
    public function clearCart(): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $this->cartModel->clearCart($userId);
        
        setFlashMessage('success', 'سبد خرید خالی شد');
        redirect('index.php?page=cart');
    }
}
