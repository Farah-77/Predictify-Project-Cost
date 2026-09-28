<?php
/**
 * Admin Dashboard
 * Statistics and system overview
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireAdmin = true;
require_once '../includes/auth_check.php';

// Get statistics
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$totalUsers = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM projects";
$totalProjects = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM training_data WHERE status = 'active'";
$totalDatasets = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT AVG(accuracy) as avg_accuracy FROM projects WHERE accuracy IS NOT NULL";
$avgAccuracy = $conn->query($sql)->fetch_assoc()['avg_accuracy'] ?? 0;

// Get recent projects
$sql = "SELECT p.*, u.name as user_name, u.email as user_email
        FROM projects p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 10";
$recentProjects = $conn->query($sql);

// Get model usage from actual projects (which models are selected as best)
$sql = "SELECT best_model, COUNT(*) as count, AVG(accuracy) as avg_accuracy
        FROM projects
        WHERE best_model IS NOT NULL
        GROUP BY best_model
        ORDER BY count DESC";
$modelUsage = $conn->query($sql);

// Get project complexity distribution
$sql = "SELECT complexity_level, COUNT(*) as count
        FROM projects
        GROUP BY complexity_level
        ORDER BY complexity_level ASC";
$complexityData = $conn->query($sql);

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
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

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Users</p>
                            <h3 class="mb-0" style="color: var(--primary-color);"><?php echo $totalUsers; ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-people" style="font-size: 3rem; color: var(--primary-color); opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Projects</p>
                            <h3 class="mb-0" style="color: var(--primary-color);"><?php echo $totalProjects; ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-folder" style="font-size: 3rem; color: var(--primary-color); opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Training Datasets</p>
                            <h3 class="mb-0" style="color: var(--primary-color);"><?php echo $totalDatasets; ?></h3>
                        </div>
                        <div>
                            <i class="bi bi-database" style="font-size: 3rem; color: var(--primary-color); opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Avg Accuracy</p>
                            <h3 class="mb-0" style="color: var(--primary-color);">
                                <?php echo number_format($avgAccuracy * 100, 1); ?>%
                            </h3>
                        </div>
                        <div>
                            <i class="bi bi-graph-up" style="font-size: 3rem; color: var(--primary-color); opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-graph-up"></i> Model Usage & Accuracy
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($modelUsage->num_rows > 0): ?>
                        <canvas id="modelChart"></canvas>
                    <?php else: ?>
                        <p class="text-center text-muted py-5">No project data available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-pie-chart"></i> Projects by Complexity Level
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($complexityData->num_rows > 0): ?>
                        <canvas id="complexityChart"></canvas>
                    <?php else: ?>
                        <p class="text-center text-muted py-5">No project data available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-clock-history"></i> Recent Projects
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>User</th>
                                    <th>Predicted Cost</th>
                                    <th>Predicted Duration</th>
                                    <th>Best Model</th>
                                    <th>Accuracy</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentProjects->num_rows > 0): ?>
                                    <?php while ($project = $recentProjects->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo htmlspecialchars($project['user_name']); ?><br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($project['user_email']); ?></span>
                                                </small>
                                            </td>
                                            <td><?php echo formatCurrency($project['predicted_cost']); ?></td>
                                            <td><?php echo $project['predicted_duration']; ?> days</td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $project['best_model']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo number_format($project['accuracy'] * 100, 1); ?>%
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($project['created_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No projects yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($modelUsage->num_rows > 0): ?>
// Model Usage & Accuracy Chart
const modelData = <?php
    $models = [];
    $counts = [];
    $accuracies = [];
    $modelUsage->data_seek(0);
    while ($row = $modelUsage->fetch_assoc()) {
        $models[] = $row['best_model'];
        $counts[] = $row['count'];
        $accuracies[] = round($row['avg_accuracy'] * 100, 1);
    }
    echo json_encode(['labels' => $models, 'counts' => $counts, 'accuracies' => $accuracies]);
?>;

const modelCtx = document.getElementById('modelChart').getContext('2d');
new Chart(modelCtx, {
    type: 'bar',
    data: {
        labels: modelData.labels,
        datasets: [
            {
                label: 'Times Selected',
                data: modelData.counts,
                backgroundColor: '<?php echo PRIMARY_COLOR; ?>',
                yAxisID: 'y'
            },
            {
                label: 'Avg Accuracy (%)',
                data: modelData.accuracies,
                backgroundColor: '<?php echo SECONDARY_COLOR; ?>',
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Times Selected'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Accuracy (%)'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
<?php endif; ?>

<?php if ($complexityData->num_rows > 0): ?>
// Project Complexity Distribution Pie Chart
const complexityChartData = <?php
    $complexityLabels = [];
    $complexityCounts = [];
    $complexityData->data_seek(0);
    $complexityMap = COMPLEXITY_LEVELS;
    while ($row = $complexityData->fetch_assoc()) {
        $level = $row['complexity_level'];
        $complexityLabels[] = $complexityMap[$level];
        $complexityCounts[] = $row['count'];
    }
    echo json_encode(['labels' => $complexityLabels, 'data' => $complexityCounts]);
?>;

const complexityCtx = document.getElementById('complexityChart').getContext('2d');
new Chart(complexityCtx, {
    type: 'pie',
    data: {
        labels: complexityChartData.labels,
        datasets: [{
            data: complexityChartData.data,
            backgroundColor: [
                '<?php echo PRIMARY_COLOR; ?>',
                '<?php echo SECONDARY_COLOR; ?>',
                '#2E7D32',
                '#0D47A1',
                '#43A047'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' projects (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
