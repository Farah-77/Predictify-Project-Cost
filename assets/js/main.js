/**
 * Main JavaScript File
 * Predictify - ML-Based Project Prediction System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initFormValidation();
    initTooltips();
    initAlertAutoDismiss();
    initConfirmDialogs();
    initNumberFormatting();
});

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    // Password confirmation validation
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');

    if (passwordField && confirmPasswordField) {
        confirmPasswordField.addEventListener('input', function() {
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        });
    }
}

/**
 * Initialize Bootstrap Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Auto-dismiss Alerts
 */
function initAlertAutoDismiss() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');

    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // 5 seconds
    });
}

/**
 * Confirm Dialogs
 */
function initConfirmDialogs() {
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');

    deleteLinks.forEach(link => {
        if (!link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        }
    });
}

/**
 * Number Formatting for Currency
 */
function initNumberFormatting() {
    const currencyInputs = document.querySelectorAll('input[name*="cost"], input[name*="budget"]');

    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value.toFixed(2);
                }
            }
        });
    });
}

/**
 * Format number as currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format number with commas
 */
function formatNumber(number) {
    return new Intl.NumberFormat('en-US').format(number);
}

/**
 * Show loading spinner on form submit
 */
function showLoading(buttonElement, message = 'Processing...') {
    const originalText = buttonElement.innerHTML;
    buttonElement.disabled = true;
    buttonElement.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        ${message}
    `;

    return {
        stop: function() {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalText;
        }
    };
}

/**
 * Show success message
 */
function showSuccess(message) {
    showMessage(message, 'success');
}

/**
 * Show error message
 */
function showError(message) {
    showMessage(message, 'danger');
}

/**
 * Show info message
 */
function showInfo(message) {
    showMessage(message, 'info');
}

/**
 * Show message alert
 */
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
}

/**
 * Validate file upload
 */
function validateFile(input, maxSize = 5242880, allowedExtensions = ['csv']) {
    if (!input.files || !input.files[0]) {
        return { valid: false, error: 'No file selected' };
    }

    const file = input.files[0];
    const extension = file.name.split('.').pop().toLowerCase();

    // Check file size
    if (file.size > maxSize) {
        return {
            valid: false,
            error: `File size exceeds ${maxSize / 1024 / 1024}MB limit`
        };
    }

    // Check extension
    if (!allowedExtensions.includes(extension)) {
        return {
            valid: false,
            error: `Only ${allowedExtensions.join(', ')} files are allowed`
        };
    }

    return { valid: true };
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Copied to clipboard!');
    }).catch(err => {
        showError('Failed to copy to clipboard');
    });
}

/**
 * Download data as CSV
 */
function downloadCSV(data, filename = 'export.csv') {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

/**
 * Convert array of objects to CSV
 */
function convertToCSV(data) {
    if (!data || !data.length) return '';

    const headers = Object.keys(data[0]);
    const csvRows = [headers.join(',')];

    for (const row of data) {
        const values = headers.map(header => {
            const value = row[header];
            return `"${value}"`;
        });
        csvRows.push(values.join(','));
    }

    return csvRows.join('\n');
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Check if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Export functions for use in other scripts
window.Predictify = {
    formatCurrency,
    formatNumber,
    showLoading,
    showSuccess,
    showError,
    showInfo,
    showMessage,
    validateFile,
    smoothScrollTo,
    copyToClipboard,
    downloadCSV,
    debounce,
    isInViewport
};
