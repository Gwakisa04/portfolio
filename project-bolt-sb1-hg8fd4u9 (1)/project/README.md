# LocalBizHub - Employee & Business Management System

A comprehensive full-stack web application for managing local businesses and their employees, built with PHP, MySQL, HTML5, CSS3, and Bootstrap.

## 🚀 Features

### Admin Panel (Super Admin)
- **Dashboard**: Overview of all companies, employees, and system statistics
- **Company Management**: Approve/reject company registrations, manage subscriptions
- **Advertisement Control**: Manage featured companies and promotional content
- **Reporting**: Generate comprehensive reports on business activities
- **User Management**: Create and manage admin users

### Company Manager Panel
- **Employee Management**: Full CRUD operations for employee records
- **Attendance Tracking**: Mark daily attendance and generate reports
- **Payroll Management**: Calculate and track salary payments
- **Leave Management**: Handle employee leave requests and approvals
- **Business Profile**: Manage company information and advertisements

### Public Website
- **Business Directory**: Browse and search local businesses
- **Advanced Filtering**: Filter by industry, location, and services
- **Company Profiles**: Detailed business information and contact details
- **Featured Businesses**: Highlighted premium company listings

## 🛠 Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8.0+, PDO for database operations
- **Database**: MySQL 8.0+
- **Styling**: Custom CSS with modern design principles
- **Authentication**: Secure session-based authentication
- **File Uploads**: Image handling for company logos

## 📁 Project Structure

```
localbiz-hub/
├── admin/                  # Admin panel files
├── manager/                # Company manager panel
├── public/                 # Public website
├── includes/               # Shared PHP includes
├── assets/                 # CSS, JS, and image assets
├── uploads/               # User uploaded files
├── sql/                   # Database schema and migrations
├── login.php              # Main login page
├── register.php           # Company registration
└── index.php             # Main entry point
```

## 🔧 Installation

1. **Database Setup**
   ```sql
   -- Import the database schema
   source sql/database_schema.sql
   ```

2. **Configuration**
   - Update database credentials in `includes/config.php`
   - Set appropriate file permissions for uploads directory

3. **Default Credentials**
   - **Admin**: username: `admin`, password: `admin123`
   - **Sample Company**: email: `john@techsolutions.com`, password: `password`

## 🔐 Security Features

- **Password Hashing**: Using PHP's `password_hash()` function
- **CSRF Protection**: Token-based request validation
- **SQL Injection Prevention**: Prepared statements with PDO
- **File Upload Security**: Validated file types and sizes
- **Session Management**: Secure session handling
- **Input Validation**: Comprehensive data sanitization

## 📱 Responsive Design

The application is fully responsive and works seamlessly across:
- Desktop computers (1200px+)
- Tablets (768px - 1199px)
- Mobile phones (< 768px)

## 🎨 Design Features

- **Modern UI**: Clean, professional interface design
- **Interactive Elements**: Hover effects and smooth transitions
- **Color System**: Consistent color palette with proper contrast
- **Typography**: Readable fonts with proper hierarchy
- **Accessibility**: WCAG 2.1 compliant design elements

## 📊 Key Functionalities

### Employee Management
- Add, edit, and delete employee records
- Track employee status (active, inactive, terminated)
- Salary management and calculations
- Employee role assignments

### Attendance System
- Daily attendance marking
- Multiple status options (present, absent, late, half-day)
- Monthly attendance reports
- Attendance statistics and analytics

### Payroll Processing
- Automated salary calculations
- Bonus and deduction management
- Monthly payroll reports
- Payment status tracking

### Leave Management
- Leave request submissions
- Approval/rejection workflow
- Different leave types (sick, vacation, personal, etc.)
- Leave balance tracking

### Business Directory
- Public company profiles
- Search and filter functionality
- Featured business listings
- Contact information display

## 🔄 Database Schema

The system uses 8 main tables:
- `admins` - System administrators
- `companies` - Registered businesses
- `employees` - Company employees
- `attendance` - Daily attendance records
- `payroll` - Salary payment records
- `leaves` - Leave request management
- `advertisements` - Business promotions
- `messages` - System communications

## 🚀 Getting Started

1. Clone or download the project files
2. Set up a web server (Apache/Nginx) with PHP 8.0+
3. Create a MySQL database and import the schema
4. Configure database connection settings
5. Access the application through your web browser
6. Use default credentials to log in and explore features

## 🤝 Contributing

This is a complete, production-ready application designed for local business management. Feel free to customize and extend based on your specific requirements.

## 📄 License

This project is open source and available under standard web development licensing terms.

## 📞 Support

For technical support or feature requests, please refer to the documentation or contact the development team.

---

**LocalBizHub** - Connecting local businesses with their community through efficient management solutions.