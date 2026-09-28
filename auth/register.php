<?php
/**
 * Registration Page
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

$pageTitle = 'Register';
include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="<?php echo IMG_URL; ?>/logo.png" alt="Predictify Logo" style="max-width: 120px;">
                        <h3 class="mt-3" style="color: <?php echo PRIMARY_COLOR; ?>;">Create Account</h3>
                        <p class="text-muted">Start predicting your project costs</p>
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

                    <form action="process_auth.php" method="POST" id="registerForm">
                        <input type="hidden" name="action" value="register">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="Enter your full name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Enter your email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Create a password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" placeholder="Confirm your password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the Terms and Conditions
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    style="background-color: <?php echo PRIMARY_COLOR; ?>; border: none;">
                                Create Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Already have an account?
                            <a href="login.php" style="color: <?php echo SECONDARY_COLOR; ?>;">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
