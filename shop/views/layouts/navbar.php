<?php
$cartItemCount = 0;
if (isLoggedIn()) {
    $cartModel = new Cart();
    $cartItemCount = $cartModel->getItemCount($_SESSION['user_id']);
}
?>

<nav class="bg-dark-800 border-b border-gray-800 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-2 space-x-reverse">
                <span class="text-primary text-2xl font-bold">🛒 فروشگاه ابزار</span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                <a href="index.php?page=products" class="text-gray-300 hover:text-primary transition">محصولات</a>
                <a href="index.php?page=categories" class="text-gray-300 hover:text-primary transition">دسته‌بندی‌ها</a>
                
                <?php if (isLoggedIn()): ?>
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 space-x-reverse text-gray-300 hover:text-primary transition">
                            <span><?= e($_SESSION['full_name'] ?? $_SESSION['username']) ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div class="absolute left-0 mt-2 w-48 bg-dark-800 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-gray-700">
                            <a href="index.php?page=dashboard" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-primary rounded-t-lg transition">داشبورد</a>
                            <a href="index.php?page=orders" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-primary transition">سفارشات من</a>
                            <a href="index.php?page=logout" class="block px-4 py-2 text-red-400 hover:bg-gray-700 rounded-b-lg transition">خروج</a>
                        </div>
                    </div>
                    
                    <!-- Cart Icon -->
                    <a href="index.php?page=cart" class="relative text-gray-300 hover:text-primary transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <?php if ($cartItemCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-primary text-dark-950 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= $cartItemCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="text-gray-300 hover:text-primary transition">ورود</a>
                    <a href="index.php?page=register" class="bg-primary text-dark-950 px-4 py-2 rounded-lg font-medium hover:bg-amber-500 transition">ثبت‌نام</a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="text-gray-300 hover:text-primary focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="hidden md:hidden pb-4">
            <div class="flex flex-col space-y-2">
                <a href="index.php?page=products" class="text-gray-300 hover:text-primary transition py-2">محصولات</a>
                <a href="index.php?page=categories" class="text-gray-300 hover:text-primary transition py-2">دسته‌بندی‌ها</a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="index.php?page=dashboard" class="text-gray-300 hover:text-primary transition py-2">داشبورد</a>
                    <a href="index.php?page=orders" class="text-gray-300 hover:text-primary transition py-2">سفارشات من</a>
                    <a href="index.php?page=cart" class="text-gray-300 hover:text-primary transition py-2 flex items-center">
                        سبد خرید
                        <?php if ($cartItemCount > 0): ?>
                            <span class="mr-2 bg-primary text-dark-950 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= $cartItemCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=logout" class="text-red-400 hover:text-red-300 transition py-2">خروج</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="text-gray-300 hover:text-primary transition py-2">ورود</a>
                    <a href="index.php?page=register" class="bg-primary text-dark-950 px-4 py-2 rounded-lg font-medium hover:bg-amber-500 transition inline-block text-center">ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
