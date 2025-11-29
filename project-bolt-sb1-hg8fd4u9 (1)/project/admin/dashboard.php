<?php
/**
 * Admin Dashboard - LocalBizHub
 * Main dashboard for system administrators
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require admin authentication
require_admin_login();

$db = get_db();

// Get dashboard statistics
$stats = [];

// Total companies
$stmt = $db->query("SELECT COUNT(*) as total FROM companies");
$stats['total_companies'] = $stmt->fetch()['total'];

// Active companies
$stmt = $db->query("SELECT COUNT(*) as total FROM companies WHERE is_active = 1 AND is_approved = 1");
$stats['active_companies'] = $stmt->fetch()['total'];

// Pending approvals
$stmt = $db->query("SELECT COUNT(*) as total FROM companies WHERE is_approved = 0");
$stats['pending_companies'] = $stmt->fetch()['total'];

// Total employees
$stmt = $db->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stats['total_employees'] = $stmt->fetch()['total'];

// Featured companies
$stmt = $db->query("SELECT COUNT(*) as total FROM companies WHERE is_featured = 1 AND is_active = 1");
$stats['featured_companies'] = $stmt->fetch()['total'];

// Active advertisements
$stmt = $db->query("SELECT COUNT(*) as total FROM advertisements WHERE is_active = 1 AND end_date >= CURDATE()");
$stats['active_ads'] = $stmt->fetch()['total'];

// Recent companies (last 7 days)
$stmt = $db->query("
    SELECT c.name, c.manager_name, c.created_at, c.is_approved, c.industry 
    FROM companies c 
    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$recent_companies = $stmt->fetchAll();

// Expiring subscriptions (next 30 days)
$stmt = $db->query("
    SELECT c.name, c.manager_name, c.subscription_end, c.email
    FROM companies c 
    WHERE c.is_active = 1 AND c.subscription_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY c.subscription_end ASC 
    LIMIT 5
");
$expiring_subscriptions = $stmt->fetchAll();

// Monthly statistics for chart
$monthly_stats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM companies WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$month]);
    $monthly_stats[] = [
        'month' => date('M Y', strtotime($month . '-01')),
        'companies' => $stmt->fetch()['total']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>Export Report
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Companies
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['total_companies']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Active Companies
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['active_companies']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Employees
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['total_employees']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Approvals
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['pending_companies']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="row">
                    <!-- Company Registrations Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Company Registrations (Last 12 Months)</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="companyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Sources Pie Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Company Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2">
                                    <canvas id="statusChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-success"></i> Active
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-warning"></i> Pending
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-danger"></i> Inactive
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Tables -->
                <div class="row">
                    <!-- Recent Companies -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Company Registrations</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_companies)): ?>
                                    <p class="text-muted">No recent registrations</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Company</th>
                                                    <th>Manager</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_companies as $company): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($company['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($company['manager_name']); ?></td>
                                                        <td>
                                                            <?php if ($company['is_approved']): ?>
                                                                <span class="badge bg-success">Approved</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Pending</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo format_date($company['created_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                <div class="text-center">
                                    <a href="companies.php" class="btn btn-sm btn-primary">View All Companies</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Subscriptions -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Expiring Subscriptions (Next 30 Days)</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($expiring_subscriptions)): ?>
                                    <p class="text-muted">No subscriptions expiring soon</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Company</th>
                                                    <th>Manager</th>
                                                    <th>Expires</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($expiring_subscriptions as $company): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($company['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($company['manager_name']); ?></td>
                                                        <td>
                                                            <span class="text-warning">
                                                                <?php echo format_date($company['subscription_end']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="mailto:<?php echo $company['email']; ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-envelope"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                <div class="text-center">
                                    <a href="companies.php?filter=expiring" class="btn btn-sm btn-warning">View All Expiring</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Company Registrations Line Chart
        const ctx1 = document.getElementById('companyChart').getContext('2d');
        const companyChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
                datasets: [{
                    label: 'New Companies',
                    data: <?php echo json_encode(array_column($monthly_stats, 'companies')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Company Registrations'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Company Status Pie Chart
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Inactive'],
                datasets: [{
                    data: [
                        <?php echo $stats['active_companies']; ?>,
                        <?php echo $stats['pending_companies']; ?>,
                        <?php echo $stats['total_companies'] - $stats['active_companies'] - $stats['pending_companies']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Company Status Distribution'
                    }
                }
            }
        });
    </script>
</body>
</html>