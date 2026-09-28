<?php
/**
 * Retrain Models
 * Triggers Python script to retrain ML models
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
require_once 'python_call.php';
$requireAdmin = true;
require_once '../includes/auth_check.php';

// Handle training request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'train') {
    $datasetId = intval($_POST['dataset_id']);

    // Get dataset path
    $sql = "SELECT file_path, record_count FROM training_data WHERE id = ? AND status = 'active'";
    $result = executePreparedQuery($conn, $sql, 'i', [$datasetId]);

    if ($result['success'] && $result['result']->num_rows > 0) {
        $dataset = $result['result']->fetch_assoc();

        if ($dataset['record_count'] < MIN_TRAINING_RECORDS) {
            setFlashMessage('Dataset has too few records. Minimum: ' . MIN_TRAINING_RECORDS, 'danger');
        } else {
            // Call Python training script
            $datasetPath = $dataset['file_path'];

            if (strpos($datasetPath, '/models/') !== false) {
                $datasetPath = substr($datasetPath, strpos($datasetPath, '/models/') + 1);
            }

            $trainingResult = trainModels($datasetPath);


            if ($trainingResult['success']) {
                // Save model performance
                $models = $trainingResult['models'];
                foreach ($models as $modelName => $metrics) {
                    $sql = "INSERT INTO model_performance (model_name, r2_score, mae, rmse, dataset_id)
                            VALUES (?, ?, ?, ?, ?)";
                    executePreparedQuery($conn, $sql, 'sdddi', [
                        $modelName,
                        $metrics['r2_score'],
                        $metrics['mae'] ?? null,
                        $metrics['rmse'] ?? null,
                        $datasetId
                    ]);
                }

                setFlashMessage('Models trained successfully! Best model: ' . $trainingResult['best_model'], 'success');
            } else {
                // Show detailed error including raw Python output if available
                $errorMessage = 'Training failed: ' . $trainingResult['error'];
                if (!empty($trainingResult['raw_output'])) {
                    $errorMessage .= '<br><br><strong>Python Output:</strong><br><pre style="background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;">' . htmlspecialchars($trainingResult['raw_output']) . '</pre>';
                }
                setFlashMessage($errorMessage, 'danger');
            }
        }
    } else {
        setFlashMessage('Invalid dataset selected', 'danger');
    }

    redirect('/admin/retrain_models.php');
}

// Get available datasets
$sql = "SELECT * FROM training_data WHERE status = 'active' ORDER BY upload_date DESC";
$datasets = $conn->query($sql);

// Get recent training history
$sql = "SELECT mp.*, td.dataset_name
        FROM model_performance mp
        LEFT JOIN training_data td ON mp.dataset_id = td.id
        ORDER BY mp.training_date DESC
        LIMIT 10";
$trainingHistory = $conn->query($sql);

$pageTitle = 'Retrain Models';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-arrow-repeat"></i> Retrain Models
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
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        Start Training
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Training may take several minutes depending on dataset size.
                        Please be patient.
                    </div>

                    <form action="" method="POST" id="trainForm">
                        <input type="hidden" name="action" value="train">

                        <div class="mb-3">
                            <label for="dataset_id" class="form-label">Select Training Dataset</label>
                            <select class="form-select" id="dataset_id" name="dataset_id" required>
                                <option value="">-- Select Dataset --</option>
                                <?php if ($datasets->num_rows > 0): ?>
                                    <?php while ($dataset = $datasets->fetch_assoc()): ?>
                                        <option value="<?php echo $dataset['id']; ?>">
                                            <?php echo htmlspecialchars($dataset['dataset_name']); ?>
                                            (<?php echo $dataset['record_count']; ?> records)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg"
                                style="background-color: var(--primary-color); border: none;"
                                id="trainBtn">
                            <i class="bi bi-play-circle"></i> Start Training
                        </button>
                    </form>

                    <hr class="my-4">

                    <h6 style="color: var(--secondary-color);">Models to be trained:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="bi bi-graph-up"></i> Linear Regression
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-tree"></i> Random Forest Regressor
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-lightning"></i> Gradient Boosting Regressor
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-clock-history"></i> Training History
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($trainingHistory->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th>R² Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($history = $trainingHistory->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($history['model_name']); ?><br>
                                                    <span class="text-muted">
                                                        <?php echo htmlspecialchars($history['dataset_name'] ?? 'N/A'); ?>
                                                    </span>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo number_format($history['r2_score'] * 100, 2); ?>%
                                                </span>
                                            </td>
                                            <td><small><?php echo formatDateTime($history['training_date']); ?></small></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No training history yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('trainForm').addEventListener('submit', function() {
    const btn = document.getElementById('trainBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Training in progress...';
});
</script>

<?php include '../includes/footer.php'; ?>
