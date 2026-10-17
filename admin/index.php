<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Admin Dashboard';

$totalResearchers = getTableCount('users', "role = 'researcher'");
$totalReviewers = getTableCount('users', "role = 'reviewer'");
$totalAwards = getTableCount('grant_awards');
$totalGrants = getTableCount('grants');
$totalApplications = getTableCount('proposals');
$approvedApps = getTableCount('proposals', "status = 'approved'");
$pendingReviews = getTableCount('proposals', "status = 'submitted' OR status = 'under_review'");

$monthlyApps = $db->query("SELECT MONTH(submitted_at) as month, COUNT(*) as count FROM proposals WHERE submitted_at IS NOT NULL AND YEAR(submitted_at) = YEAR(CURDATE()) GROUP BY MONTH(submitted_at) ORDER BY month")->fetchAll();
$months = array_fill(1, 12, 0);
foreach ($monthlyApps as $row) {
    $months[(int)$row['month']] = (int)$row['count'];
}

$categoryGrants = $db->query("SELECT gc.name, COUNT(g.id) as count FROM grants g JOIN grant_categories gc ON g.category_id = gc.id GROUP BY gc.name")->fetchAll();

$statusCounts = $db->query("SELECT status, COUNT(*) as count FROM proposals GROUP BY status")->fetchAll();
$statusLabels = [];
$statusData = [];
foreach ($statusCounts as $row) {
    $statusLabels[] = ucwords(str_replace('_', ' ', $row['status']));
    $statusData[] = (int)$row['count'];
}

$monthlyUsers = $db->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM users WHERE YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY month")->fetchAll();
$userMonths = array_fill(1, 12, 0);
foreach ($monthlyUsers as $row) {
    $userMonths[(int)$row['month']] = (int)$row['count'];
}

$recentActivities = $db->query("SELECT al.*, u.first_name, u.last_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Admin Dashboard</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Overview</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $totalResearchers ?></div>
                    <div class="stat-label">Total Researchers</div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="50">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $totalReviewers ?></div>
                    <div class="stat-label">Total Reviewers</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-check fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $totalAwards ?></div>
                    <div class="stat-label">Total Awards</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-trophy fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $totalGrants ?></div>
                    <div class="stat-label">Total Grants</div>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-award fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $totalApplications ?></div>
                    <div class="stat-label">Applications</div>
                </div>
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-file-text fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="250">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $approvedApps ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="stat-value"><?= $pendingReviews ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6" data-aos="fade-up">
        <div class="card">
            <div class="card-header">Applications by Month</div>
            <div class="card-body">
                <canvas id="applicationsChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card">
            <div class="card-header">Grant Distribution by Category</div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6" data-aos="fade-up">
        <div class="card">
            <div class="card-header">Approval Statistics</div>
            <div class="card-body">
                <canvas id="approvalChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card">
            <div class="card-header">Monthly Registrations</div>
            <div class="card-body">
                <canvas id="registrationsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Activities</span>
        <a href="<?= SITE_URL ?>admin/pages/audit_logs.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivities as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? 'System')) ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['action']))) ?></td>
                        <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
                        <td><?= formatDateTime($log['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentActivities)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No activities found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    new Chart(document.getElementById('applicationsChart'), {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            datasets: [{
                label: 'Applications',
                data: [<?= implode(',', $months) ?>],
                backgroundColor: 'rgba(13,110,253,0.6)',
                borderColor: '#537497',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($categoryGrants as $i => $c): ?><?= $i > 0 ? ',' : '' ?>'<?= htmlspecialchars($c['name']) ?>'<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($categoryGrants as $i => $c): ?><?= $i > 0 ? ',' : '' ?><?= $c['count'] ?><?php endforeach; ?>],
                backgroundColor: ['#537497','#c15757','#65a390','#65a390','#b8895e','#d4a373','#c4936f','#96b2c6']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('approvalChart'), {
        type: 'pie',
        data: {
            labels: [<?php foreach ($statusCounts as $i => $s): ?><?= $i > 0 ? ',' : '' ?>'<?= htmlspecialchars(ucwords(str_replace('_', ' ', $s['status']))) ?>'<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($statusCounts as $i => $s): ?><?= $i > 0 ? ',' : '' ?><?= $s['count'] ?><?php endforeach; ?>],
                backgroundColor: ['#65a390','#c15757','#c4936f','#537497','#d4a373']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('registrationsChart'), {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            datasets: [{
                label: 'Registrations',
                data: [<?= implode(',', $userMonths) ?>],
                borderColor: '#65a390',
                backgroundColor: 'rgba(25,135,84,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
