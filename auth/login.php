<?php
/**
 * Login Page
 * Predictify - ML-Based Project Prediction System
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/user/add_project.php');
    }
}

$pageTitle = 'Login';
include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="/assets/images/logo.png" alt="Predictify Logo" style="max-width: 120px;">
                        <h3 class="mt-3" style="color: <?php echo PRIMARY_COLOR; ?>;">Welcome Back</h3>
                        <p class="text-muted">Sign in to your account</p>
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

                    <form action="process_auth.php" method="POST" id="loginForm">
                        <input type="hidden" name="action" value="login">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Enter your email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Enter your password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    style="background-color: <?php echo PRIMARY_COLOR; ?>; border: none;">
                                Sign In
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Don't have an account?
                            <a href="register.php" style="color: <?php echo SECONDARY_COLOR; ?>;">
                                Create one now
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>
