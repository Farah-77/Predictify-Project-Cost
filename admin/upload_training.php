<?php
/**
 * Upload Training Data
 * Admin uploads CSV files for model training
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireAdmin = true;
require_once '../includes/auth_check.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['training_file'])) {
    $datasetName = sanitizeInput($_POST['dataset_name']);
    $file = $_FILES['training_file'];

    // Validate dataset name
    if (empty($datasetName)) {
        setFlashMessage('Please provide a dataset name', 'danger');
    }
    // Validate file upload
    elseif ($file['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('File upload error', 'danger');
    }
    // Validate file extension
    elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        setFlashMessage('Only CSV files are allowed', 'danger');
    }
    // Validate file size
    elseif ($file['size'] > MAX_FILE_SIZE) {
        setFlashMessage('File size exceeds maximum limit', 'danger');
    }
    else {
        // Create unique filename
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filepath = DATASET_PATH . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Count records
            $rowCount = 0;
            if (($handle = fopen($filepath, 'r')) !== false) {
                while (fgets($handle) !== false) {
                    $rowCount++;
                }
                fclose($handle);
                $rowCount--; // Subtract header row
            }

            // Insert into database
            $sql = "INSERT INTO training_data (dataset_name, file_path, uploaded_by, record_count)
                    VALUES (?, ?, ?, ?)";
            $result = executePreparedQuery($conn, $sql, 'ssii', [
                $datasetName,
                $filepath,
                $_SESSION['user_id'],
                $rowCount
            ]);

            if ($result['success']) {
                setFlashMessage("Dataset uploaded successfully! $rowCount records added.", 'success');
            } else {
                setFlashMessage('Failed to save dataset information', 'danger');
            }
        } else {
            setFlashMessage('Failed to upload file', 'danger');
        }
    }

    redirect('/admin/upload_training.php');
}

// Get existing datasets
$sql = "SELECT td.*, u.name as uploaded_by_name
        FROM training_data td
        JOIN users u ON td.uploaded_by = u.id
        ORDER BY td.upload_date DESC";
$datasets = $conn->query($sql);

$pageTitle = 'Upload Training Data';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-cloud-upload"></i> Upload Training Data
            </h2>
        </div>
    </div>

    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Upload Form -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        Upload New Dataset
                    </h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="dataset_name" class="form-label">Dataset Name</label>
                            <input type="text" class="form-control" id="dataset_name" name="dataset_name"
                                   placeholder="e.g., Training Data Q4 2024" required>
                        </div>

                        <div class="mb-3">
                            <label for="training_file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="training_file" name="training_file"
                                   accept=".csv" required>
                            <small class="text-muted">
                                Max size: <?php echo MAX_FILE_SIZE / (1024 * 1024); ?> MB
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary"
                                style="background-color: var(--primary-color); border: none;">
                            <i class="bi bi-upload"></i> Upload Dataset
                        </button>
                    </form>
                </div>
            </div>

            <!-- CSV Format Guide -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header ">
                    <h6 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-info-circle"></i> CSV Format Requirements
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Required Columns:</strong></p>
                    <ul class="small text-muted">
                        <li>Project Size</li>
                        <li>Team Members</li>
                        <li>Equipment Count</li>
                        <li>Material Cost</li>
                        <li>Complexity Level (1-5)</li>
                        <li>Duration (days)</li>
                        <li>Budget (target variable)</li>
                    </ul>
                    <p class="mb-0 small"><strong>Guidelines:</strong></p>
                    <ul class="small text-muted mb-0">
                        <li>Don't rename or remove columns</li>
                        <li>Don't leave empty cells</li>
                        <li>Use plain numbers only</li>
                        <li>First row should be headers</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Existing Datasets -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-database"></i> Existing Datasets
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($datasets->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($dataset = $datasets->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($dataset['dataset_name']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($dataset['uploaded_by_name']); ?>
                                            </p>
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-calendar"></i> <?php echo formatDateTime($dataset['upload_date']); ?>
                                            </p>
                                            <p class="mb-0 small">
                                                <span class="badge bg-info"><?php echo $dataset['record_count']; ?> records</span>
                                                <span class="badge bg-<?php echo $dataset['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($dataset['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No datasets uploaded yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
