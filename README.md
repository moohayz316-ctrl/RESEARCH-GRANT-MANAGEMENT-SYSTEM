# Research Grant Management System (RGMS)

[![PHP](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%20%7C%208.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-Academic%20Use-green)](#license)

A comprehensive, role-based web application designed to streamline the research grant lifecycle for academic institutions, funding organizations, and research agencies. The system manages the entire workflow from grant call creation, proposal submission, multi-reviewer scoring, administrative fund approval, milestone-based fund disbursement tracking, through to research progress monitoring and grant closure.

---

## 📌 Project Overview

In traditional academic environments, research grant management often involves manual paperwork, fragmented email trails, and decentralized spreadsheets. This project provides a centralized, transparent, and auditable digital platform.

### ✨ Key Capabilities

1. **Grant Opportunity Lifecycle:** Call publication, eligibility criteria, budget ceiling, deadline management, and guideline document attachments.
2. **Researcher Submissions:** Online application, proposal documentation upload (PDF), requested budget itemization, research team specification, and real-time status tracking.
3. **Peer Review & Evaluation:** Double-blind or assigned peer-review, quantitative scoring criteria (1-100), qualitative critiques, and funding recommendations.
4. **Administrative Decision & Allocation:** Comparative evaluation of proposals, administrative approval/rejection with formal feedback, and budget allocation.
5. **Milestone Fund Disbursement Tracking:** Recording phased grant disbursement instalments (e.g., First Instalment, Mid-term, Final), transaction payment references, and real-time balance calculations.
6. **Project Monitoring & Reporting:** Periodic research progress submission, expenditure reporting, milestone completion tracking, and administrative review.

---

## 👥 System Roles & Workflow

The system is organized into **three distinct user roles**:

```
+-----------------------------------------------------------------------------------+
|                            RESEARCH GRANT WORKFLOW                               |
+-----------------------------------------------------------------------------------+

 1. Grant Creation (Administrator)
    └── Defines grant title, funding agency, total budget, max per proposal, deadline.

 2. Proposal Submission (Researcher)
    └── Submits proposal document, itemized budget request, and project timeline.

 3. Evaluation & Recommendation (Reviewer)
    └── Reviews assigned proposal, submits rubric score, comments & recommended fund.

 4. Decision & Allocation (Administrator)
    └── Final approval decision, sets formal approved fund amount.

 5. Fund Disbursement (Administrator)
    └── Records phased disbursement tranches (Approved -> 1st Tranche -> 2nd Tranche).

 6. Monitoring & Progress Reports (Researcher & Administrator)
    └── Researcher submits milestone & expenditure reports; Administrator verifies.
```

### 1. 🛡️ Administrator
- Manages users, researchers, reviewers, and role assignments.
- Creates, edits, activates, and closes grant opportunities.
- Assigns submitted proposals to qualified reviewers.
- Reviews reviewer evaluations and gives final grant approval decisions.
- Manages fund allocations and records multi-stage disbursements with payment references.
- Monitors researcher milestone progress and expenditure reports.
- Comprehensive reporting (financial summaries, grant analytics, PDF export).

### 2. 🔬 Researcher
- Browses open grant calls and eligibility guidelines.
- Submits structured grant applications and attaches research proposals.
- Tracks real-time proposal evaluation status and feedback.
- Views approved fund allocations and disbursement instalments.
- Submits progress reports, research milestones, and financial expenditure updates.

### 3. 📝 Reviewer
- Accesses assigned proposals securely with full proposal document preview.
- Evaluates proposals against standardized scoring criteria.
- Submits constructive qualitative feedback, overall score, and recommended funding amount.

---

## 🛠️ Technology Stack

- **Backend:** PHP 7.4 / 8.x (Object-Oriented, PDO / MySQLi, secure session authentication)
- **Database:** MySQL 5.7+ / MariaDB (Relational schema with referential integrity, foreign keys & indexes)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6), Bootstrap 5.3, FontAwesome 6 icons
- **Server:** Apache (AppServ, XAMPP, WampServer, or LAMP stack) with `mod_rewrite` enabled

---

## 📂 Directory Structure

```
research/
├── admin/                     # Administrator portal (management, disbursements, reports)
│   ├── includes/              # Admin header, footer, sidebar navigation
│   └── pages/                 # Admin controllers & views (grants, reviews, disbursements)
├── assets/                    # Static assets
│   ├── css/                   # Custom styles and Bootstrap bundles
│   ├── js/                    # Client-side scripts and validation logic
│   └── images/                # UI logos and icons
├── config/                    # System configuration
│   └── database.php           # Database connection and dynamic base URL detection
├── database/                  # Database schema and initial seeds
│   └── database.sql           # Complete schema, triggers, and sample demonstration data
├── includes/                  # Common libraries, authentication helpers & security
├── pages/                     # Public user pages (browse grants, view details)
├── researcher/                # Researcher portal (applications, disbursements, progress)
├── reviewer/                  # Reviewer portal (assigned proposals, scoring & evaluation)
├── uploads/                   # Secure storage for uploaded files (.gitkeep preserved)
│   ├── cvs/
│   ├── guidelines/
│   ├── profiles/
│   ├── proposals/
│   └── reports/
├── .gitignore                 # Git ignore rules for sensitive files and user uploads
├── .htaccess                  # Apache server routing and upload security configuration
├── INSTALLATION.md            # Detailed step-by-step local & server installation guide
├── index.php                  # Public portal landing page
├── login.php                  # Centralized secure role-based login handler
├── register.php               # Researcher registration
└── README.md                  # Project documentation & appendix reference
```

---

## 🚀 Quick Setup Guide

For complete, detailed instructions, refer to [INSTALLATION.md](INSTALLATION.md).

### 1. Prerequisites
- **Web Server:** AppServ, XAMPP, WAMP, or Apache.
- **PHP:** Version 7.4 or higher.
- **Database:** MySQL 5.7+ or MariaDB.

### 2. Installation
1. Clone or copy this repository into your web server's root directory:
   ```bash
   git clone https://github.com/moohayz316-ctrl/RESEARCH-GRANT-MANAGEMENT-SYSTEM.git research
   ```
   *(e.g., `C:\AppServ\www\research` or `C:\xampp\htdocs\research`)*

2. Start Apache and MySQL services.

3. Import the database:
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Create a new database named `research_grant_db` with collation `utf8mb4_unicode_ci`.
   - Import the file located at: `database/database.sql`.

4. Check Configuration:
   - Verify connection credentials in `config/database.php` (default is user `root`, password `root` or empty `""`).

5. Access the System:
   - Open your browser at: `http://localhost/research/`

---

## 🔐 Default Demo Accounts

All demo accounts are preconfigured with the password: `00000000`

| Role | Email Address | Password | Description |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@researchgrants.com` | `00000000` | Full administrative control & disbursement recording |
| **Researcher** | `daniska@gmail.com` | `00000000` | Sample researcher with submitted and funded proposals |
| **Reviewer** | `mahi@gmail.com` | `00000000` | Sample academic peer reviewer with assigned applications |

---

## 📄 Academic Project Reference

This system was developed as a Final Year Capstone Project in Computer Science / Information Technology to demonstrate the design and implementation of an automated, audit-ready research administration and grant monitoring system.

- **Author / Developer:** Muhammad Yakubu
- **GitHub Repository:** [moohayz316-ctrl/RESEARCH-GRANT-MANAGEMENT-SYSTEM](https://github.com/moohayz316-ctrl/RESEARCH-GRANT-MANAGEMENT-SYSTEM)

---

## ⚖️ License & Attribution

This project is made available for academic, educational, and institutional research purposes. You are free to fork, customize, and extend the system for non-commercial educational use.
