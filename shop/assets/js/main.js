/**
 * Main JavaScript for Accessories Shop
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('[data-flash]');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.3s ease';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('آیا از حذف این مورد اطمینان دارید؟')) {
                e.preventDefault();
            }
        });
    });
    
    // Quantity input controls
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(function(input) {
        const minusBtn = input.parentElement.querySelector('.quantity-minus');
        const plusBtn = input.parentElement.querySelector('.quantity-plus');
        
        if (minusBtn) {
            minusBtn.addEventListener('click', function() {
                const min = parseInt(input.getAttribute('min')) || 1;
                const newValue = parseInt(input.value) - 1;
                if (newValue >= min) {
                    input.value = newValue;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', function() {
                const max = parseInt(input.getAttribute('max')) || 999;
                const newValue = parseInt(input.value) + 1;
                if (newValue <= max) {
                    input.value = newValue;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(function(field) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !emailRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Image preview for file inputs
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const previewContainer = input.parentElement.querySelector('.image-preview');
            if (previewContainer && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = '<img src="' + e.target.result + '" class="max-h-48 rounded-lg" alt="Preview">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Search functionality with debounce
    const searchInputs = document.querySelectorAll('[data-search-target]');
    searchInputs.forEach(function(input) {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                const targetForm = document.querySelector(input.dataset.searchTarget);
                if (targetForm) {
                    targetForm.submit();
                }
            }, 500);
        });
    });
    
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('[data-mobile-menu]');
    const mobileMenu = document.querySelector('[data-mobile-menu-content]');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('[data-password-toggle]');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = document.querySelector(this.dataset.passwordToggle);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Toggle icon
                const eyeIcon = this.querySelector('.eye-open');
                const eyeSlashIcon = this.querySelector('.eye-slash');
                if (eyeIcon && eyeSlashIcon) {
                    eyeIcon.classList.toggle('hidden');
                    eyeSlashIcon.classList.toggle('hidden');
                }
            }
        });
    });
    
});

/**
 * Show loading state on buttons
 */
function showLoading(button, text = 'در حال پردازش...') {
    button.disabled = true;
    button.originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner inline-block w-4 h-4 mr-2"></span>' + text;
}

/**
 * Hide loading state on buttons
 */
function hideLoading(button) {
    button.disabled = false;
    button.innerHTML = button.originalText;
}

/**
 * Format price with commas
 */
function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * AJAX helper
 */
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('کپی شد');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 left-4 bg-dark-800 border border-gray-700 text-gray-100 px-6 py-3 rounded-lg shadow-lg z-50';
    
    if (type === 'success') {
        toast.classList.add('border-green-700', 'text-green-100');
    } else if (type === 'error') {
        toast.classList.add('border-red-700', 'text-red-100');
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}
