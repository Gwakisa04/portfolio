<?php
/**
 * Company Manager Dashboard - LocalBizHub
 * Main dashboard for company managers
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require company authentication
require_company_login();

$db = get_db();

// Get dashboard statistics
$company_id = $_SESSION['company_id'];
$stats = [];

// Total employees
$stmt = $db->prepare("SELECT COUNT(*) as total FROM employees WHERE company_id = ? AND status = 'active'");
$stmt->execute([$company_id]);
$stats['total_employees'] = $stmt->fetch()['total'];

// Present today
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    WHERE a.company_id = ? AND a.date = CURDATE() AND a.status = 'present' AND e.status = 'active'
");
$stmt->execute([$company_id]);
$stats['present_today'] = $stmt->fetch()['total'];

// This month payroll
$stmt = $db->prepare("
    SELECT COALESCE(SUM(net_salary), 0) as total 
    FROM payroll 
    WHERE company_id = ? AND month = DATE_FORMAT(CURDATE(), '%Y-%m')
");
$stmt->execute([$company_id]);
$stats['monthly_payroll'] = $stmt->fetch()['total'];

// Pending leave requests
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM leaves 
    WHERE company_id = ? AND status = 'pending'
");
$stmt->execute([$company_id]);
$stats['pending_leaves'] = $stmt->fetch()['total'];

// Active advertisements
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM advertisements 
    WHERE company_id = ? AND is_active = 1 AND end_date >= CURDATE()
");
$stmt->execute([$company_id]);
$stats['active_ads'] = $stmt->fetch()['total'];

// Recent employees (last 30 days)
$stmt = $db->prepare("
    SELECT name, role, hire_date, status 
    FROM employees 
    WHERE company_id = ? AND hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY hire_date DESC 
    LIMIT 5
");
$stmt->execute([$company_id]);
$recent_employees = $stmt->fetchAll();

// Pending leave requests details
$stmt = $db->prepare("
    SELECT l.*, e.name as employee_name 
    FROM leaves l 
    JOIN employees e ON l.employee_id = e.id 
    WHERE l.company_id = ? AND l.status = 'pending' 
    ORDER BY l.created_at DESC 
    LIMIT 5
");
$stmt->execute([$company_id]);
$pending_leaves = $stmt->fetchAll();

// Today's attendance summary
$stmt = $db->prepare("
    SELECT 
        a.status,
        COUNT(*) as count
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    WHERE a.company_id = ? AND a.date = CURDATE() AND e.status = 'active'
    GROUP BY a.status
");
$stmt->execute([$company_id]);
$attendance_summary = [];
while ($row = $stmt->fetch()) {
    $attendance_summary[$row['status']] = $row['count'];
}

// Get company info
$stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company_info = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/manager.css" rel="stylesheet">
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
                            <a href="attendance/mark.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-clock me-1"></i>Mark Attendance
                            </a>
                            <a href="employees/add.php" class="btn btn-sm btn-success">
                                <i class="fas fa-user-plus me-1"></i>Add Employee
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subscription Alert -->
                <?php 
                $days_left = (strtotime($company_info['subscription_end']) - time()) / (60 * 60 * 24);
                if ($days_left <= 30 && $days_left > 0): 
                ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Subscription Notice:</strong> Your subscription expires in <?php echo ceil($days_left); ?> days on <?php echo format_date($company_info['subscription_end']); ?>. Please contact admin to renew.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($days_left <= 0): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Subscription Expired:</strong> Your subscription expired on <?php echo format_date($company_info['subscription_end']); ?>. Please contact admin immediately.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
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
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Present Today
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['present_today']); ?>
                                            <span class="small text-muted">/ <?php echo $stats['total_employees']; ?></span>
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
                                            Monthly Payroll
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo format_currency($stats['monthly_payroll']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                            Pending Leaves
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['pending_leaves']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="row">
                    <!-- Today's Attendance Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Today's Attendance</h6>
                                <a href="attendance/mark.php" class="btn btn-sm btn-primary">Mark Attendance</a>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-success"></i> Present (<?php echo $attendance_summary['present'] ?? 0; ?>)
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-danger"></i> Absent (<?php echo $attendance_summary['absent'] ?? 0; ?>)
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-warning"></i> Late (<?php echo $attendance_summary['late'] ?? 0; ?>)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Employees -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Employees (Last 30 Days)</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_employees)): ?>
                                    <p class="text-muted">No new employees in the last 30 days.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Role</th>
                                                    <th>Hire Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_employees as $employee): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($employee['role']); ?></td>
                                                        <td><?php echo format_date($employee['hire_date']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $employee['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($employee['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                <div class="text-center">
                                    <a href="employees/index.php" class="btn btn-sm btn-primary">View All Employees</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Leave Requests -->
                <?php if (!empty($pending_leaves)): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Pending Leave Requests</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Leave Type</th>
                                                    <th>Dates</th>
                                                    <th>Days</th>
                                                    <th>Reason</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_leaves as $leave): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                <?php echo ucfirst($leave['leave_type']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo format_date($leave['date_from']); ?> - 
                                                            <?php echo format_date($leave['date_to']); ?>
                                                        </td>
                                                        <td><?php echo $leave['days_count']; ?> days</td>
                                                        <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50)) . (strlen($leave['reason']) > 50 ? '...' : ''); ?></td>
                                                        <td>
                                                            <a href="leaves/manage.php?id=<?php echo $leave['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                Review
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <a href="leaves/index.php" class="btn btn-sm btn-primary">View All Leave Requests</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Today's Attendance Pie Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late', 'Half Day'],
                datasets: [{
                    data: [
                        <?php echo $attendance_summary['present'] ?? 0; ?>,
                        <?php echo $attendance_summary['absent'] ?? 0; ?>,
                        <?php echo $attendance_summary['late'] ?? 0; ?>,
                        <?php echo $attendance_summary['half_day'] ?? 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Attendance Status'
                    }
                }
            }
        });
    </script>
</body>
</html>