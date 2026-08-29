<?php
/**
 * Admin Controller
 * Handles admin dashboard and statistics
 */

class AdminController {
    
    /**
     * Show admin dashboard
     */
    public function dashboard(): void {
        requireAdmin();
        
        $orderModel = new Order();
        $userModel = new User();
        $productModel = new Product();
        
        // Get statistics
        $stats = $orderModel->getStatistics();
        $stats['total_users'] = $userModel->getTotalCount();
        $stats['total_products'] = $productModel->count('products');
        
        // Recent orders
        $recentOrders = $orderModel->getAllOrders(1, 5);
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}
