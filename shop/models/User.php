<?php
/**
 * User Model
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    
    /**
     * Find user by username or email
     */
    public function findByUsernameOrEmail(string $identifier): ?array {
        $sql = "SELECT * FROM users WHERE username = :identifier OR email = :identifier";
        return $this->queryOne($sql, ['identifier' => $identifier]);
    }
    
    /**
     * Find user by ID
     */
    public function findById(int $id): ?array {
        return $this->find($id, 'users');
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists(string $username, int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email, int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Create new user
     */
    public function create(array $data): int {
        $data['password'] = hashPassword($data['password']);
        $data['role'] = $data['role'] ?? 'customer';
        return $this->insert('users', $data);
    }
    
    /**
     * Update user
     */
    public function update(int $id, array $data): bool {
        // If password is being updated, hash it
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = hashPassword($data['password']);
        } else {
            unset($data['password']);
        }
        
        return $this->update('users', $id, $data);
    }
    
    /**
     * Delete user
     */
    public function delete(int $id): bool {
        return $this->delete('users', $id);
    }
    
    /**
     * Get all users with pagination
     */
    public function getAll(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id, username, email, full_name, phone, role, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get total user count
     */
    public function getTotalCount(): int {
        return $this->count('users');
    }
    
    /**
     * Update user address
     */
    public function updateAddress(int $userId, string $address): bool {
        return $this->update($userId, ['address' => $address]);
    }
    
    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $identifier, string $password): ?array {
        $user = $this->findByUsernameOrEmail($identifier);
        
        if ($user && verifyPassword($password, $user['password'])) {
            return $user;
        }
        
        return null;
    }
}
