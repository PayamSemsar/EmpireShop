<?php
/**
 * Category Model
 */

require_once __DIR__ . '/Model.php';

class Category extends Model {
    
    /**
     * Get all categories
     */
    public function getAll(): array {
        return $this->all('categories', 'id ASC');
    }
    
    /**
     * Find category by ID
     */
    public function findById(int $id): ?array {
        return $this->find($id, 'categories');
    }
    
    /**
     * Create new category
     */
    public function create(string $name, string $description = null): int {
        return $this->insert('categories', [
            'name' => $name,
            'description' => $description
        ]);
    }
    
    /**
     * Update category
     */
    public function update(int $id, string $name, string $description = null): bool {
        return $this->update('categories', $id, [
            'name' => $name,
            'description' => $description
        ]);
    }
    
    /**
     * Delete category
     */
    public function delete(int $id): bool {
        return $this->delete('categories', $id);
    }
    
    /**
     * Get category with product count
     */
    public function getAllWithProductCount(): array {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        
        return $this->query($sql);
    }
}
