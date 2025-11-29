<?php
/**
 * Company Manager Navigation Bar - LocalBizHub
 */

$current_user = get_current_user();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-building me-2"></i>
            <?php echo SITE_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="employees/index.php">
                        <i class="fas fa-users me-1"></i>Employees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="attendance/index.php">
                        <i class="fas fa-clock me-1"></i>Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="payroll/index.php">
                        <i class="fas fa-dollar-sign me-1"></i>Payroll
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaves/index.php">
                        <i class="fas fa-calendar-times me-1"></i>Leaves
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <!-- Company Name -->
                <li class="nav-item">
                    <span class="navbar-text me-3">
                        <i class="fas fa-building me-1"></i>
                        <?php echo htmlspecialchars($current_user['name']); ?>
                    </span>
                </li>
                
                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($current_user['manager']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-2"></i>Company Profile
                        </a></li>
                        <li><a class="dropdown-item" href="advertisements/index.php">
                            <i class="fas fa-ad me-2"></i>Advertisements
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../login.php?logout=1">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>