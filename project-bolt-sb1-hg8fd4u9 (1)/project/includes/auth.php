<?php
/**
 * Authentication and Authorization Functions
 * LocalBizHub - Employee & Business Management System
 */

require_once 'config.php';

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Check if company manager is logged in
 */
function is_company_logged_in() {
    return isset($_SESSION['company_id']) && isset($_SESSION['company_name']);
}

/**
 * Require admin authentication
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Require company authentication
 */
function require_company_login() {
    if (!is_company_logged_in()) {
        redirect('../login.php');
    }
    
    // Check if company subscription is still valid
    $db = get_db();
    $stmt = $db->prepare("SELECT subscription_end, is_active FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();
    
    if (!$company || !$company['is_active'] || !is_company_subscription_valid($company['subscription_end'])) {
        logout_company();
        redirect('../login.php?error=subscription_expired');
    }
}

/**
 * Login admin user
 */
function login_admin($username, $password) {
    $db = get_db();
    
    $stmt = $db->prepare("SELECT id, username, password, full_name FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['user_type'] = 'admin';
        
        return true;
    }
    
    return false;
}

/**
 * Login company manager
 */
function login_company($email, $password) {
    $db = get_db();
    
    $stmt = $db->prepare("
        SELECT id, name, manager_name, email, password, subscription_end, is_active, is_approved 
        FROM companies 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $company = $stmt->fetch();
    
    if (!$company) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    if (!$company['is_approved']) {
        return ['success' => false, 'message' => 'Your account is pending approval'];
    }
    
    if (!$company['is_active']) {
        return ['success' => false, 'message' => 'Your account has been deactivated'];
    }
    
    if (!is_company_subscription_valid($company['subscription_end'])) {
        return ['success' => false, 'message' => 'Your subscription has expired'];
    }
    
    if (password_verify($password, $company['password'])) {
        $_SESSION['company_id'] = $company['id'];
        $_SESSION['company_name'] = $company['name'];
        $_SESSION['company_manager'] = $company['manager_name'];
        $_SESSION['company_email'] = $company['email'];
        $_SESSION['user_type'] = 'company';
        
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

/**
 * Logout admin user
 */
function logout_admin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['user_type']);
}

/**
 * Logout company user
 */
function logout_company() {
    unset($_SESSION['company_id']);
    unset($_SESSION['company_name']);
    unset($_SESSION['company_manager']);
    unset($_SESSION['company_email']);
    unset($_SESSION['user_type']);
}

/**
 * Register new company (requires admin approval)
 */
function register_company($data) {
    $db = get_db();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM companies WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Handle logo upload
    $logo_filename = 'default-logo.png';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploaded_logo = upload_file($_FILES['logo']);
        if ($uploaded_logo) {
            $logo_filename = $uploaded_logo;
        }
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO companies (name, manager_name, email, password, phone, industry, location, description, logo, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['company_name'],
            $data['manager_name'],
            $data['email'],
            $hashed_password,
            $data['phone'],
            $data['industry'],
            $data['location'],
            $data['description'],
            $logo_filename
        ]);
        
        return ['success' => true, 'message' => 'Registration successful. Please wait for admin approval.'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Get current user info
 */
function get_logged_in_user() {
    if (is_admin_logged_in()) {
        return [
            'type' => 'admin',
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'username' => $_SESSION['admin_username']
        ];
    } elseif (is_company_logged_in()) {
        return [
            'type' => 'company',
            'id' => $_SESSION['company_id'],
            'name' => $_SESSION['company_name'],
            'manager' => $_SESSION['company_manager'],
            'email' => $_SESSION['company_email']
        ];
    }
    
    return null;
}
?>