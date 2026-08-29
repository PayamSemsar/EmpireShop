<?php
$pageTitle = 'صفحه اصلی - فروشگاه آنلاین ابزار جانبی';
ob_start();
?>

<!-- Hero Section -->
<section class="bg-gradient-to-l from-dark-800 to-dark-950 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                به <span class="text-primary">فروشگاه ابزار</span> خوش آمدید
            </h1>
            <p class="text-gray-400 text-lg mb-8">
                بهترین و باکیفیت‌ترین لوازم جانبی موبایل و کامپیوتر را از ما بخواهید
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php?page=products" class="bg-primary text-dark-950 px-8 py-3 rounded-lg font-bold hover:bg-amber-500 transition inline-block">
                    مشاهده محصولات
                </a>
                <a href="index.php?page=categories" class="border border-gray-600 text-gray-300 px-8 py-3 rounded-lg font-medium hover:border-primary hover:text-primary transition inline-block">
                    دسته‌بندی‌ها
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-white mb-8 text-center">دسته‌بندی‌های محصولات</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php foreach ($categories as $category): ?>
                <a href="index.php?page=products&category=<?= $category['id'] ?>" 
                   class="bg-dark-800 p-6 rounded-xl border border-gray-700 hover:border-primary transition group text-center">
                    <div class="text-4xl mb-3">📦</div>
                    <h3 class="text-gray-200 font-medium group-hover:text-primary transition"><?= e($category['name']) ?></h3>
                    <p class="text-gray-500 text-sm mt-1"><?= $category['product_count'] ?? 0 ?> محصول</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-12 bg-dark-900">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-white">جدیدترین محصولات</h2>
            <a href="index.php?page=products" class="text-primary hover:text-amber-300 transition text-sm">
                مشاهده همه
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (empty($featuredProducts)): ?>
                <p class="text-gray-400 col-span-full text-center py-8">محصولی یافت نشد</p>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="bg-dark-800 rounded-xl overflow-hidden border border-gray-700 hover:border-primary transition group">
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-700 relative overflow-hidden">
                            <?php if ($product['image_path']): ?>
                                <img src="<?= e($product['image_path']) ?>" 
                                     alt="<?= e($product['name']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                     onerror="this.src='assets/images/placeholder.jpg'">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] <= 0): ?>
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">ناموجود</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-gray-200 font-medium mb-2 line-clamp-2 h-12">
                                <a href="index.php?page=product&id=<?= $product['id'] ?>" class="hover:text-primary transition">
                                    <?= e($product['name']) ?>
                                </a>
                            </h3>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-primary font-bold"><?= formatPrice($product['price']) ?></span>
                                
                                <?php if ($product['stock'] > 0): ?>
                                    <a href="index.php?page=product&id=<?= $product['id'] ?>" 
                                       class="bg-primary/20 text-primary px-3 py-1.5 rounded-lg text-sm hover:bg-primary hover:text-dark-950 transition">
                                        جزئیات
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-dark-800 p-6 rounded-xl border border-gray-700 text-center">
                <div class="text-4xl mb-4">🚚</div>
                <h3 class="text-white font-bold mb-2">ارسال سریع</h3>
                <p class="text-gray-400 text-sm">ارسال به سراسر کشور در کمترین زمان</p>
            </div>
            
            <div class="bg-dark-800 p-6 rounded-xl border border-gray-700 text-center">
                <div class="text-4xl mb-4">✅</div>
                <h3 class="text-white font-bold mb-2">ضمانت اصالت</h3>
                <p class="text-gray-400 text-sm">تمامی محصولات اورجینال هستند</p>
            </div>
            
            <div class="bg-dark-800 p-6 rounded-xl border border-gray-700 text-center">
                <div class="text-4xl mb-4">💳</div>
                <h3 class="text-white font-bold mb-2">پرداخت امن</h3>
                <p class="text-gray-400 text-sm">پرداخت آنلاین با درگاه معتبر</p>
            </div>
            
            <div class="bg-dark-800 p-6 rounded-xl border border-gray-700 text-center">
                <div class="text-4xl mb-4">📞</div>
                <h3 class="text-white font-bold mb-2">پشتیبانی</h3>
                <p class="text-gray-400 text-sm">پاسخگویی در تمام روزهای هفته</p>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/header.php';
?>
