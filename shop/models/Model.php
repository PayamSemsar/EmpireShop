<?php
/**
 * Base Model Class
 * Provides common database operations
 */

class Model {
    protected PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    protected function find(int $id, string $table): ?array {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get all records from table
     */
    protected function all(string $table, string $orderBy = 'id DESC', int $limit = null): array {
        $sql = "SELECT * FROM $table";
        
        if ($limit !== null) {
            $sql .= " ORDER BY $orderBy LIMIT $limit";
        } else {
            $sql .= " ORDER BY $orderBy";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert record
     */
    protected function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    protected function update(string $table, int $id, array $data): bool {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = :$column";
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE $table SET $setString WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Delete record
     */
    protected function delete(string $table, int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM $table WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Count records
     */
    protected function count(string $table, string $where = null): int {
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
    
    /**
     * Custom query with prepared statements
     */
    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Query single record
     */
    protected function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
