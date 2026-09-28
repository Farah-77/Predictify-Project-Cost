<?php
/**
 * Logout Script
 * Redirects to process_auth.php
 */

require_once '../config/settings.php';

// Destroy session
session_unset();
session_destroy();

// Start new session for flash message
session_start();
setFlashMessage('You have been logged out successfully', 'success');

redirect('/index.php');
?>
