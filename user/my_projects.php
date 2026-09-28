<?php
/**
 * My Projects
 * View user's prediction history
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireUser = true;
require_once '../includes/auth_check.php';

// Get user's projects
$sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC";
$result = executePreparedQuery($conn, $sql, 'i', [$_SESSION['user_id']]);
$projects = $result['result'];

// Get statistics
$sql = "SELECT
        COUNT(*) as total_projects,
        AVG(predicted_cost) as avg_cost,
        AVG(predicted_duration) as avg_duration,
        AVG(accuracy) as avg_accuracy
        FROM projects WHERE user_id = ?";
$statsResult = executePreparedQuery($conn, $sql, 'i', [$_SESSION['user_id']]);
$stats = $statsResult['result']->fetch_assoc();

$pageTitle = 'My Projects';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-folder2-open"></i> My Projects
            </h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if ($stats['total_projects'] > 0): ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-folder" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <h4 class="mt-2" style="color: var(--primary-color);">
                            <?php echo $stats['total_projects']; ?>
                        </h4>
                        <p class="text-muted mb-0 small">Total Projects</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem; color: var(--secondary-color);"></i>
                        <h4 class="mt-2" style="color: var(--secondary-color);">
                            <?php echo formatCurrency($stats['avg_cost']); ?>
                        </h4>
                        <p class="text-muted mb-0 small">Average Cost</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-range" style="font-size: 2rem; color: #6c757d;"></i>
                        <h4 class="mt-2" style="color: #6c757d;">
                            <?php echo round($stats['avg_duration']); ?> Days
                        </h4>
                        <p class="text-muted mb-0 small">Average Duration</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up" style="font-size: 2rem; color: #28a745;"></i>
                        <h4 class="mt-2" style="color: #28a745;">
                            <?php echo number_format($stats['avg_accuracy'] * 100, 1); ?>%
                        </h4>
                        <p class="text-muted mb-0 small">Average Accuracy</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Projects List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header  d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: var(--primary-color);">
                All Predictions
            </h5>
            <a href="add_project.php" class="btn btn-sm btn-primary"
               style="background-color: var(--primary-color); border: none;">
                <i class="bi bi-plus-circle"></i> New Prediction
            </a>
        </div>
        <div class="card-body">
            <?php if ($projects->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Size (m²)</th>
                                <th>Team</th>
                                <th>Predicted Cost</th>
                                <th>Duration</th>
                                <th>Model</th>
                                <th>Accuracy</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($project = $projects->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                                    </td>
                                    <td><?php echo number_format($project['project_size'], 0); ?></td>
                                    <td><?php echo $project['team_members']; ?></td>
                                    <td><?php echo formatCurrency($project['predicted_cost']); ?></td>
                                    <td><?php echo $project['predicted_duration']; ?> days</td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($project['best_model']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo number_format($project['accuracy'] * 100, 1); ?>%
                                        </span>
                                    </td>
                                    <td><small><?php echo formatDate($project['created_at']); ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-folder-x" style="font-size: 4rem; color: var(--primary-color); opacity: 0.3;"></i>
                    <h5 class="mt-3 text-muted">No projects yet</h5>
                    <p class="text-muted">Start by creating your first project prediction</p>
                    <a href="add_project.php" class="btn btn-primary mt-3"
                       style="background-color: var(--primary-color); border: none;">
                        <i class="bi bi-plus-circle"></i> Create First Prediction
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
