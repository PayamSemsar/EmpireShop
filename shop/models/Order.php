<?php
/**
 * Order Model
 */

require_once __DIR__ . '/Model.php';

class Order extends Model {
    
    /**
     * Create new order
     */
    public function create(int $userId, float $totalAmount, string $shippingAddress): int {
        $data = [
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'payment_method' => 'zibal',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'shipping_address' => $shippingAddress,
            'tracking_code' => generateTrackingCode()
        ];
        
        return $this->insert('orders', $data);
    }
    
    /**
     * Find order by ID with user info
     */
    public function findById(int $id): ?array {
        $sql = "SELECT o.*, u.username, u.email, u.full_name, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        
        return $this->queryOne($sql, [$id]);
    }
    
    /**
     * Get order items
     */
    public function getOrderItems(int $orderId): array {
        $sql = "SELECT oi.*, p.name, p.image_path 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        return $this->query($sql, [$orderId]);
    }
    
    /**
     * Add order item
     */
    public function addOrderItem(int $orderId, int $productId, int $quantity, float $price): bool {
        return $this->insert('order_items', [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ]) > 0;
    }
    
    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool {
        $validStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        return $this->update('orders', $orderId, ['status' => $status]);
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $orderId, string $paymentStatus, string $trackId = null): bool {
        $validStatuses = ['unpaid', 'paid', 'failed'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            return false;
        }
        
        $data = ['payment_status' => $paymentStatus];
        
        if ($trackId !== null) {
            $data['zibal_track_id'] = $trackId;
        }
        
        // If payment is successful, update order status to 'paid'
        if ($paymentStatus === 'paid') {
            $data['status'] = 'paid';
        }
        
        return $this->update('orders', $orderId, $data);
    }
    
    /**
     * Get user orders with pagination
     */
    public function getUserOrders(int $userId, int $page = 1, int $perPage = 10): array {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all orders for admin with pagination
     */
    public function getAllOrders(int $page = 1, int $perPage = 20, array $filters = []): array {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = 'payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT o.*, u.username, u.email, u.full_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                $whereClause
                ORDER BY o.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get total order count
     */
    public function getTotalCount(array $filters = []): int {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = 'payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as count FROM orders $whereClause";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
    
    /**
     * Get order statistics for dashboard
     */
    public function getStatistics(): array {
        $stats = [];
        
        // Total orders
        $stats['total_orders'] = $this->count('orders');
        
        // Pending orders
        $stats['pending_orders'] = $this->count('orders', "status = 'pending'");
        
        // Total revenue (paid orders)
        $sql = "SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'";
        $result = $this->queryOne($sql);
        $stats['total_revenue'] = (float)($result['revenue'] ?? 0);
        
        // Today's orders
        $sql = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
        $result = $this->queryOne($sql);
        $stats['today_orders'] = (int)$result['count'];
        
        return $stats;
    }
    
    /**
     * Find order by tracking code
     */
    public function findByTrackingCode(string $trackingCode): ?array {
        return $this->queryOne("SELECT * FROM orders WHERE tracking_code = ?", [$trackingCode]);
    }
}
