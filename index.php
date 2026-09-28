<?php
/**
 * Landing Page
 * Predictify - ML-Based Project Prediction System
 */

require_once 'config/settings.php';
require_once 'config/db_config.php';

// Redirect if logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/user/add_project.php');
    }
}

$pageTitle = 'Home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo APP_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo APP_DESCRIPTION; ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/style.css">

    <style>
        :root {
            --primary-color: <?php echo PRIMARY_COLOR; ?>;
            --secondary-color: <?php echo SECONDARY_COLOR; ?>;
            --background-color: <?php echo BACKGROUND_COLOR; ?>;
            --card-background: #FFFFFF;
        }

        body {
            background: linear-gradient(135deg, var(--background-color) 0%, var(--card-background) 100%);
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .feature-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(27, 94, 32, 0.2);
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-3 mb-4">
                    <img src="<?php echo IMG_URL; ?>/logo.png" alt="Aramco Logo"
                         class="img-fluid" style="max-width: 200px;">
                    <img src="<?php echo IMG_URL; ?>/Hail-Uni-Logo.png" alt="Hail University Logo"
                         class="img-fluid" style="max-width: 200px;">
                </div>

                <h1 class="display-3 fw-bold mb-4" style="color: var(--primary-color);">
                    <?php echo APP_NAME; ?>
                </h1>

                <p class="lead mb-4" style="color: var(--secondary-color); font-size: 1.3rem;">
                    Predict Your Project Costs and Duration with Machine Learning
                </p>

                <p class="text-muted mb-5">
                    Leverage the power of AI to accurately estimate project budgets and timelines.
                    Make informed decisions with data-driven predictions.
                </p>

                <?php
                $flash = getFlashMessage();
                if ($flash):
                ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3 d-sm-flex justify-content-sm-start">
                    <a href="/auth/register.php"
                       class="btn btn-primary-custom btn-lg">
                        <i class="bi bi-rocket-takeoff me-2"></i>Get Started
                    </a>
                    <a href="/auth/login.php"
                       class="btn btn-outline-secondary btn-lg"
                       style="border-color: var(--secondary-color); color: var(--secondary-color); border-radius: 50px;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="text-center">
                    <i class="bi bi-graph-up-arrow"
                       style="font-size: 15rem; color: var(--primary-color); opacity: 0.15;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5" style="background-color: var(--card-background);">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--primary-color);">
            Why Choose Predictify?
        </h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-cpu" style="font-size: 3rem; color: var(--primary-color);"></i>
                        </div>
                        <h4 style="color: var(--secondary-color);">Machine Learning</h4>
                        <p class="text-muted">
                            Uses advanced ML algorithms including Random Forest and Gradient Boosting
                            for accurate predictions.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-speedometer2" style="font-size: 3rem; color: var(--primary-color);"></i>
                        </div>
                        <h4 style="color: var(--secondary-color);">Fast & Accurate</h4>
                        <p class="text-muted">
                            Get instant predictions with high accuracy. Our models are trained
                            on extensive project data.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-bar-chart-line" style="font-size: 3rem; color: var(--primary-color);"></i>
                        </div>
                        <h4 style="color: var(--secondary-color);">Visual Insights</h4>
                        <p class="text-muted">
                            View predictions with interactive charts and detailed analytics
                            for better decision-making.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--primary-color);">
            How It Works
        </h2>

        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; background-color: var(--primary-color);">
                        <span class="text-white fw-bold" style="font-size: 2rem;">1</span>
                    </div>
                </div>
                <h5 style="color: var(--secondary-color);">Sign Up</h5>
                <p class="text-muted">Create your free account</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; background-color: var(--primary-color);">
                        <span class="text-white fw-bold" style="font-size: 2rem;">2</span>
                    </div>
                </div>
                <h5 style="color: var(--secondary-color);">Enter Details</h5>
                <p class="text-muted">Input your project information</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; background-color: var(--primary-color);">
                        <span class="text-white fw-bold" style="font-size: 2rem;">3</span>
                    </div>
                </div>
                <h5 style="color: var(--secondary-color);">Get Prediction</h5>
                <p class="text-muted">Receive instant cost & duration estimates</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; background-color: var(--primary-color);">
                        <span class="text-white fw-bold" style="font-size: 2rem;">4</span>
                    </div>
                </div>
                <h5 style="color: var(--secondary-color);">Make Decisions</h5>
                <p class="text-muted">Plan with confidence</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-4 border-top">
    <div class="container text-center">
        <p class="text-muted mb-0">
            &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
        </p>
        <p class="text-muted small">
            Version <?php echo APP_VERSION; ?>
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
