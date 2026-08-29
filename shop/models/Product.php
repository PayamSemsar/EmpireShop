<?php
/**
 * Product Model
 */

require_once __DIR__ . '/Model.php';

class Product extends Model {
    
    /**
     * Get all active products with pagination and filters
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 12): array {
        $where = ['p.is_active = 1'];
        $params = [];
        
        // Category filter
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :search OR p.description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Price range filter
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = 'p.price >= :min_price';
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = 'p.price <= :max_price';
            $params['max_price'] = $filters['max_price'];
        }
        
        // Stock filter
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $where[] = 'p.stock > 0';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Sorting
        $orderBy = 'p.created_at DESC';
        $sortOptions = [
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest' => 'p.created_at DESC',
            'name_asc' => 'p.name ASC',
        ];
        
        if (!empty($filters['sort']) && isset($sortOptions[$filters['sort']])) {
            $orderBy = $sortOptions[$filters['sort']];
        }
        
        // Pagination
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE $whereClause 
                ORDER BY $orderBy 
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
     * Get total count with filters
     */
    public function getTotalCount(array $filters = []): int {
        $where = ['is_active = 1'];
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(name LIKE :search OR description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = 'price >= :min_price';
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = 'price <= :max_price';
            $params['max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $where[] = 'stock > 0';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as count FROM products WHERE $whereClause";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
    
    /**
     * Find product by ID with category info
     */
    public function findById(int $id): ?array {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        return $this->queryOne($sql, [$id]);
    }
    
    /**
     * Create new product
     */
    public function create(array $data): int {
        return $this->insert('products', $data);
    }
    
    /**
     * Update product
     */
    public function update(int $id, array $data): bool {
        return $this->update('products', $id, $data);
    }
    
    /**
     * Delete product
     */
    public function delete(int $id): bool {
        return $this->delete('products', $id);
    }
    
    /**
     * Get featured/new products
     */
    public function getFeatured(int $limit = 8): array {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get related products (same category)
     */
    public function getRelated(int $productId, int $limit = 4): array {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                AND p.category_id = (SELECT category_id FROM products WHERE id = ?)
                AND p.id != ? 
                ORDER BY RAND() 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $productId, PDO::PARAM_INT);
        $stmt->bindValue(2, $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check stock availability
     */
    public function checkStock(int $productId, int $quantity): bool {
        $product = $this->find($productId, 'products');
        return $product && $product['stock'] >= $quantity;
    }
    
    /**
     * Decrease stock
     */
    public function decreaseStock(int $productId, int $quantity): bool {
        $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'quantity' => $quantity,
            'id' => $productId
        ]);
    }
    
    /**
     * Get all products for admin
     */
    public function getAllForAdmin(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
