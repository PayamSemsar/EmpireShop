<?php
/**
 * Product Controller
 * Handles product display and management
 */

class ProductController {
    private Product $productModel;
    private Category $categoryModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Show homepage with featured products
     */
    public function index(): void {
        $featuredProducts = $this->productModel->getFeatured(8);
        $categories = $this->categoryModel->getAllWithProductCount();
        
        require_once __DIR__ . '/../views/public/index.php';
    }
    
    /**
     * Show products listing page
     */
    public function showProducts(): void {
        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'search' => $_GET['search'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'in_stock' => isset($_GET['in_stock']) ? true : false,
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        
        $products = $this->productModel->getAll($filters, $page, $perPage);
        $totalCount = $this->productModel->getTotalCount($filters);
        $totalPages = ceil($totalCount / $perPage);
        
        $categories = $this->categoryModel->getAll();
        
        require_once __DIR__ . '/../views/public/products.php';
    }
    
    /**
     * Show product detail page
     */
    public function showProduct(int $id): void {
        $product = $this->productModel->findById($id);
        
        if (!$product || !$product['is_active']) {
            http_response_code(404);
            setFlashMessage('error', 'محصول مورد نظر یافت نشد');
            redirect('index.php?page=products');
        }
        
        $relatedProducts = $this->productModel->getRelated($id, 4);
        
        require_once __DIR__ . '/../views/public/product-detail.php';
    }
    
    /**
     * Admin: Show all products
     */
    public function adminIndex(): void {
        requireAdmin();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        $products = $this->productModel->getAllForAdmin($page, $perPage);
        $totalCount = $this->productModel->count('products');
        $totalPages = ceil($totalCount / $perPage);
        
        require_once __DIR__ . '/../views/admin/products.php';
    }
    
    /**
     * Admin: Show create product form
     */
    public function adminCreate(): void {
        requireAdmin();
        
        $categories = $this->categoryModel->getAll();
        
        require_once __DIR__ . '/../views/admin/product-form.php';
    }
    
    /**
     * Admin: Create product
     */
    public function adminStore(array $data, array $files): void {
        requireAdmin();
        
        $errors = [];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=admin/products');
        }
        
        // Sanitize inputs
        $name = sanitizeInput($data['name'] ?? '');
        $description = sanitizeInput($data['description'] ?? '');
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $categoryId = $data['category_id'] ?? null;
        $isActive = isset($data['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($name)) {
            $errors[] = 'نام محصول الزامی است';
        }
        
        if (!validateFloat($price, 0)) {
            $errors[] = 'قیمت معتبر نیست';
        }
        
        if (!validateInt($stock, 0)) {
            $errors[] = 'موجودی معتبر نیست';
        }
        
        if (empty($categoryId)) {
            $errors[] = 'دسته‌بندی الزامی است';
        }
        
        // Handle image upload
        $imagePath = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($files['image']);
            
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=admin/products/create');
        }
        
        // Create product
        $productId = $this->productModel->create([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'is_active' => $isActive
        ]);
        
        if ($productId) {
            setFlashMessage('success', 'محصول با موفقیت ایجاد شد');
            redirect('index.php?page=admin/products');
        } else {
            setFlashMessage('error', 'خطا در ایجاد محصول');
            redirect('index.php?page=admin/products/create');
        }
    }
    
    /**
     * Admin: Show edit product form
     */
    public function adminEdit(int $id): void {
        requireAdmin();
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            setFlashMessage('error', 'محصول یافت نشد');
            redirect('index.php?page=admin/products');
        }
        
        $categories = $this->categoryModel->getAll();
        
        require_once __DIR__ . '/../views/admin/product-form.php';
    }
    
    /**
     * Admin: Update product
     */
    public function adminUpdate(int $id, array $data, array $files): void {
        requireAdmin();
        
        $errors = [];
        
        // Validate CSRF
        if (!verifyCsrfToken($data['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی. لطفاً مجدد تلاش کنید.');
            redirect('index.php?page=admin/products');
        }
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            setFlashMessage('error', 'محصول یافت نشد');
            redirect('index.php?page=admin/products');
        }
        
        // Sanitize inputs
        $name = sanitizeInput($data['name'] ?? '');
        $description = sanitizeInput($data['description'] ?? '');
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $categoryId = $data['category_id'] ?? null;
        $isActive = isset($data['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($name)) {
            $errors[] = 'نام محصول الزامی است';
        }
        
        if (!validateFloat($price, 0)) {
            $errors[] = 'قیمت معتبر نیست';
        }
        
        if (!validateInt($stock, 0)) {
            $errors[] = 'موجودی معتبر نیست';
        }
        
        if (empty($categoryId)) {
            $errors[] = 'دسته‌بندی الزامی است';
        }
        
        // Handle image upload
        $imagePath = $product['image_path'];
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            if ($imagePath && file_exists(__DIR__ . '/../' . $imagePath)) {
                unlink(__DIR__ . '/../' . $imagePath);
            }
            
            $uploadResult = $this->handleImageUpload($files['image']);
            
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect('index.php?page=admin/products/edit&id=' . $id);
        }
        
        // Update product
        $success = $this->productModel->update($id, [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'is_active' => $isActive
        ]);
        
        if ($success) {
            setFlashMessage('success', 'محصول با موفقیت به‌روزرسانی شد');
        } else {
            setFlashMessage('error', 'خطا در به‌روزرسانی محصول');
        }
        
        redirect('index.php?page=admin/products');
    }
    
    /**
     * Admin: Delete product
     */
    public function adminDelete(int $id): void {
        requireAdmin();
        
        // Validate CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'خطای امنیتی');
            redirect('index.php?page=admin/products');
        }
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            setFlashMessage('error', 'محصول یافت نشد');
            redirect('index.php?page=admin/products');
        }
        
        // Delete image file
        if ($product['image_path'] && file_exists(__DIR__ . '/../' . $product['image_path'])) {
            unlink(__DIR__ . '/../' . $product['image_path']);
        }
        
        $success = $this->productModel->delete($id);
        
        if ($success) {
            setFlashMessage('success', 'محصول با موفقیت حذف شد');
        } else {
            setFlashMessage('error', 'خطا در حذف محصول');
        }
        
        redirect('index.php?page=admin/products');
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload(array $file): array {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Check file type
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'فرمت فایل مجاز نیست (فقط jpg, jpeg, png, webp)'
            ];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => 'حجم فایل باید کمتر از ۲ مگابایت باشد'
            ];
        }
        
        // Generate new filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        
        // Upload directory
        $uploadDir = __DIR__ . '/../assets/images/products/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $newFilename;
        $relativePath = 'assets/images/products/' . $newFilename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'path' => $relativePath
            ];
        }
        
        return [
            'success' => false,
            'error' => 'خطا در آپلود فایل'
        ];
    }
}
