# فروشگاه آنلاین ابزار جانبی - راهنمای نصب و راه‌اندازی

## 📋 پیش‌نیازها

- PHP 8.x یا بالاتر
- MySQL 5.7+ یا MariaDB 10.3+
- Apache با ماژول mod_rewrite فعال (برای URL rewriting)
- CURL Extension برای PHP (برای اتصال به درگاه پرداخت)
- GD یا Imagick Extension (برای پردازش تصاویر)

## 🚀 مراحل نصب

### 1. ایجاد دیتابیس

ابتدا وارد phpMyAdmin یا MySQL CLI شوید و دستور زیر را اجرا کنید:

```bash
mysql -u root -p < database.sql
```

یا فایل `database.sql` را در phpMyAdmin ایمپورت کنید.

### 2. تنظیمات دیتابیس

فایل `config/database.php` را باز کرده و اطلاعات دیتابیس خود را وارد کنید:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'accessories_shop');
define('DB_USER', 'root');
define('DB_PASS', ''); // رمز عبور دیتابیس خود را وارد کنید
```

### 3. تنظیمات درگاه پرداخت زیبال

فایل `config/zibal.php` را باز کرده و Merchant ID خود را وارد کنید:

```php
define('ZIBAL_MERCHANT', 'your-merchant-id-here');
define('ZIBAL_SANDBOX', true); // برای محیط تست true بگذارید
```

برای دریافت Merchant ID به [زیبال](https://zibal.ir) مراجعه کنید.

### 4. تنظیم مجوزهای پوشه‌ها

مطمئن شوید پوشه‌های زیر قابل نوشتن هستند:

```bash
chmod -R 755 /path/to/shop/assets/images/
chmod 644 /path/to/shop/config/*.php
```

### 5. فعال‌سازی mod_rewrite (Apache)

اگر از Apache استفاده می‌کنید، مطمئن شوید mod_rewrite فعال است:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

همچنین در فایل `.htaccess` مسیر صحیح را تنظیم کنید:

```apache
RewriteBase /shop/
```

## 👤 حساب‌های کاربری پیش‌فرض

### ادمین:
- **نام کاربری:** `admin`
- **رمز عبور:** `admin123`
- **ایمیل:** `admin@shop.com`

## 📁 ساختار پروژه

```
shop/
├── assets/              # فایل‌های استاتیک (CSS, JS, تصاویر)
│   ├── css/
│   ├── js/
│   └── images/
│       └── products/    # تصاویر محصولات
├── config/              # فایل‌های پیکربندی
│   ├── database.php     # تنظیمات دیتابیس
│   └── zibal.php        # تنظیمات درگاه پرداخت
├── controllers/         # کنترلرها (منطق کسب‌وکار)
├── includes/            # توابع کمکی
├── models/              # مدل‌ها (دسترسی به دیتابیس)
├── views/               # ویوها (HTML/PHP templates)
│   ├── layouts/         # قالب‌های مشترک (header, footer, navbar)
│   ├── public/          # صفحات عمومی
│   └── admin/           # صفحات پنل ادمین
├── .htaccess            # قوانین URL rewriting
├── database.sql         # اسکریپت دیتابیس
└── index.php            # نقطه ورود اصلی (Router)
```

## 🔐 ویژگی‌های امنیتی

- ✅ Prepared Statements (جلوگیری از SQL Injection)
- ✅ CSRF Protection (توکن CSRF در تمام فرم‌ها)
- ✅ XSS Prevention (htmlspecialchars برای output)
- ✅ Password Hashing (bcrypt با cost=12)
- ✅ Session Security (session_regenerate_id)
- ✅ Input Validation (سمت سرور)
- ✅ File Upload Validation (بررسی نوع و سایز فایل)

## 💳 درگاه پرداخت زیبال

### محیط تست (Sandbox):
- آدرس: https://sandbox.zibal.ir
- برای ثبت‌نام و دریافت Merchant ID به سایت زیبال مراجعه کنید

### محیط تولید (Production):
- آدرس: https://api.zibal.ir
- پس از تکمیل مراحل احراز هویت، `ZIBAL_SANDBOX` را به `false` تغییر دهید

## 🎨 طراحی UI

- **تم:** Dark Mode
- **رنگ پس‌زمینه:** `#0d0d0d` (bg-gray-950)
- **رنگ باکس‌ها:** `#222222` (bg-neutral-800)
- **رنگ Accent:** Amber-400 (`#fbbf24`)
- **فونت:** Vazirmatn (فارسی)
- **Framework:** Tailwind CSS + HeadlessUI
- **Responsive:** Mobile-first design

## 🛠️ توسعه

### افزودن محصول جدید از پنل ادمین:

1. وارد حساب ادمین شوید
2. به منوی "مدیریت محصولات" بروید
3. روی "افزودن محصول جدید" کلیک کنید
4. اطلاعات محصول را وارد کنید
5. تصویر محصول را آپلود کنید (حداکثر 2MB)
6. ذخیره کنید

### تغییر تم و رنگ‌ها:

فایل `views/layouts/header.php` را باز کرده و مقادیر tailwind.config را تغییر دهید.

## 📊 صفحات موجود

### عمومی:
- صفحه اصلی (Home)
- لیست محصولات (Products)
- جزئیات محصول (Product Detail)
- سبد خرید (Cart)
- تسویه حساب (Checkout)
- ورود/ثبت‌نام (Login/Register)
- داشبورد کاربری (User Dashboard)
- سفارشات من (My Orders)

### ادمین:
- داشبورد (Dashboard)
- مدیریت محصولات (CRUD)
- مدیریت سفارشات
- مدیریت کاربران
- مدیریت دسته‌بندی‌ها

## 🔧 عیب‌یابی

### خطای اتصال به دیتابیس:
- اطلاعات دیتابیس را در `config/database.php` بررسی کنید
- مطمئن شوید دیتابیس ایجاد شده است
- دسترسی‌های کاربر دیتابیس را بررسی کنید

### خطای 404 در صفحات:
- مطمئن شوید mod_rewrite فعال است
- فایل `.htaccess` وجود دارد
- مسیر `RewriteBase` را در `.htaccess` بررسی کنید

### خطا در آپلود تصویر:
- مجوز پوشه `assets/images/products/` را بررسی کنید
- حداکثر سایز فایل را در `php.ini` بررسی کنید:
  ```ini
  upload_max_filesize = 2M
  post_max_size = 3M
  ```

### خطا در پرداخت:
- Merchant ID را در `config/zibal.php` بررسی کنید
- اتصال به اینترنت را بررسی کنید
- CURL Extension فعال باشد

## 📞 پشتیبانی

برای گزارش باگ یا درخواست ویژگی‌های جدید، لطفاً با تیم توسعه تماس بگیرید.

---

**نسخه:** 1.0.0  
**توسعه‌دهنده:** تیم فروشگاه ابزار  
**لایسنس:** MIT
