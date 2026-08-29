<?php
/**
 * Security Functions
 * CSRF Protection, Input Validation, XSS Prevention
 */

/**
 * Generate CSRF Token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF Token
 */
function regenerateCsrfToken(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Sanitize Output (XSS Prevention)
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Validate Email
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Phone Number (Iranian format)
 */
function validatePhone(string $phone): bool {
    return preg_match('/^09[0-9]{9}$/', $phone) || preg_match('/^\+989[0-9]{9}$/', $phone);
}

/**
 * Validate Username
 */
function validateUsername(string $username): bool {
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

/**
 * Validate Password Strength
 */
function validatePassword(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد';
    }
    
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = 'رمز عبور باید شامل حروف انگلیسی باشد';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'رمز عبور باید شامل اعداد باشد';
    }
    
    return $errors;
}

/**
 * Sanitize Input
 */
function sanitizeInput(string $input): string {
    return trim(strip_tags($input));
}

/**
 * Validate Integer
 */
function validateInt(mixed $value, int $min = null, int $max = null): bool {
    if (!is_numeric($value) || !is_int((int)$value)) {
        return false;
    }
    
    $intValue = (int)$value;
    
    if ($min !== null && $intValue < $min) {
        return false;
    }
    
    if ($max !== null && $intValue > $max) {
        return false;
    }
    
    return true;
}

/**
 * Validate Float/Decimal
 */
function validateFloat(mixed $value, float $min = null, float $max = null): bool {
    if (!is_numeric($value)) {
        return false;
    }
    
    $floatValue = (float)$value;
    
    if ($min !== null && $floatValue < $min) {
        return false;
    }
    
    if ($max !== null && $floatValue > $max) {
        return false;
    }
    
    return true;
}

/**
 * Hash Password
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify Password
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Generate Random String
 */
function generateRandomString(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate Tracking Code
 */
function generateTrackingCode(): string {
    return 'TRK-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(1000, 9999);
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require Login
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('index.php?page=login');
    }
}

/**
 * Require Admin
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('index.php?page=login');
    }
}

/**
 * Redirect Helper
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Set Flash Message
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and Clear Flash Message
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format Price in Tomans
 */
function formatPrice(float|int $price): string {
    return number_format($price) . ' ' . CURRENCY;
}

/**
 * Format Date (Jalali/Persian)
 */
function formatDate(string $date): string {
    $timestamp = strtotime($date);
    return date('Y/m/d H:i', $timestamp);
}
