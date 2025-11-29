<?php
/**
 * Database Configuration and Global Settings
 * LocalBizHub - Employee & Business Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'localbiz_hub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('SITE_NAME', 'LocalBizHub');
define('SITE_URL', 'http://localhost');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Date/Time Configuration
date_default_timezone_set('UTC');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date) {
    return date('M d, Y', strtotime($date));
}

function is_company_subscription_valid($subscription_end) {
    return strtotime($subscription_end) >= strtotime(date('Y-m-d'));
}

function upload_file($file, $upload_dir = UPLOAD_DIR) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}

// Get database connection
function get_db() {
    return Database::getInstance()->getConnection();
}
?>