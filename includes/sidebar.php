<?php
$role = $_SESSION['user_role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
$baseUrl = SITE_URL;
?>
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">Research Grant</span>
                <span class="brand-desc">Management System</span>
            </div>
        </div>
        <button type="button" id="sidebarCollapse" class="sidebar-toggle-btn d-none d-lg-flex" title="Collapse sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="<?= getAvatarUrl($_SESSION['user_name']) ?>" alt="">
            <span class="user-status online"></span>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="user-role text-capitalize"><?= str_replace('_', ' ', $role) ?></span>
        </div>
    </div>

    <div class="sidebar-menu">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="<?= $baseUrl ?><?= $role ?>/index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <?php if ($role === 'admin'): ?>
            <li class="nav-item has-sub">
                <a href="#usersMenu" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">User Management</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="sub-menu collapse" id="usersMenu" data-bs-parent="#sidebar">
                    <li><a href="<?= $baseUrl ?>admin/pages/researchers.php" class="sub-link">Researchers</a></li>
                    <li><a href="<?= $baseUrl ?>admin/pages/reviewers.php" class="sub-link">Reviewers</a></li>
                    <li><a href="<?= $baseUrl ?>admin/pages/all_users.php" class="sub-link">All Users</a></li>
                </ul>
            </li>
            <li class="nav-item has-sub">
                <a href="#grantsMenu" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <i class="bi bi-award nav-icon"></i>
                    <span class="nav-text">Grants</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="sub-menu collapse" id="grantsMenu" data-bs-parent="#sidebar">
                    <li><a href="<?= $baseUrl ?>admin/pages/create_grant.php" class="sub-link">Create Grant</a></li>
                    <li><a href="<?= $baseUrl ?>admin/pages/manage_grants.php" class="sub-link">Manage Grants</a></li>
                    <li><a href="<?= $baseUrl ?>admin/pages/categories.php" class="sub-link">Categories</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>admin/pages/applications.php" class="nav-link">
                    <i class="bi bi-file-earmark-text nav-icon"></i>
                    <span class="nav-text">Applications</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>admin/pages/reviews.php" class="nav-link">
                    <i class="bi bi-chat-square-text nav-icon"></i>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>admin/pages/awards.php" class="nav-link <?= $currentPage === 'awards.php' ? 'active' : '' ?>">
                    <i class="bi bi-trophy nav-icon"></i>
                    <span class="nav-text">Awards</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>admin/pages/disbursements.php" class="nav-link <?= $currentPage === 'disbursements.php' ? 'active' : '' ?>">
                    <i class="bi bi-cash-stack nav-icon"></i>
                    <span class="nav-text">Fund Disbursements</span>
                </a>
            </li>
            <li class="nav-item has-sub">
                <a href="#monitoringMenu" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <i class="bi bi-kanban nav-icon"></i>
                    <span class="nav-text">Project Monitoring</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="sub-menu collapse" id="monitoringMenu" data-bs-parent="#sidebar">
                    <li><a href="<?= $baseUrl ?>grant_manager/pages/projects.php" class="sub-link">Monitored Projects</a></li>
                    <li><a href="<?= $baseUrl ?>grant_manager/pages/milestones.php" class="sub-link">Milestones</a></li>
                    <li><a href="<?= $baseUrl ?>grant_manager/pages/progress_reviews.php" class="sub-link">Progress Reviews</a></li>
                </ul>
            </li>
            <li class="nav-item has-sub">
                <a href="#reportsMenu" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false">
                    <i class="bi bi-bar-chart nav-icon"></i>
                    <span class="nav-text">Reports</span>
                    <i class="bi bi-chevron-down nav-arrow"></i>
                </a>
                <ul class="sub-menu collapse" id="reportsMenu" data-bs-parent="#sidebar">
                    <li><a href="<?= $baseUrl ?>admin/pages/reports.php" class="sub-link">All Reports</a></li>
                    <li><a href="<?= $baseUrl ?>admin/pages/audit_logs.php" class="sub-link">Audit Logs</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>admin/pages/settings.php" class="nav-link">
                    <i class="bi bi-gear nav-icon"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role === 'researcher'): ?>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/grants.php" class="nav-link">
                    <i class="bi bi-award nav-icon"></i>
                    <span class="nav-text">Available Grants</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/my_proposals.php" class="nav-link">
                    <i class="bi bi-file-earmark-text nav-icon"></i>
                    <span class="nav-text">My Proposals</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/my_awards.php" class="nav-link">
                    <i class="bi bi-trophy nav-icon"></i>
                    <span class="nav-text">My Awards</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/progress_reports.php" class="nav-link">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span class="nav-text">Progress Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/final_reports.php" class="nav-link">
                    <i class="bi bi-file-check nav-icon"></i>
                    <span class="nav-text">Final Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/funding_history.php" class="nav-link">
                    <i class="bi bi-cash-stack nav-icon"></i>
                    <span class="nav-text">Funding History</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>researcher/pages/profile.php" class="nav-link">
                    <i class="bi bi-person nav-icon"></i>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role === 'reviewer'): ?>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>reviewer/pages/assigned_proposals.php" class="nav-link">
                    <i class="bi bi-file-earmark-check nav-icon"></i>
                    <span class="nav-text">Assigned Proposals</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>reviewer/pages/review_history.php" class="nav-link">
                    <i class="bi bi-clock-history nav-icon"></i>
                    <span class="nav-text">Review History</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $baseUrl ?>reviewer/pages/profile.php" class="nav-link">
                    <i class="bi bi-person nav-icon"></i>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a href="<?= $baseUrl ?>logout.php" class="nav-link">
            <i class="bi bi-box-arrow-right nav-icon"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
</nav>
