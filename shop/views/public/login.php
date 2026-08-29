<?php
/**
 * Login Page View
 */
$pageTitle = 'ورود به حساب کاربری';
ob_start();
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Login Card -->
        <div class="bg-dark-800 rounded-xl border border-gray-700 p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">ورود به حساب کاربری</h1>
                <p class="text-gray-400 text-sm">خوش آمدید! لطفاً وارد شوید</p>
            </div>
            
            <!-- Login Form -->
            <form method="POST" action="index.php?page=login" data-validate>
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <!-- Identifier (Username or Email) -->
                <div class="mb-4">
                    <label for="identifier" class="block text-gray-300 text-sm font-medium mb-2">
                        نام کاربری یا ایمیل
                    </label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        required
                        placeholder="نام کاربری یا ایمیل خود را وارد کنید"
                        class="w-full bg-dark-900 border border-gray-700 text-gray-100 rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        value="<?= $_SESSION['old_identifier'] ?? '' ?>"
                    >
                    <?php unset($_SESSION['old_identifier']); ?>
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-300 text-sm font-medium mb-2">
                        رمز عبور
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="رمز عبور خود را وارد کنید"
                            class="w-full bg-dark-900 border border-gray-700 text-gray-100 rounded-lg px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                        >
                        <button 
                            type="button" 
                            data-password-toggle="#password"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300"
                        >
                            <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg class="eye-slash w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-primary text-dark-950 font-bold py-3 rounded-lg hover:bg-amber-500 transition"
                >
                    ورود
                </button>
            </form>
            
            <!-- Links -->
            <div class="mt-6 text-center">
                <p class="text-gray-400 text-sm">
                    حساب کاربری ندارید؟
                    <a href="index.php?page=register" class="text-primary hover:text-amber-300 transition">ثبت‌نام کنید</a>
                </p>
            </div>
        </div>
        
        <!-- Demo Credentials -->
        <div class="mt-6 bg-dark-800/50 rounded-lg border border-gray-700 p-4">
            <h3 class="text-gray-300 font-medium mb-2 text-sm">اطلاعات ورود ادمین (برای تست):</h3>
            <div class="text-gray-400 text-xs space-y-1">
                <p>نام کاربری: <code class="bg-dark-900 px-2 py-1 rounded">admin</code></p>
                <p>رمز عبور: <code class="bg-dark-900 px-2 py-1 rounded">admin123</code></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/header.php';
?>
