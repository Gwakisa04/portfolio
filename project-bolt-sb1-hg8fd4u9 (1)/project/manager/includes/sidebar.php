<?php
/**
 * Company Manager Sidebar Navigation - LocalBizHub
 */

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
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
                <a class="nav-link <?php echo ($current_dir == 'employees') ? 'active' : ''; ?>" href="employees/index.php">
                    <i class="fas fa-users me-2"></i>
                    Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_dir == 'attendance') ? 'active' : ''; ?>" href="attendance/index.php">
                    <i class="fas fa-clock me-2"></i>
                    Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_dir == 'payroll') ? 'active' : ''; ?>" href="payroll/index.php">
                    <i class="fas fa-dollar-sign me-2"></i>
                    Payroll
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_dir == 'leaves') ? 'active' : ''; ?>" href="leaves/index.php">
                    <i class="fas fa-calendar-times me-2"></i>
                    Leave Management
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Business</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_dir == 'advertisements') ? 'active' : ''; ?>" href="advertisements/index.php">
                    <i class="fas fa-ad me-2"></i>
                    Advertisements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-building me-2"></i>
                    Company Profile
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Reports</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_dir == 'reports') ? 'active' : ''; ?>" href="reports/index.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
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