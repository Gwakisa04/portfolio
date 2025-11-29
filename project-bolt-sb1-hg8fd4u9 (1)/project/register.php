<?php
/**
 * Company Registration Page - LocalBizHub
 * Allows new companies to register (requires admin approval)
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

$error_message = '';
$success_message = '';

// Handle registration form submission
if ($_POST) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $data = [
            'company_name' => sanitize_input($_POST['company_name']),
            'manager_name' => sanitize_input($_POST['manager_name']),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'phone' => sanitize_input($_POST['phone']),
            'industry' => sanitize_input($_POST['industry']),
            'location' => sanitize_input($_POST['location']),
            'description' => sanitize_input($_POST['description'])
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['company_name'])) $errors[] = 'Company name is required';
        if (empty($data['manager_name'])) $errors[] = 'Manager name is required';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        if (strlen($data['password']) < 6) $errors[] = 'Password must be at least 6 characters';
        if ($data['password'] !== $data['confirm_password']) $errors[] = 'Passwords do not match';
        if (empty($data['phone'])) $errors[] = 'Phone number is required';
        if (empty($data['industry'])) $errors[] = 'Industry is required';
        if (empty($data['location'])) $errors[] = 'Location is required';
        
        if (empty($errors)) {
            $result = register_company($data);
            if ($result['success']) {
                $success_message = $result['message'];
                // Clear form data
                $data = array_fill_keys(array_keys($data), '');
            } else {
                $error_message = $result['message'];
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
}

// Get industries for dropdown
$industries = [
    'Technology', 'Healthcare', 'Finance', 'Education', 'Retail', 'Manufacturing',
    'Construction', 'Agriculture', 'Food & Beverage', 'Transportation', 'Real Estate',
    'Consulting', 'Marketing', 'Legal', 'Hospitality', 'Entertainment', 'Other'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Company - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary fw-bold"><?php echo SITE_NAME; ?></h2>
                            <p class="text-muted">Register Your Company</p>
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

                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name *</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="<?php echo htmlspecialchars($data['company_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="manager_name" class="form-label">Manager Name *</label>
                                    <input type="text" class="form-control" id="manager_name" name="manager_name" 
                                           value="<?php echo htmlspecialchars($data['manager_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <div class="form-text">Minimum 6 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="industry" class="form-label">Industry *</label>
                                    <select class="form-select" id="industry" name="industry" required>
                                        <option value="">Select Industry</option>
                                        <?php foreach ($industries as $industry): ?>
                                            <option value="<?php echo $industry; ?>" 
                                                <?php echo (isset($data['industry']) && $data['industry'] === $industry) ? 'selected' : ''; ?>>
                                                <?php echo $industry; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">Location *</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($data['location'] ?? ''); ?>" 
                                           placeholder="City, State/Province" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Company Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Brief description of your company..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label">Company Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <div class="form-text">Optional: JPG, PNG, or GIF (max 5MB)</div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> *
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register Company
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-2">Already have an account?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Here
                            </a>
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

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By registering your company with LocalBizHub, you agree to the following terms:</p>
                    <ul>
                        <li>Provide accurate and truthful information about your company</li>
                        <li>Maintain active subscription to continue using the platform</li>
                        <li>Use the platform responsibly and in accordance with applicable laws</li>
                        <li>Respect the privacy and rights of employees and other users</li>
                        <li>Keep your account credentials secure and confidential</li>
                    </ul>
                    <p>LocalBizHub reserves the right to suspend or terminate accounts that violate these terms.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password && confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>