<?php
/**
 * Public Homepage - LocalBizHub
 * Display company advertisements and services
 */

require_once '../includes/config.php';

$db = get_db();

// Handle search and filters
$search = $_GET['search'] ?? '';
$industry = $_GET['industry'] ?? '';
$location = $_GET['location'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12; // Show 12 companies per page
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = ["c.is_active = 1", "c.is_approved = 1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

if (!empty($industry)) {
    $where_conditions[] = "c.industry = ?";
    $params[] = $industry;
}

if (!empty($location)) {
    $where_conditions[] = "c.location LIKE ?";
    $params[] = "%$location%";
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM companies c WHERE $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Get companies with their latest advertisement
$query = "
    SELECT c.*, 
           a.title as ad_title, 
           a.description as ad_description,
           a.ad_type,
           a.contact_phone as ad_phone,
           a.contact_email as ad_email
    FROM companies c 
    LEFT JOIN (
        SELECT DISTINCT company_id, title, description, ad_type, contact_phone, contact_email,
               ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY created_at DESC) as rn
        FROM advertisements 
        WHERE is_active = 1 AND end_date >= CURDATE()
    ) a ON c.id = a.company_id AND a.rn = 1
    WHERE $where_clause 
    ORDER BY c.is_featured DESC, c.created_at DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$companies = $stmt->fetchAll();

// Get filter options
$industries_query = "SELECT DISTINCT industry FROM companies WHERE is_active = 1 AND is_approved = 1 ORDER BY industry";
$industries = $db->query($industries_query)->fetchAll(PDO::FETCH_COLUMN);

$locations_query = "SELECT DISTINCT location FROM companies WHERE is_active = 1 AND is_approved = 1 ORDER BY location";
$locations = $db->query($locations_query)->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Local Business Directory</title>
    <meta name="description" content="Discover local businesses and services in your area. Connect with trusted companies and explore their offerings.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/public.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-store me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#businesses">Browse Businesses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../register.php">Register Your Business</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-5 mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Discover Local Businesses
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Connect with trusted local companies and discover amazing services in your area. 
                        From technology solutions to professional services, find what you need.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#businesses" class="btn btn-light btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Businesses
                        </a>
                        <a href="../register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-plus me-2"></i>Join Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg?auto=compress&cs=tinysrgb&w=800" 
                         alt="Local Business" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section id="businesses" class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search Businesses</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Company name, service...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="industry" class="form-label">Industry</label>
                                    <select class="form-select" id="industry" name="industry">
                                        <option value="">All Industries</option>
                                        <?php foreach ($industries as $ind): ?>
                                            <option value="<?php echo htmlspecialchars($ind); ?>" 
                                                <?php echo ($industry === $ind) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ind); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="location" class="form-label">Location</label>
                                    <select class="form-select" id="location" name="location">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?php echo htmlspecialchars($loc); ?>" 
                                                <?php echo ($location === $loc) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($loc); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    Local Businesses 
                    <span class="badge bg-primary"><?php echo number_format($total_records); ?> found</span>
                </h2>
                
                <?php if ($search || $industry || $location): ?>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($companies)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No businesses found</h3>
                    <p class="text-muted mb-4">Try adjusting your search criteria or browse all businesses.</p>
                    <a href="index.php" class="btn btn-primary">Browse All Businesses</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($companies as $company): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm business-card <?php echo $company['is_featured'] ? 'featured-business' : ''; ?>">
                                <?php if ($company['is_featured']): ?>
                                    <div class="featured-badge">
                                        <i class="fas fa-star"></i> Featured
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="../uploads/<?php echo htmlspecialchars($company['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($company['name']); ?> Logo" 
                                             class="company-logo me-3"
                                             onerror="this.src='../assets/img/default-logo.png'">
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($company['name']); ?></h5>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($company['industry']); ?></span>
                                        </div>
                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($company['location']); ?>
                                    </p>

                                    <p class="card-text">
                                        <?php 
                                        $description = $company['ad_description'] ?: $company['description'];
                                        echo htmlspecialchars(substr($description, 0, 120)) . (strlen($description) > 120 ? '...' : '');
                                        ?>
                                    </p>

                                    <?php if ($company['ad_title']): ?>
                                        <div class="alert alert-light mb-3">
                                            <strong class="text-primary">
                                                <i class="fas fa-bullhorn me-1"></i>
                                                <?php echo htmlspecialchars($company['ad_title']); ?>
                                            </strong>
                                            <?php if ($company['ad_type']): ?>
                                                <span class="badge bg-secondary ms-2">
                                                    <?php echo ucfirst($company['ad_type']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($company['phone']): ?>
                                                <a href="tel:<?php echo $company['phone']; ?>" 
                                                   class="btn btn-sm btn-outline-success me-2">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($company['email']): ?>
                                                <a href="mailto:<?php echo $company['email']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="btn btn-primary btn-sm" 
                                                onclick="showCompanyDetails(<?php echo htmlspecialchars(json_encode($company)); ?>)">
                                            <i class="fas fa-info-circle me-1"></i>Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&location=<?php echo urlencode($location); ?>">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&location=<?php echo urlencode($location); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&location=<?php echo urlencode($location); ?>">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Grow Your Business?</h2>
            <p class="lead mb-4">Join hundreds of local businesses already using LocalBizHub to connect with customers.</p>
            <a href="../register.php" class="btn btn-light btn-lg">
                <i class="fas fa-rocket me-2"></i>Get Started Today
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">Connecting local businesses with their community.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Company Details Modal -->
    <div class="modal fade" id="companyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="companyModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="companyModalBody">
                    <!-- Company details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showCompanyDetails(company) {
            document.getElementById('companyModalTitle').textContent = company.name;
            
            const modalBody = document.getElementById('companyModalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <img src="../uploads/${company.logo}" alt="${company.name} Logo" 
                             class="img-fluid rounded mb-3" 
                             onerror="this.src='../assets/img/default-logo.png'">
                    </div>
                    <div class="col-md-8">
                        <h6><i class="fas fa-building me-2"></i>Company Information</h6>
                        <p><strong>Industry:</strong> ${company.industry}</p>
                        <p><strong>Location:</strong> ${company.location}</p>
                        <p><strong>Manager:</strong> ${company.manager_name}</p>
                        
                        ${company.description ? `
                            <h6 class="mt-3"><i class="fas fa-info-circle me-2"></i>About</h6>
                            <p>${company.description}</p>
                        ` : ''}
                        
                        ${company.ad_title ? `
                            <h6 class="mt-3"><i class="fas fa-bullhorn me-2"></i>Current Promotion</h6>
                            <div class="alert alert-info">
                                <strong>${company.ad_title}</strong>
                                ${company.ad_description ? `<br><small>${company.ad_description}</small>` : ''}
                            </div>
                        ` : ''}
                        
                        <h6 class="mt-3"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                        <div class="d-flex gap-2">
                            ${company.phone ? `
                                <a href="tel:${company.phone}" class="btn btn-success">
                                    <i class="fas fa-phone me-1"></i>${company.phone}
                                </a>
                            ` : ''}
                            ${company.email ? `
                                <a href="mailto:${company.email}" class="btn btn-primary">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('companyModal')).show();
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>