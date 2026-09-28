<?php
/**
 * Authentication Processing
 * Handles login, registration, and logout
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        redirect('/auth/login.php');
}

/**
 * Handle user login
 */
function handleLogin() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/auth/login.php');
    }

    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        setFlashMessage('Please fill in all fields', 'danger');
        redirect('/auth/login.php');
    }

    // Query user
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $result = executePreparedQuery($conn, $sql, 's', [$email]);

    if (!$result['success']) {
        setFlashMessage('An error occurred. Please try again.', 'danger');
        redirect('/auth/login.php');
    }

    $user = $result['result']->fetch_assoc();

    if (!$user) {
        setFlashMessage('Invalid email or password', 'danger');
        redirect('/auth/login.php');
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        setFlashMessage('Invalid email or password', 'danger');
        redirect('/auth/login.php');
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role
    if ($user['role'] === 'admin') {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/user/add_project.php');
    }
}

/**
 * Handle user registration
 */
function handleRegister() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/auth/register.php');
    }

    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        setFlashMessage('Please fill in all fields', 'danger');
        redirect('/auth/register.php');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('Invalid email format', 'danger');
        redirect('/auth/register.php');
    }

    // Validate password length
    if (strlen($password) < 6) {
        setFlashMessage('Password must be at least 6 characters', 'danger');
        redirect('/auth/register.php');
    }

    // Check password confirmation
    if ($password !== $confirm_password) {
        setFlashMessage('Passwords do not match', 'danger');
        redirect('/auth/register.php');
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $result = executePreparedQuery($conn, $sql, 's', [$email]);

    if ($result['result']->num_rows > 0) {
        setFlashMessage('Email already registered', 'danger');
        redirect('/auth/register.php');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
    $result = executePreparedQuery($conn, $sql, 'sss', [$name, $email, $hashedPassword]);

    if (!$result['success']) {
        setFlashMessage('Registration failed. Please try again.', 'danger');
        redirect('/auth/register.php');
    }

    setFlashMessage('Registration successful! Please login.', 'success');
    redirect('/auth/login.php');
}

/**
 * Handle user logout
 */
function handleLogout() {
    // Destroy all session data
    session_unset();
    session_destroy();

    setFlashMessage('You have been logged out successfully', 'success');
    redirect('/index.php');
}
?>
