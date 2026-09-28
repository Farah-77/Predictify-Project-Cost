</main>

<!-- Footer -->
<footer class="mt-5 border-top">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5 style="color: var(--primary-color);"><?php echo APP_NAME; ?></h5>
                <p class="text-muted"><?php echo APP_DESCRIPTION; ?></p>
            </div>
            <div class="col-md-3">
                <h6 style="color: var(--primary-color);">Quick Links</h6>
                <ul class="list-unstyled">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="/admin/dashboard.php" class="text-muted">Dashboard</a></li>
                            <li><a href="/admin/upload_training.php" class="text-muted">Upload Data</a></li>
                        <?php else: ?>
                            <li><a href="/user/add_project.php" class="text-muted">New Prediction</a></li>
                            <li><a href="/user/my_projects.php" class="text-muted">My Projects</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="/index.php" class="text-muted">Home</a></li>
                        <li><a href="/auth/login.php" class="text-muted">Login</a></li>
                        <li><a href="/auth/register.php" class="text-muted">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 style="color: var(--primary-color);">About</h6>
                <p class="text-muted small">
                    ML-powered project cost and duration prediction system.
                </p>
                <p class="text-muted small">
                    Version <?php echo APP_VERSION; ?>
                </p>
            </div>
        </div>
        <hr>
        <div class="text-center text-muted small">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="/assets/js/main.js"></script>

</body>
</html>
