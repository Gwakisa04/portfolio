/*
# LocalBizHub Database Schema

Creates the complete database structure for the Employee & Business Management System.

## Tables Created:
1. **admins** - Super admin users who manage the platform
2. **companies** - Registered companies with subscription details
3. **employees** - Company employees with their details
4. **attendance** - Daily attendance records
5. **payroll** - Monthly salary payment records
6. **leaves** - Employee leave requests and approvals
7. **advertisements** - Company advertisements and promotions
8. **messages** - Communication between companies and admin

## Security Features:
- Proper foreign key constraints
- Indexed columns for performance
- Default values and constraints
- Created and updated timestamps
*/

CREATE DATABASE IF NOT EXISTS localbiz_hub;
USE localbiz_hub;

-- Table for super admin users
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for companies
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    manager_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    industry VARCHAR(50),
    location VARCHAR(100),
    description TEXT,
    logo VARCHAR(255) DEFAULT 'default-logo.png',
    subscription_start DATE,
    subscription_end DATE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_industry (industry),
    INDEX idx_location (location),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured)
);

-- Table for employees
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    hire_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_status (status)
);

-- Table for attendance records
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    company_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
    notes TEXT,
    marked_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, date),
    INDEX idx_date (date),
    INDEX idx_company_date (company_id, date)
);

-- Table for payroll records
CREATE TABLE IF NOT EXISTS payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    company_id INT NOT NULL,
    month VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    base_salary DECIMAL(10,2) NOT NULL,
    bonus DECIMAL(10,2) DEFAULT 0.00,
    deductions DECIMAL(10,2) DEFAULT 0.00,
    net_salary DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    paid_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_payroll (employee_id, month),
    INDEX idx_month (month),
    INDEX idx_status (status)
);

-- Table for leave requests
CREATE TABLE IF NOT EXISTS leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    company_id INT NOT NULL,
    leave_type ENUM('sick', 'vacation', 'personal', 'emergency', 'maternity', 'other') NOT NULL,
    reason TEXT NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    days_count INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by VARCHAR(100),
    approved_date DATE,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_dates (date_from, date_to),
    INDEX idx_company_status (company_id, status)
);

-- Table for advertisements
CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    ad_type ENUM('job', 'service', 'product', 'promotion') DEFAULT 'service',
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    views_count INT DEFAULT 0,
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_type (ad_type)
);

-- Table for messages/communication
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_type ENUM('admin', 'company') NOT NULL,
    from_id INT NOT NULL,
    to_type ENUM('admin', 'company') NOT NULL,
    to_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    INDEX idx_recipient (to_type, to_id, status),
    INDEX idx_sender (from_type, from_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, email, password, full_name) VALUES 
('admin', 'admin@localbiz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert sample companies for testing
INSERT INTO companies (name, manager_name, email, password, phone, industry, location, description, subscription_start, subscription_end, is_featured, is_active, is_approved) VALUES 
('TechSolutions Ltd', 'John Doe', 'john@techsolutions.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'Technology', 'New York', 'Leading software development company', '2025-01-01', '2025-12-31', TRUE, TRUE, TRUE),
('Green Gardens', 'Jane Smith', 'jane@greengardens.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891', 'Agriculture', 'California', 'Organic farming and landscaping services', '2025-01-01', '2025-12-31', FALSE, TRUE, TRUE);

-- Insert sample employees
INSERT INTO employees (company_id, name, role, email, phone, salary, hire_date, status) VALUES 
(1, 'Alice Johnson', 'Developer', 'alice@techsolutions.com', '+1234567892', 5000.00, '2024-01-15', 'active'),
(1, 'Bob Wilson', 'Designer', 'bob@techsolutions.com', '+1234567893', 4500.00, '2024-02-01', 'active'),
(2, 'Carol Brown', 'Gardener', 'carol@greengardens.com', '+1234567894', 3000.00, '2024-01-10', 'active');

-- Insert sample advertisements
INSERT INTO advertisements (company_id, title, description, ad_type, is_featured, start_date, end_date, contact_phone) VALUES 
(1, 'Senior Developer Position', 'We are looking for experienced developers to join our team', 'job', TRUE, '2025-01-01', '2025-03-31', '+1234567890'),
(2, 'Professional Landscaping Services', 'Transform your garden with our expert landscaping services', 'service', FALSE, '2025-01-01', '2025-06-30', '+1234567891');