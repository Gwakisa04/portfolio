<?php
/**
 * Admin Navigation Bar - LocalBizHub
 */

$current_user = get_current_user();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-shield-alt me-2"></i>
            <?php echo SITE_NAME; ?> Admin
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
                    <a class="nav-link" href="companies.php">
                        <i class="fas fa-building me-1"></i>Companies
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="advertisements.php">
                        <i class="fas fa-ad me-1"></i>Advertisements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-1"></i>Reports
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger badge-counter">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="companies.php?filter=pending">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <?php echo $stats['pending_companies'] ?? 0; ?> companies pending approval
                        </a></li>
                        <li><a class="dropdown-item" href="companies.php?filter=expiring">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Subscriptions expiring soon
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">View All Notifications</a></li>
                    </ul>
                </li>
                
                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($current_user['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
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