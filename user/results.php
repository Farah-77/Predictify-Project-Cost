<?php
/**
 * Prediction Results
 * Displays prediction output with charts
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireUser = true;
require_once '../includes/auth_check.php';

// Check if prediction result exists in session
if (!isset($_SESSION['prediction_result']) || !isset($_SESSION['project_id'])) {
    setFlashMessage('No prediction data found', 'warning');
    redirect('/user/add_project.php');
}

$result = $_SESSION['prediction_result'];
$projectId = $_SESSION['project_id'];

// Get project details
$sql = "SELECT * FROM projects WHERE id = ? AND user_id = ?";
$queryResult = executePreparedQuery($conn, $sql, 'ii', [$projectId, $_SESSION['user_id']]);
$project = $queryResult['result']->fetch_assoc();

// Clear session data
unset($_SESSION['prediction_result']);
unset($_SESSION['project_id']);

$pageTitle = 'Prediction Results';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-graph-up-arrow"></i> Prediction Results
            </h2>
        </div>
    </div>

    <!-- Success Alert -->
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        <strong>Prediction Complete!</strong> Your project cost and duration have been estimated.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Project Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header ">
            <h5 class="mb-0" style="color: var(--primary-color);">
                <i class="bi bi-folder"></i> <?php echo htmlspecialchars($project['project_name']); ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-2"><small class="text-muted">Project Size</small></p>
                    <h6><?php echo number_format($project['project_size'], 0); ?> m²</h6>
                </div>
                <div class="col-md-3">
                    <p class="mb-2"><small class="text-muted">Team Members</small></p>
                    <h6><?php echo $project['team_members']; ?> people</h6>
                </div>
                <div class="col-md-3">
                    <p class="mb-2"><small class="text-muted">Equipment Count</small></p>
                    <h6><?php echo $project['equipment_count']; ?> units</h6>
                </div>
                <div class="col-md-3">
                    <p class="mb-2"><small class="text-muted">Material Cost</small></p>
                    <h6><?php echo formatCurrency($project['material_cost']); ?></h6>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><small class="text-muted">Complexity Level</small></p>
                    <h6>
                        <?php echo $project['complexity_level']; ?> -
                        <?php echo COMPLEXITY_LEVELS[$project['complexity_level']]; ?>
                    </h6>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><small class="text-muted">Prediction Date</small></p>
                    <h6><?php echo formatDateTime($project['created_at']); ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Prediction Results -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <i class="bi bi-currency-dollar" style="font-size: 3rem; color: var(--primary-color);"></i>
                    <h3 class="mt-3 mb-2" style="color: var(--primary-color);">
                        <?php echo formatCurrency($project['predicted_cost']); ?>
                    </h3>
                    <p class="text-muted mb-0">Predicted Total Cost</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <i class="bi bi-calendar-range" style="font-size: 3rem; color: var(--secondary-color);"></i>
                    <h3 class="mt-3 mb-2" style="color: var(--secondary-color);">
                        <?php echo $project['predicted_duration']; ?> Days
                    </h3>
                    <p class="text-muted mb-0">Predicted Duration</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body p-4">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: #28a745;"></i>
                    <h3 class="mt-3 mb-2" style="color: #28a745;">
                        <?php echo number_format($project['accuracy'] * 100, 1); ?>%
                    </h3>
                    <p class="text-muted mb-0">Prediction Confidence</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Model Info & Chart -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-shield-check"></i> Prediction Confidence
                    </h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-center p-4">
                        <i class="bi bi-graph-up-arrow" style="font-size: 3.5rem; color: var(--secondary-color); opacity: 0.3;"></i>
                        <h1 class="display-3 mt-3 mb-3" style="color: var(--primary-color); font-weight: bold;">
                            <?php echo number_format($project['accuracy'] * 100, 1); ?>%
                        </h1>
                        <p class="text-muted mb-2" style="font-size: 1.1rem;">Prediction Accuracy</p>
                        <p class="small text-muted">
                            Our AI model analyzed your project details and generated this prediction with
                            <strong><?php echo number_format($project['accuracy'] * 100, 1); ?>%</strong> confidence based on historical data.
                        </p>

                        <div class="progress mt-4" style="height: 25px;">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: <?php echo $project['accuracy'] * 100; ?>%; background-color: var(--secondary-color);"
                                 aria-valuenow="<?php echo $project['accuracy'] * 100; ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <strong><?php echo number_format($project['accuracy'] * 100, 1); ?>%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-bar-chart"></i> Cost Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="costChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <h5 class="mb-4">What's Next?</h5>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="add_project.php" class="btn btn-primary"
                   style="background-color: var(--primary-color); border: none;">
                    <i class="bi bi-plus-circle"></i> New Prediction
                </a>
                <a href="my_projects.php" class="btn btn-outline-secondary">
                    <i class="bi bi-folder2-open"></i> View All Projects
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i> Print Results
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Cost Breakdown Chart
<?php
    // Ensure numeric values for calculations
    $materialCost = floatval($project['material_cost']);
    $predictedCost = floatval($project['predicted_cost']);
    $remainingCost = $predictedCost - $materialCost;
?>
const costData = {
    labels: ['Material Cost', 'Labor Cost (Est.)', 'Equipment Cost (Est.)', 'Overhead (Est.)'],
    datasets: [{
        label: 'Cost Distribution',
        data: [
            <?php echo $materialCost; ?>,
            <?php echo $remainingCost * 0.5; ?>,
            <?php echo $remainingCost * 0.3; ?>,
            <?php echo $remainingCost * 0.2; ?>
        ],
        backgroundColor: [
            '<?php echo PRIMARY_COLOR; ?>',
            '<?php echo SECONDARY_COLOR; ?>',
            '#6c757d',
            '#20c997'
        ]
    }]
};

const costCtx = document.getElementById('costChart').getContext('2d');
new Chart(costCtx, {
    type: 'doughnut',
    data: costData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
