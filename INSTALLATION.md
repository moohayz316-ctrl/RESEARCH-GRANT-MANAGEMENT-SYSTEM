# Research Grant Management System - Installation Guide

## Prerequisites
- AppServ (Apache + PHP + MySQL) installed and running
- Web browser (Chrome, Firefox, Edge, etc.)

## Installation Steps

### 1. Copy Project Files
1. Navigate to your AppServ's `www` directory:
   - Default: `C:\AppServ\www\`
2. Copy the entire `research-grant-system` folder into the `www` directory
3. Final path should be: `C:\AppServ\www\research-grant-system\`

### 2. Create Database
1. Open your web browser and go to: `http://localhost/phpmyadmin/`
2. Login with:
   - Username: `root`
   - Password: (leave blank, unless you set one)
3. Click on "Import" tab in the top navigation
4. Click "Choose File" and select `database/database.sql` from the project folder
5. Scroll down and click "Go" to import

### 3. Configure Database Connection
1. Open `config/config.php` in a text editor
2. If you changed MySQL password, update:
   ```php
   define('DB_PASS', 'your_password');
   ```
3. If you changed the project folder name, update:
   ```php
   define('SITE_URL', 'http://localhost/your-folder-name/');
   ```

### 4. Set Directory Permissions
Ensure the following folders have write permissions:
- `uploads/profiles/`
- `uploads/cvs/`
- `uploads/proposals/`
- `uploads/guidelines/`
- `uploads/reports/`

On Windows (AppServ), these should already be writable by default.

### 5. Access the Application
1. Open your browser and go to:
   - `http://localhost/research-grant-system/`
2. You should see the landing page

### 6. Default Login Credentials

All sample accounts use the password: `00000000`

#### 1. Administrator
- **Email:** `admin@researchgrants.com`
- **Password:** `00000000`
- **Access:** System Administration, Grants, Reviews, Awards, Fund Disbursements, Project Monitoring, Reports

#### 2. Researcher
- **Email:** `daniska@gmail.com`
- **Password:** `00000000`
- **Access:** Browse Grants, Submit Proposals, Track Applications, View Awards & Disbursements, Submit Progress Reports

#### 3. Reviewer
- **Email:** `mahi@gmail.com`
- **Password:** `00000000`
- **Access:** Evaluate Assigned Proposals, Submit Scores, Recommendations & Recommended Funding Amounts

## System Architecture

### Directory Structure
```
research-grant-system/
├── admin/              # Admin module
│   ├── index.php       # Admin dashboard
│   ├── pages/          # Admin pages
│   └── ajax/           # AJAX handlers
├── researcher/         # Researcher module
│   ├── index.php       # Researcher dashboard
│   ├── pages/          # Researcher pages
│   └── ajax/           # AJAX handlers
├── reviewer/           # Reviewer module
│   ├── index.php       # Reviewer dashboard
│   ├── pages/          # Reviewer pages
│   └── ajax/           # AJAX handlers
├── grant_manager/      # Grant Manager module
│   ├── index.php       # Grant Manager dashboard
│   ├── pages/          # Grant Manager pages
│   └── ajax/           # AJAX handlers
├── includes/           # Shared components
│   ├── auth.php        # Authentication functions
│   ├── functions.php   # Common functions
│   ├── header.php      # HTML header & sidebar
│   ├── footer.php      # Scripts & closing tags
│   ├── navbar.php      # Top navigation bar
│   └── sidebar.php     # Sidebar menu
├── config/             # Configuration
│   ├── config.php      # App constants
│   └── database.php    # PDO database connection
├── assets/             # Static assets
│   ├── css/style.css   # Custom styles
│   └── js/main.js      # Custom JavaScript
├── uploads/            # File uploads
│   ├── profiles/       # Profile pictures
│   ├── cvs/            # Researcher CVs
│   ├── proposals/      # Proposal documents
│   ├── guidelines/     # Grant guidelines
│   └── reports/        # Progress/final reports
├── database/           # SQL files
│   └── database.sql    # Complete database schema
├── index.php           # Landing page
├── login.php           # Login page
├── register.php        # Registration page
├── forgot_password.php # Password reset request
├── reset_password.php  # Password reset form
├── logout.php          # Logout handler
└── .htaccess           # Security rules
```

## Features Overview

### Admin
- Dashboard with real-time statistics, monthly analytics, and charts
- User management (CRUD for Researchers, Reviewers, Administrators)
- Grant opportunity management (Create, Edit, Publish, Categories)
- Application review tracking and reviewer assignment
- Grant approval and awarding workflow
- **Fund Disbursement Management** (Record instalments, payment references, tracking)
- **Project Monitoring** (Monitored Projects, Milestones, Progress Reviews)
- Analytical and financial reports generation
- Audit log viewer and system settings

### Researcher
- Account registration, profile, and academic portfolio
- Browse and search published grant opportunities
- Submit comprehensive proposals with budgets and uploaded documents
- Track proposal review and approval status
- View awards, approved funding, and instalment disbursement history
- Submit progress reports, expenditure details, and milestone updates

### Reviewer
- Reviewer dashboard with assigned proposals list
- Comprehensive proposal evaluation (problem statement, methodology, outcomes, budget)
- Score submissions, provide constructive comments, and select recommendations
- Enter recommended funding amount
- View historical reviews and evaluation records

## Security Features
- PDO prepared statements (SQL injection protection)
- XSS protection (htmlspecialchars, sanitizeInput)
- Password hashing (bcrypt)
- Session-based authentication
- Role-based access control
- CSRF token support
- Input validation and sanitization
- File upload validation
- Audit logging

## Technologies Used
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP 8+
- **Database:** MySQL (via PDO)
- **Libraries:** Chart.js, DataTables, SweetAlert2, AOS, Bootstrap Icons

## Troubleshooting

### "Database connection failed"
- Ensure MySQL is running in AppServ
- Check credentials in `config/config.php`
- Verify database was imported successfully

### Blank page on login
- Check PHP error logs in AppServ
- Enable `display_errors` in `config/config.php` temporarily

### File upload fails
- Check folder write permissions
- Check file size limits in `config/config.php`
- Check PHP's `upload_max_filesize` in php.ini

### 404 errors on pages
- Verify `SITE_URL` in `config/config.php` matches your installation path
- Check if `.htaccess` is causing issues (rename to `.htaccess.bak` to test)
