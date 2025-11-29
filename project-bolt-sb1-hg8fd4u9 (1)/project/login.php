<?php
/**
 * Login Page - LocalBizHub
 * Handles both admin and company manager login
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_POST) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        $email_username = sanitize_input($_POST['email_username']);
        $password = $_POST['password'];
        $user_type = $_POST['user_type'] ?? 'company';
        
        if (empty($email_username) || empty($password)) {
            $error_message = 'Please fill in all fields.';
        } else {
            if ($user_type === 'admin') {
                if (login_admin($email_username, $password)) {
                    redirect('admin/dashboard.php');
                } else {
                    $error_message = 'Invalid username/email or password.';
                }
            } else {
                $result = login_company($email_username, $password);
                if ($result['success']) {
                    redirect('manager/dashboard.php');
                } else {
                    $error_message = $result['message'];
                }
            }
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'admin') {
            logout_admin();
        } else {
            logout_company();
        }
    }
    session_destroy();
    $success_message = 'You have been logged out successfully.';
}

// Handle subscription expired message
if (isset($_GET['error']) && $_GET['error'] === 'subscription_expired') {
    $error_message = 'Your subscription has expired. Please contact admin to renew.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-md-6 col-lg-4 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary fw-bold"><?php echo SITE_NAME; ?></h2>
                            <p class="text-muted">Employee & Business Management</p>
                        </div>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Login As</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="user_type" id="company" value="company" checked>
                                    <label class="btn btn-outline-primary" for="company">
                                        <i class="fas fa-building me-2"></i>Company Manager
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="user_type" id="admin" value="admin">
                                    <label class="btn btn-outline-primary" for="admin">
                                        <i class="fas fa-user-shield me-2"></i>Admin
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email_username" class="form-label">
                                    <span id="login-label">Email Address</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="text" class="form-control" id="email_username" name="email_username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <div class="company-only">
                                <p class="mb-2">Don't have an account?</p>
                                <a href="register.php" class="btn btn-outline-success">
                                    <i class="fas fa-user-plus me-2"></i>Register Your Company
                                </a>
                            </div>
                            <div class="mt-3">
                                <a href="public/index.php" class="text-decoration-none">
                                    <i class="fas fa-home me-2"></i>Back to Homepage
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle user type display
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const loginLabel = document.getElementById('login-label');
                const companyOnly = document.querySelector('.company-only');
                
                if (this.value === 'admin') {
                    loginLabel.textContent = 'Username or Email';
                    companyOnly.style.display = 'none';
                } else {
                    loginLabel.textContent = 'Email Address';
                    companyOnly.style.display = 'block';
                }
            });
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>