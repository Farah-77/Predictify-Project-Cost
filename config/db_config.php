<?php
/**
 * Database Configuration
 * Predictify - ML-Based Project Prediction System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'predictify_db');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Global connection instance
$conn = getDBConnection();

// Function to close connection
function closeDBConnection($connection) {
    if ($connection) {
        $connection->close();
    }
}

// Function to execute prepared statement safely
function executePreparedQuery($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ['success' => false, 'error' => $conn->error];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $result = $stmt->execute();

    if (!$result) {
        return ['success' => false, 'error' => $stmt->error];
    }

    $output = ['success' => true];

    // For SELECT queries, fetch results
    if (stripos($sql, 'SELECT') === 0) {
        $output['result'] = $stmt->get_result();
    } else {
        $output['affected_rows'] = $stmt->affected_rows;
        $output['insert_id'] = $conn->insert_id;
    }

    $stmt->close();
    return $output;
}
?>
