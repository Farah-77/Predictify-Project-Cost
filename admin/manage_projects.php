<?php
/**
 * Manage Projects
 * View and manage all user projects
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireAdmin = true;
require_once '../includes/auth_check.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $projectId = intval($_GET['id']);

    $sql = "DELETE FROM projects WHERE id = ?";
    $result = executePreparedQuery($conn, $sql, 'i', [$projectId]);

    if ($result['success'] && $result['affected_rows'] > 0) {
        setFlashMessage('Project deleted successfully', 'success');
    } else {
        setFlashMessage('Failed to delete project', 'danger');
    }

    redirect('/admin/manage_projects.php');
}

// Get all projects with user info
$sql = "SELECT p.*, u.name as user_name, u.email as user_email
        FROM projects p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC";
$projects = $conn->query($sql);

$pageTitle = 'Manage Projects';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-folder"></i> Manage Projects
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

    <div class="card border-0 shadow-sm">
        <div class="card-header  d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: var(--primary-color);">
                All Projects
            </h5>
            <span class="badge bg-primary"><?php echo $projects->num_rows; ?> total projects</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>User</th>
                            <th>Size (m²)</th>
                            <th>Team</th>
                            <th>Predicted Cost</th>
                            <th>Duration</th>
                            <th>Model</th>
                            <th>Accuracy</th>
                            <th>ML Plot</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($projects->num_rows > 0): ?>
                            <?php while ($project = $projects->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $project['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($project['user_name']); ?><br>
                                            <span class="text-muted"><?php echo htmlspecialchars($project['user_email']); ?></span>
                                        </small>
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
                                    <td class="text-center">
                                        <?php if (!empty($project['plot_image'])): ?>
                                            <a href="../assets/uploads/plots/<?php echo htmlspecialchars($project['plot_image']); ?>" target="_blank" title="View Full Size">
                                                <img src="../assets/uploads/plots/<?php echo htmlspecialchars($project['plot_image']); ?>"
                                                     alt="ML Plot"
                                                     style="max-width: 120px; height: auto; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.25); cursor: pointer; transition: transform 0.2s;"
                                                     onmouseover="this.style.transform='scale(1.05)'"
                                                     onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="bi bi-image"></i> No plot</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?php echo formatDate($project['created_at']); ?></small></td>
                                    <td>
                                        <a href="?action=delete&id=<?php echo $project['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this project?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted">No projects found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
