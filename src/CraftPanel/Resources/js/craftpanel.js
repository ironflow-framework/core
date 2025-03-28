/**
 * CraftPanel JavaScript
 * 
 * This file contains all the JavaScript functionality for the CraftPanel administration interface.
 * It includes theme switching, sidebar toggling, dropdown handling, and other interactive features.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Theme Switcher
    initThemeSwitcher();
    
    // Sidebar Toggle
    initSidebarToggle();
    
    // Dropdowns
    initDropdowns();
    
    // Flash Messages
    initFlashMessages();
    
    // Confirm Dialogs
    initConfirmDialogs();
    
    // File Input Preview
    initFileInputPreview();
    
    // Select All Checkboxes
    initSelectAllCheckboxes();
});

/**
 * Initialize theme switcher functionality
 */
function initThemeSwitcher() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;
    
    themeToggle.addEventListener('click', function() {
        const htmlElement = document.documentElement;
        const currentTheme = htmlElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        htmlElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/**
 * Initialize sidebar toggle functionality for mobile
 */
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if (!sidebarToggle || !sidebar) return;
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        if (sidebarOverlay) {
            sidebarOverlay.classList.toggle('hidden');
        }
    });
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isMobile = window.innerWidth < 768;
        if (isMobile && sidebar && !sidebar.contains(event.target) && event.target !== sidebarToggle) {
            sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('hidden');
            }
        }
    });
}

/**
 * Initialize dropdown functionality
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown:not(.dropdown-hover)');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const content = dropdown.querySelector('.dropdown-content');
        
        if (!trigger || !content) return;
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            content.classList.toggle('hidden');
            
            // Close other dropdowns
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    const otherContent = otherDropdown.querySelector('.dropdown-content');
                    if (otherContent) {
                        otherContent.classList.add('hidden');
                    }
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            const content = dropdown.querySelector('.dropdown-content');
            if (content) {
                content.classList.add('hidden');
            }
        });
    });
}

/**
 * Initialize auto-dismissing flash messages
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.alert[data-auto-dismiss]');
    
    flashMessages.forEach(message => {
        const dismissTime = message.getAttribute('data-auto-dismiss') || 5000;
        
        setTimeout(() => {
            message.classList.add('opacity-0');
            setTimeout(() => {
                message.remove();
            }, 300);
        }, dismissTime);
    });
    
    // Add close button functionality
    const closeButtons = document.querySelectorAll('.alert .close-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.classList.add('opacity-0');
            setTimeout(() => {
                alert.remove();
            }, 300);
        });
    });
}

/**
 * Initialize confirm dialogs for delete actions
 */
function initConfirmDialogs() {
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Initialize file input preview functionality
 */
function initFileInputPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        const previewElement = document.getElementById(input.getAttribute('data-preview'));
        if (!previewElement) return;
        
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (previewElement.tagName === 'IMG') {
                        previewElement.src = e.target.result;
                    } else {
                        previewElement.style.backgroundImage = `url(${e.target.result})`;
                    }
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
}

/**
 * Initialize select all checkboxes functionality
 */
function initSelectAllCheckboxes() {
    const selectAllCheckboxes = document.querySelectorAll('[data-select-all]');
    
    selectAllCheckboxes.forEach(checkbox => {
        const targetName = checkbox.getAttribute('data-select-all');
        const targetCheckboxes = document.querySelectorAll(`input[name="${targetName}"]`);
        
        checkbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            targetCheckboxes.forEach(targetCheckbox => {
                targetCheckbox.checked = isChecked;
            });
        });
        
        // Update select all checkbox when individual checkboxes change
        targetCheckboxes.forEach(targetCheckbox => {
            targetCheckbox.addEventListener('change', function() {
                const allChecked = Array.from(targetCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(targetCheckboxes).some(cb => cb.checked);
                
                checkbox.checked = allChecked;
                checkbox.indeterminate = someChecked && !allChecked;
            });
        });
    });
}

/**
 * Show a notification toast
 * 
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, info, warning)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} shadow-lg transition-opacity duration-300`;
    notification.innerHTML = `
        <div class="flex w-full justify-between items-start">
            <div class="flex items-start">
                <i class="ti ti-${type === 'success' ? 'check' : type === 'error' ? 'alert-circle' : type === 'warning' ? 'alert-triangle' : 'info-circle'} mt-0.5 mr-2"></i>
                <span>${message}</span>
            </div>
            <button class="close-btn ml-4 -mt-1 text-lg font-bold">&times;</button>
        </div>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Add close button functionality
    const closeButton = notification.querySelector('.close-btn');
    closeButton.addEventListener('click', () => {
        notification.classList.add('opacity-0');
        setTimeout(() => notification.remove(), 300);
    });
    
    // Auto dismiss
    setTimeout(() => {
        notification.classList.add('opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

/**
 * Format a date using the browser's Intl.DateTimeFormat
 * 
 * @param {string|Date} date - The date to format
 * @param {string} format - The format to use (short, medium, long, full)
 * @param {string} locale - The locale to use (defaults to document.documentElement.lang)
 * @returns {string} - The formatted date
 */
function formatDate(date, format = 'medium', locale = document.documentElement.lang || 'en') {
    const dateObj = date instanceof Date ? date : new Date(date);
    
    const options = {
        short: { year: 'numeric', month: 'numeric', day: 'numeric' },
        medium: { year: 'numeric', month: 'short', day: 'numeric' },
        long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },
        full: { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            weekday: 'long', 
            hour: '2-digit', 
            minute: '2-digit'
        }
    };
    
    return new Intl.DateTimeFormat(locale, options[format]).format(dateObj);
}

/**
 * Format a number as currency
 * 
 * @param {number} amount - The amount to format
 * @param {string} currency - The currency code (USD, EUR, etc.)
 * @param {string} locale - The locale to use (defaults to document.documentElement.lang)
 * @returns {string} - The formatted currency amount
 */
function formatCurrency(amount, currency = 'USD', locale = document.documentElement.lang || 'en') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Export the public API
 */
window.CraftPanel = {
    showNotification,
    formatDate,
    formatCurrency
};
