<?php
/**
 * Cart Model
 */

require_once __DIR__ . '/Model.php';

class Cart extends Model {
    
    /**
     * Get cart items for a user
     */
    public function getItems(int $userId): array {
        $sql = "SELECT ci.*, p.name, p.price, p.stock, p.image_path, c.name as category_name
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE ci.user_id = ?
                ORDER BY ci.added_at DESC";
        
        return $this->query($sql, [$userId]);
    }
    
    /**
     * Add item to cart
     */
    public function addItem(int $userId, int $productId, int $quantity = 1): bool {
        // Check if item already exists in cart
        $existingItem = $this->queryOne(
            "SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Insert new item
            return $this->insert('cart_items', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]) > 0;
        }
    }
    
    /**
     * Update item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): bool {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        
        return $this->update('cart_items', $cartItemId, ['quantity' => $quantity]);
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): bool {
        return $this->delete('cart_items', $cartItemId);
    }
    
    /**
     * Clear cart for user
     */
    public function clearCart(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Get cart total
     */
    public function getTotal(int $userId): float {
        $sql = "SELECT SUM(p.price * ci.quantity) as total
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?";
        
        $result = $this->queryOne($sql, [$userId]);
        return (float)($result['total'] ?? 0);
    }
    
    /**
     * Get cart item count
     */
    public function getItemCount(int $userId): int {
        $sql = "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?";
        $result = $this->queryOne($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Check if product is in cart
     */
    public function isInCart(int $userId, int $productId): bool {
        $sql = "SELECT COUNT(*) as count FROM cart_items WHERE user_id = ? AND product_id = ?";
        $result = $this->queryOne($sql, [$userId, $productId]);
        return $result['count'] > 0;
    }
    
    /**
     * Validate cart stock availability
     */
    public function validateStock(int $userId): array {
        $items = $this->getItems($userId);
        $issues = [];
        
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                $issues[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'available_stock' => $item['stock'],
                    'requested_quantity' => $item['quantity']
                ];
            }
        }
        
        return $issues;
    }
}
