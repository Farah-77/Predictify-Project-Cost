<nav class="navbar navbar-expand-lg navbar-light shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
            <img src="/assets/images/logo.png" alt="<?php echo APP_NAME; ?>" height="50" class="me-2">
            <span style="color: var(--primary-color); font-weight: bold; font-size: 1.3rem;">
                <?php echo APP_NAME; ?>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isAdmin()): ?>
                <!-- Admin Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/upload_training.php">
                            <i class="bi bi-cloud-upload"></i> Upload Training Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/retrain_models.php">
                            <i class="bi bi-arrow-repeat"></i> Retrain Models
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/manage_users.php">
                            <i class="bi bi-people"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/manage_projects.php">
                            <i class="bi bi-folder"></i> Manage Projects
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <!-- User Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/user/add_project.php">
                            <i class="bi bi-plus-circle"></i> New Prediction
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/my_projects.php">
                            <i class="bi bi-folder2-open"></i> My Projects
                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <!-- User Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1" style="font-size: 1.5rem;"></i>
                        <span><?php echo $_SESSION['user_name']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <span class="dropdown-item-text">
                                <small class="text-muted"><?php echo $_SESSION['user_email']; ?></small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <span class="dropdown-item-text">
                                <small>
                                    <i class="bi bi-shield-check"></i>
                                    Role: <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
                                </small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
