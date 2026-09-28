<?php
/**
 * Global Settings and Constants
 * Predictify - ML-Based Project Prediction System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Base filesystem path (REAL path on disk)
|--------------------------------------------------------------------------
*/
define('BASE_PATH', realpath(__DIR__ . '/..'));

/*
|--------------------------------------------------------------------------
| Filesystem paths (USED FOR mkdir, upload, saving files)
|--------------------------------------------------------------------------
*/
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads');
define('MODEL_PATH', BASE_PATH . '/models');

define('DATASET_PATH', BASE_PATH . '/models/datasets');
define('SAVED_MODELS_PATH', BASE_PATH . '/models/saved_models');

/*
|--------------------------------------------------------------------------
| Create directories if they don't exist
|--------------------------------------------------------------------------
*/
$directories = [
    UPLOAD_PATH,
    DATASET_PATH,
    SAVED_MODELS_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/*
|--------------------------------------------------------------------------
| URL paths (USED IN HTML / CSS / JS)
|--------------------------------------------------------------------------
*/

define('BASE_URL', '/');
define('ASSETS_URL', '/assets');
define('CSS_URL', '/assets/css');
define('JS_URL', '/assets/js');
define('IMG_URL', '/assets/images');

/*
|--------------------------------------------------------------------------
| Python configuration
|--------------------------------------------------------------------------
*/

define('PYTHON_EXECUTABLE', 'python');
define('TRAIN_SCRIPT', 'models/train_models.py');
define('PREDICT_SCRIPT', 'models/predict.py');

/*
|--------------------------------------------------------------------------
| File upload settings
|--------------------------------------------------------------------------
*/
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['csv']);

/*
|--------------------------------------------------------------------------
| Model settings
|--------------------------------------------------------------------------
*/
define('AVAILABLE_MODELS', ['LinearRegression', 'RandomForest', 'GradientBoosting']);
define('MIN_TRAINING_RECORDS', 10);

/*
|--------------------------------------------------------------------------
| Application settings
|--------------------------------------------------------------------------
*/
define('APP_NAME', 'Predictify');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'ML-Based Project Cost and Duration Prediction System');

/*
|--------------------------------------------------------------------------
| Complexity levels
|--------------------------------------------------------------------------
*/
define('COMPLEXITY_LEVELS', [
    1 => 'Very Simple',
    2 => 'Simple',
    3 => 'Moderate',
    4 => 'Complex',
    5 => 'Very Complex'
]);

/*
|--------------------------------------------------------------------------
| Color scheme
|--------------------------------------------------------------------------
*/
define('PRIMARY_COLOR', '#1B5E20'); // Dark Green
define('SECONDARY_COLOR', '#1565C0'); // Dark Blue
define('BACKGROUND_COLOR', '#E3F2FD'); // Light Blue

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/
date_default_timezone_set('UTC');

/*
|--------------------------------------------------------------------------
| Error reporting (disable in production)
|--------------------------------------------------------------------------
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| Helper functions
|--------------------------------------------------------------------------
*/
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}