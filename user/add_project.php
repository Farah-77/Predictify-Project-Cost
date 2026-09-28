<?php
/**
 * Add New Project
 * User input form for project prediction
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireUser = true;
require_once '../includes/auth_check.php';

$pageTitle = 'New Prediction';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-plus-circle"></i> New Project Prediction
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
        <!-- Manual Entry Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-pencil-square"></i> Enter Project Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="process_project.php" method="POST" id="projectForm">
                        <input type="hidden" name="action" value="manual">

                        <div class="mb-3">
                            <label for="project_name" class="form-label">
                                Project Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="project_name" name="project_name"
                                   placeholder="e.g., Office Building Construction" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_size" class="form-label">
                                    Project Size (m²) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" class="form-control" id="project_size"
                                       name="project_size" placeholder="e.g., 1500" required min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="team_members" class="form-label">
                                    Team Members <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="team_members"
                                       name="team_members" placeholder="e.g., 10" required min="1">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="equipment_count" class="form-label">
                                    Equipment Count <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="equipment_count"
                                       name="equipment_count" placeholder="e.g., 5" required min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="material_cost" class="form-label">
                                    Material Cost ($) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" class="form-control" id="material_cost"
                                       name="material_cost" placeholder="e.g., 50000" required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="complexity_level" class="form-label">
                                Complexity Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="complexity_level" name="complexity_level" required>
                                <option value="">-- Select Complexity --</option>
                                <?php foreach (COMPLEXITY_LEVELS as $level => $label): ?>
                                    <option value="<?php echo $level; ?>"><?php echo $level; ?> - <?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    style="background-color: var(--primary-color); border: none;">
                                <i class="bi bi-calculator"></i> Get Prediction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CSV Upload & Help -->
        <div class="col-lg-4">
            <!-- CSV Upload Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header ">
                    <h6 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-file-earmark-arrow-up"></i> Upload CSV
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Upload a CSV file with multiple projects for batch predictions.
                    </p>

                    <form action="process_project.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="csv">

                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="project_file"
                                   accept=".csv" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                    style="border-color: var(--primary-color); color: var(--primary-color);">
                                <i class="bi bi-upload"></i> Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header ">
                    <h6 class="mb-0" style="color: var(--primary-color);">
                        <i class="bi bi-info-circle"></i> Field Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li><strong>Project Size:</strong> Total area in square meters</li>
                        <li><strong>Team Members:</strong> Number of workers</li>
                        <li><strong>Equipment:</strong> Count of major equipment</li>
                        <li><strong>Material Cost:</strong> Estimated material budget</li>
                        <li><strong>Complexity:</strong> Overall project difficulty (1-5)</li>
                    </ul>

                    <hr>

                    <p class="small mb-0">
                        <i class="bi bi-lightbulb"></i>
                        <strong>Tip:</strong> More accurate inputs lead to better predictions.
                    </p>
                </div>
            </div>

            <!-- CSV Format Guide -->
            <div class="card border-0 shadow-sm" style="border: 2px solid var(--secondary-color) !important;">
                <div class="card-header" style="background-color: var(--secondary-color); color: var(--card-background);">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> CSV File Format
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2"><strong>Required Column Names (Exact):</strong></p>
                    <ul class="small ps-3 mb-3" style="font-family: monospace; color: var(--text-dark);">
                        <li>Project Size</li>
                        <li>Team Members</li>
                        <li>Equipment Count</li>
                        <li>Material Cost</li>
                        <li>Complexity Level</li>
                    </ul>

                    <div class="alert alert-warning py-2 px-3 mb-3" style="font-size: 0.85rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Important:</strong> Column names must match exactly (case-sensitive)!
                    </div>

                    <p class="small mb-2"><strong>Example CSV Format:</strong></p>
                    <div class="bg-light p-2 rounded" style="font-family: monospace; font-size: 0.75rem; overflow-x: auto; background-color: var(--accent-light) !important;">
                        <div>Project Size,Team Members,Equipment Count,Material Cost,Complexity Level</div>
                        <div>1500.50,10,5,50000.00,3</div>
                        <div>2200.00,15,8,75000.00,4</div>
                    </div>

                    <hr>

                    <p class="small mb-2"><strong>Rules:</strong></p>
                    <ul class="small text-muted ps-3 mb-3">
                        <li>First row must be headers</li>
                        <li>Use numbers only (no currency symbols)</li>
                        <li>No empty cells allowed</li>
                        <li>Complexity Level: 1-5 only</li>
                        <li>Save as .csv file format</li>
                    </ul>

                    <div class="d-grid">
                        <a href="/assets/sample_project_template.csv" download class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Download Sample CSV Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
