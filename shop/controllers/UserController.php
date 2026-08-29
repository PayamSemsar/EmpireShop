<?php
/**
 * User Controller
 * Handles authentication and user management
 */

class UserController {
    private User $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Show login page
     */
    public function showLogin(): void {
        if (isLoggedIn()) {
            redirect('index.php?page=dashboard');
        }
        
        require_once __DIR__ . '/../views/public/login.php';
    }
    
    /**
     * Process login
     */
    public function login(array $data): void {
        $errors = [];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=login');
        }
        
        // Validate input
        $identifier = sanitizeInput($data['identifier'] ?? '');
        $password = $data['password'] ?? '';
        
        if (empty($identifier)) {
            $errors[] = 'نام کاربری یا ایمیل را وارد کنید';
        }
        
        if (empty($password)) {
            $errors[] = 'رمز عبور را وارد کنید';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=login');
        }
        
        // Verify credentials
        $user = $this->userModel->verifyCredentials($identifier, $password);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('index.php?page=admin/dashboard');
            } else {
                redirect('index.php?page=dashboard');
            }
        } else {
            setFlashMessage('error', 'نام کاربری یا رمز عبور اشتباه است');
            redirect('index.php?page=login');
        }
    }
    
    /**
     * Show register page
     */
    public function showRegister(): void {
        if (isLoggedIn()) {
            redirect('index.php?page=dashboard');
        }
        
        require_once __DIR__ . '/../views/public/register.php';
    }
    
    /**
     * Process registration
     */
    public function register(array $data): void {
        $errors = [];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=register');
        }
        
        // Sanitize inputs
        $username = sanitizeInput($data['username'] ?? '');
        $email = sanitizeInput($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $fullName = sanitizeInput($data['full_name'] ?? '');
        $phone = sanitizeInput($data['phone'] ?? '');
        
        // Validation
        if (empty($username)) {
            $errors[] = 'نام کاربری الزامی است';
        } elseif (!validateUsername($username)) {
            $errors[] = 'نام کاربری باید ۳ تا ۵۰ کاراکتر و فقط شامل حروف، اعداد و زیرخط باشد';
        } elseif ($this->userModel->usernameExists($username)) {
            $errors[] = 'این نام کاربری قبلاً گرفته شده است';
        }
        
        if (empty($email)) {
            $errors[] = 'ایمیل الزامی است';
        } elseif (!validateEmail($email)) {
            $errors[] = 'ایمیل معتبر نیست';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'این ایمیل قبلاً ثبت شده است';
        }
        
        if (empty($password)) {
            $errors[] = 'رمز عبور الزامی است';
        } else {
            $passwordErrors = validatePassword($password);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند';
        }
        
        if (empty($fullName)) {
            $errors[] = 'نام کامل الزامی است';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'شماره تلفن معتبر نیست';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=register');
        }
        
        // Create user
        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'phone' => $phone
        ]);
        
        if ($userId) {
            setFlashMessage('success', 'ثبت‌نام با موفقیت انجام شد. اکنون وارد شوید.');
            redirect('index.php?page=login');
        } else {
            setFlashMessage('error', 'خطا در ثبت‌نام. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=register');
        }
    }
    
    /**
     * Logout
     */
    public function logout(): void {
        // Clear session
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        // Regenerate CSRF token
        regenerateCsrfToken();
        
        redirect('index.php?page=login');
    }
    
    /**
     * Show user dashboard
     */
    public function showDashboard(): void {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        require_once __DIR__ . '/../views/public/dashboard.php';
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(array $data): void {
        requireLogin();
        
        $errors = [];
        $userId = $_SESSION['user_id'];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=dashboard');
        }
        
        // Sanitize inputs
        $fullName = sanitizeInput($data['full_name'] ?? '');
        $phone = sanitizeInput($data['phone'] ?? '');
        $address = sanitizeInput($data['address'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        
        // Validation
        if (empty($fullName)) {
            $errors[] = 'نام کامل الزامی است';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'شماره تلفن معتبر نیست';
        }
        
        if (!empty($password)) {
            $passwordErrors = validatePassword($password);
            $errors = array_merge($errors, $passwordErrors);
            
            if ($password !== $confirmPassword) {
                $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند';
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=dashboard');
        }
        
        // Update user
        $updateData = [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address
        ];
        
        if (!empty($password)) {
            $updateData['password'] = $password;
        }
        
        $success = $this->userModel->update($userId, $updateData);
        
        if ($success) {
            $_SESSION['full_name'] = $fullName;
            setFlashMessage('success', 'اطلاعات پروفایل با موفقیت به‌روزرسانی شد');
        } else {
            setFlashMessage('error', 'خطا در به‌روزرسانی اطلاعات');
        }
        
        redirect('index.php?page=dashboard');
    }
}
