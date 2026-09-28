<?php
/**
 * Authentication Check
 * Protects pages from unauthorized access
 */

require_once dirname(__DIR__) . '/config/settings.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please login to access this page', 'warning');
    redirect('/auth/login.php');
}

// Optional: Check for specific role
if (isset($requireAdmin) && $requireAdmin === true) {
    if (!isAdmin()) {
        setFlashMessage('Access denied. Admin privileges required.', 'danger');
        redirect('/user/add_project.php');
    }
}

if (isset($requireUser) && $requireUser === true) {
    if (isAdmin()) {
        redirect('/admin/dashboard.php');
    }
}
?>
