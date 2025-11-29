<?php
/**
 * Company Management - LocalBizHub Admin
 * Manage all registered companies
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require admin authentication
require_admin_login();

$db = get_db();
$success_message = '';
$error_message = '';

// Handle actions
if ($_POST) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $company_id = (int)($_POST['company_id'] ?? 0);
        
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE companies SET is_approved = 1, is_active = 1 WHERE id = ?");
                if ($stmt->execute([$company_id])) {
                    $success_message = 'Company approved successfully.';
                } else {
                    $error_message = 'Failed to approve company.';
                }
                break;
                
            case 'reject':
                $stmt = $db->prepare("UPDATE companies SET is_approved = 0, is_active = 0 WHERE id = ?");
                if ($stmt->execute([$company_id])) {
                    $success_message = 'Company rejected.';
                } else {
                    $error_message = 'Failed to reject company.';
                }
                break;
                
            case 'toggle_featured':
                $stmt = $db->prepare("UPDATE companies SET is_featured = NOT is_featured WHERE id = ?");
                if ($stmt->execute([$company_id])) {
                    $success_message = 'Featured status updated.';
                } else {
                    $error_message = 'Failed to update featured status.';
                }
                break;
                
            case 'toggle_active':
                $stmt = $db->prepare("UPDATE companies SET is_active = NOT is_active WHERE id = ?");
                if ($stmt->execute([$company_id])) {
                    $success_message = 'Active status updated.';
                } else {
                    $error_message = 'Failed to update active status.';
                }
                break;
                
            case 'extend_subscription':
                $months = (int)($_POST['months'] ?? 1);
                $stmt = $db->prepare("
                    UPDATE companies 
                    SET subscription_end = DATE_ADD(COALESCE(subscription_end, CURDATE()), INTERVAL ? MONTH)
                    WHERE id = ?
                ");
                if ($stmt->execute([$months, $company_id])) {
                    $success_message = "Subscription extended by $months month(s).";
                } else {
                    $error_message = 'Failed to extend subscription.';
                }
                break;
        }
    }
}

// Handle filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query based on filters
$where_conditions = [];
$params = [];

if ($filter === 'pending') {
    $where_conditions[] = "is_approved = 0";
} elseif ($filter === 'active') {
    $where_conditions[] = "is_approved = 1 AND is_active = 1";
} elseif ($filter === 'inactive') {
    $where_conditions[] = "is_active = 0";
} elseif ($filter === 'featured') {
    $where_conditions[] = "is_featured = 1";
} elseif ($filter === 'expiring') {
    $where_conditions[] = "subscription_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR manager_name LIKE ? OR email LIKE ? OR industry LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM companies $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Get companies
$query = "
    SELECT c.*, 
           (SELECT COUNT(*) FROM employees e WHERE e.company_id = c.id AND e.status = 'active') as employee_count,
           (SELECT COUNT(*) FROM advertisements a WHERE a.company_id = c.id AND a.is_active = 1) as ad_count
    FROM companies c 
    $where_clause 
    ORDER BY c.created_at DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$companies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies - <?php echo SITE_NAME; ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-building me-2"></i>Companies
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportCompanies()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="filter" class="form-label">Filter</label>
                                <select class="form-select" id="filter" name="filter" onchange="this.form.submit()">
                                    <option value="all" <?php echo ($filter === 'all') ? 'selected' : ''; ?>>All Companies</option>
                                    <option value="pending" <?php echo ($filter === 'pending') ? 'selected' : ''; ?>>Pending Approval</option>
                                    <option value="active" <?php echo ($filter === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($filter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="featured" <?php echo ($filter === 'featured') ? 'selected' : ''; ?>>Featured</option>
                                    <option value="expiring" <?php echo ($filter === 'expiring') ? 'selected' : ''; ?>>Expiring Soon</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search companies, managers, email...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <a href="companies.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-1"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Companies Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Companies List 
                            <span class="badge bg-primary"><?php echo number_format($total_records); ?> total</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($companies)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No companies found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Company</th>
                                            <th>Manager</th>
                                            <th>Industry</th>
                                            <th>Employees</th>
                                            <th>Subscription</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($companies as $company): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="../uploads/<?php echo $company['logo']; ?>" 
                                                             alt="Logo" class="rounded me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                             onerror="this.src='../assets/img/default-logo.png'">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($company['name']); ?></strong>
                                                            <?php if ($company['is_featured']): ?>
                                                                <i class="fas fa-star text-warning ms-1" title="Featured"></i>
                                                            <?php endif; ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($company['email']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($company['manager_name']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($company['phone']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($company['industry']); ?></span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($company['location']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $company['employee_count']; ?> employees</span>
                                                    <br>
                                                    <small class="text-muted"><?php echo $company['ad_count']; ?> ads</small>
                                                </td>
                                                <td>
                                                    <?php if ($company['subscription_end']): ?>
                                                        <?php 
                                                        $days_left = (strtotime($company['subscription_end']) - time()) / (60 * 60 * 24);
                                                        $badge_class = $days_left > 30 ? 'bg-success' : ($days_left > 0 ? 'bg-warning' : 'bg-danger');
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>">
                                                            <?php echo format_date($company['subscription_end']); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo $days_left > 0 ? ceil($days_left) . ' days left' : 'Expired'; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$company['is_approved']): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif ($company['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <?php if (!$company['is_approved']): ?>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="performAction('approve', <?php echo $company['id']; ?>)">
                                                                        <i class="fas fa-check text-success me-2"></i>Approve
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="performAction('reject', <?php echo $company['id']; ?>)">
                                                                        <i class="fas fa-times text-danger me-2"></i>Reject
                                                                    </button>
                                                                </li>
                                                            <?php else: ?>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="performAction('toggle_active', <?php echo $company['id']; ?>)">
                                                                        <i class="fas fa-power-off text-warning me-2"></i>
                                                                        <?php echo $company['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="performAction('toggle_featured', <?php echo $company['id']; ?>)">
                                                                        <i class="fas fa-star text-warning me-2"></i>
                                                                        <?php echo $company['is_featured'] ? 'Remove Featured' : 'Make Featured'; ?>
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="showExtendModal(<?php echo $company['id']; ?>, '<?php echo htmlspecialchars($company['name']); ?>')">
                                                                        <i class="fas fa-calendar-plus text-info me-2"></i>Extend Subscription
                                                                    </button>
                                                                </li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item" href="company_details.php?id=<?php echo $company['id']; ?>">
                                                                    <i class="fas fa-eye text-primary me-2"></i>View Details
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Extend Subscription Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Extend Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="extendForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="extend_subscription">
                        <input type="hidden" name="company_id" id="extend_company_id">
                        
                        <p>Extend subscription for: <strong id="extend_company_name"></strong></p>
                        
                        <div class="mb-3">
                            <label for="months" class="form-label">Extension Period</label>
                            <select class="form-select" name="months" required>
                                <option value="1">1 Month</option>
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12" selected>12 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Extend Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form for actions -->
    <form id="actionForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="action" id="action_type">
        <input type="hidden" name="company_id" id="action_company_id">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function performAction(action, companyId) {
            if (confirm('Are you sure you want to perform this action?')) {
                document.getElementById('action_type').value = action;
                document.getElementById('action_company_id').value = companyId;
                document.getElementById('actionForm').submit();
            }
        }

        function showExtendModal(companyId, companyName) {
            document.getElementById('extend_company_id').value = companyId;
            document.getElementById('extend_company_name').textContent = companyName;
            new bootstrap.Modal(document.getElementById('extendModal')).show();
        }

        document.getElementById('extendForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        });

        function exportCompanies() {
            window.location.href = 'export.php?type=companies&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>';
        }
    </script>
</body>
</html>