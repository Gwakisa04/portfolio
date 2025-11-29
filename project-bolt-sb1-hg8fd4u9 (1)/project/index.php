<?php
/**
 * Main Entry Point - LocalBizHub
 * Redirects to appropriate section based on user status
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is already logged in and redirect accordingly
if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
} elseif (is_company_logged_in()) {
    redirect('manager/dashboard.php');
} else {
    // Redirect to public homepage
    redirect('public/index.php');
}
?>