<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'فروشگاه آنلاین ابزار جانبی') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Headless UI -->
    <script src="https://unpkg.com/@headlessui/react@1.7.17/dist/headlessui.min.js"></script>
    
    <!-- Custom Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#fbbf24', // Amber-400
                        dark: {
                            950: '#0d0d0d',
                            900: '#1a1a1a',
                            800: '#222222',
                        }
                    },
                    fontFamily: {
                        sans: ['Vazirmatn', 'Tahoma', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #fbbf24;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #f59e0b;
        }
    </style>
</head>
<body class="bg-dark-950 text-gray-100 min-h-screen flex flex-col">
    
    <!-- Navbar -->
    <?php require_once __DIR__ . '/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-grow">
        <!-- Flash Messages -->
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="container mx-auto px-4 mt-4">
                <div class="max-w-4xl mx-auto">
                    <div class="p-4 rounded-lg mb-4 <?= $flash['type'] === 'success' ? 'bg-green-900/50 border border-green-700 text-green-100' : 'bg-red-900/50 border border-red-700 text-red-100' ?>">
                        <div class="flex items-center justify-between">
                            <span><?= $flash['message'] ?></span>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-white">&times;</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <?php require_once __DIR__ . '/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>
