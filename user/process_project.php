<?php
/**
 * Process Project Prediction
 * Handles form submission and calls Python prediction script
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
require_once '../admin/python_call.php';
$requireUser = true;
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/user/add_project.php');
}

$action = $_POST['action'] ?? '';

if ($action === 'manual') {
    processManualInput();
} elseif ($action === 'csv') {
    processCsvUpload();
} else {
    setFlashMessage('Invalid action', 'danger');
    redirect('/user/add_project.php');
}

/**
 * Process manual form input
 */
function processManualInput() {
    global $conn;

    // Get form data
    $projectName = sanitizeInput($_POST['project_name']);
    $projectSize = floatval($_POST['project_size']);
    $teamMembers = intval($_POST['team_members']);
    $equipmentCount = intval($_POST['equipment_count']);
    $materialCost = floatval($_POST['material_cost']);
    $complexityLevel = intval($_POST['complexity_level']);

    // Validate inputs
    if (empty($projectName) || $projectSize <= 0 || $teamMembers <= 0 || $materialCost < 0 || $complexityLevel < 1 || $complexityLevel > 5) {
        setFlashMessage('Please fill all fields correctly', 'danger');
        redirect('/user/add_project.php');
    }

    // Prepare data for prediction
    $projectData = [
        'project_size' => $projectSize,
        'team_members' => $teamMembers,
        'equipment_count' => $equipmentCount,
        'material_cost' => $materialCost,
        'complexity_level' => $complexityLevel
    ];

    // Call Python prediction script
    $predictionResult = makePrediction($projectData);

    if (!$predictionResult['success']) {
        setFlashMessage('Prediction failed: ' . ($predictionResult['error'] ?? 'Unknown error'), 'danger');
        redirect('/user/add_project.php');
    }

    // Extract plot filename if available (from plot_image field)
    $plotImage = $predictionResult['plot_image'] ?? null;

    // Save to database
    $sql = "INSERT INTO projects (user_id, project_name, project_size, team_members, equipment_count,
            material_cost, complexity_level, predicted_cost, predicted_duration, best_model, accuracy, plot_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $result = executePreparedQuery($conn, $sql, 'isdiiddiisss', [
        $_SESSION['user_id'],
        $projectName,
        $projectSize,
        $teamMembers,
        $equipmentCount,
        $materialCost,
        $complexityLevel,
        $predictionResult['predicted_cost'],
        $predictionResult['predicted_duration'],
        $predictionResult['best_model'],
        $predictionResult['accuracy'],
        $plotImage
    ]);

    if ($result['success']) {
        $_SESSION['prediction_result'] = $predictionResult;
        $_SESSION['project_id'] = $result['insert_id'];
        redirect('/user/results.php');
    } else {
        die("Database error: " . $result['error']);
    }
}

/**
 * Process CSV file upload
 */
function processCsvUpload() {
    setFlashMessage('CSV batch processing is currently under development', 'info');
    redirect('/user/add_project.php');
}
?>
