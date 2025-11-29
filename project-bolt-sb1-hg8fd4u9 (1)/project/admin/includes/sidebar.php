<?php
/**
 * Admin Sidebar Navigation - LocalBizHub
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'companies.php') ? 'active' : ''; ?>" href="companies.php">
                    <i class="fas fa-building me-2"></i>
                    Companies
                    <?php if (isset($stats['pending_companies']) && $stats['pending_companies'] > 0): ?>
                        <span class="badge bg-warning rounded-pill ms-auto"><?php echo $stats['pending_companies']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'employees.php') ? 'active' : ''; ?>" href="employees.php">
                    <i class="fas fa-users me-2"></i>
                    All Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'advertisements.php') ? 'active' : ''; ?>" href="advertisements.php">
                    <i class="fas fa-ad me-2"></i>
                    Advertisements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Management</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'subscriptions.php') ? 'active' : ''; ?>" href="subscriptions.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Subscriptions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>" href="messages.php">
                    <i class="fas fa-envelope me-2"></i>
                    Messages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'admins.php') ? 'active' : ''; ?>" href="admins.php">
                    <i class="fas fa-user-shield me-2"></i>
                    Admin Users
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>System</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../public/index.php" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>
                    View Website
                </a>
            </li>
        </ul>
    </div>
</nav>