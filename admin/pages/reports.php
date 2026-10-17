<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Reports';

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'grant';
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$grantReport = $type === 'grant' ? $db->query("SELECT g.*, gc.name as category_name, (SELECT COUNT(*) FROM proposals WHERE grant_id = g.id) as total_applications, (SELECT COUNT(*) FROM proposals WHERE grant_id = g.id AND status = 'approved') as approved_apps FROM grants g LEFT JOIN grant_categories gc ON g.category_id = gc.id ORDER BY g.created_at DESC")->fetchAll() : [];

if ($type === 'application') {
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email as researcher_email, g.title as grant_title FROM proposals p JOIN users u ON p.researcher_id = u.id JOIN grants g ON p.grant_id = g.id";
    $params = [];
    if (!empty($statusFilter)) {
        $sql .= " WHERE p.status = :status";
        $params[':status'] = $statusFilter;
    }
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $appReport = $stmt->fetchAll();
} else {
    $appReport = [];
}

$researcherReport = $type === 'researcher' ? $db->query("SELECT u.*, (SELECT COUNT(*) FROM proposals WHERE researcher_id = u.id) as total_proposals, (SELECT COUNT(*) FROM proposals WHERE researcher_id = u.id AND status = 'approved') as approved_proposals, (SELECT COUNT(*) FROM grant_awards WHERE researcher_id = u.id) as total_awards FROM users u WHERE u.role = 'researcher' ORDER BY u.created_at DESC")->fetchAll() : [];
$reviewerReport = $type === 'reviewer' ? $db->query("SELECT u.*, (SELECT COUNT(*) FROM proposal_reviews WHERE reviewer_id = u.id) as total_reviews, (SELECT COUNT(*) FROM proposal_reviews WHERE reviewer_id = u.id AND status = 'completed') as completed_reviews FROM users u WHERE u.role = 'reviewer' ORDER BY u.created_at DESC")->fetchAll() : [];

if ($type === 'project') {
    $sql = "SELECT ga.*, u.first_name, u.last_name, u.email, g.title as grant_title, p.title as proposal_title, (SELECT COUNT(*) FROM project_milestones WHERE award_id = ga.id) as total_milestones, (SELECT COUNT(*) FROM project_milestones WHERE award_id = ga.id AND status = 'completed') as completed_milestones FROM grant_awards ga JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id JOIN proposals p ON ga.proposal_id = p.id";
    $params = [];
    if (!empty($statusFilter)) {
        $sql .= " WHERE ga.status = :status";
        $params[':status'] = $statusFilter;
    }
    $sql .= " ORDER BY ga.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $projectReport = $stmt->fetchAll();
} else {
    $projectReport = [];
}

$financialReport = $type === 'financial' ? $db->query("SELECT gc.name as category, COUNT(g.id) as total_grants, SUM(g.funding_amount) as total_funding, AVG(g.funding_amount) as avg_funding FROM grants g LEFT JOIN grant_categories gc ON g.category_id = gc.id WHERE g.funding_amount IS NOT NULL GROUP BY gc.name")->fetchAll() : [];

$totalGrants = $db->query("SELECT COUNT(*) as c FROM grants")->fetch()['c'];
$totalApps = $db->query("SELECT COUNT(*) as c FROM proposals")->fetch()['c'];
$totalAwards = $db->query("SELECT COUNT(*) as c FROM grant_awards")->fetch()['c'];
$totalFunding = $db->query("SELECT SUM(awarded_amount) as total FROM grant_awards")->fetch()['total'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Reports & Analytics</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card text-center">
            <div class="stat-value"><?= $totalGrants ?></div>
            <div class="stat-label">Total Grants</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card text-center">
            <div class="stat-value"><?= $totalApps ?></div>
            <div class="stat-label">Total Applications</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card text-center">
            <div class="stat-value"><?= $totalAwards ?></div>
            <div class="stat-label">Total Awards</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card text-center">
            <div class="stat-value"><?= formatAmount($totalFunding ?? 0) ?></div>
            <div class="stat-label">Total Funding</div>
        </div>
    </div>
</div>

<div class="card" data-aos="fade-up">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link <?= $type === 'grant' ? 'active' : '' ?>" href="?type=grant">Grant Opportunities</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'application' ? 'active' : '' ?>" href="?type=application">Applications</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'project' ? 'active' : '' ?>" href="?type=project">Projects & Awards</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'financial' ? 'active' : '' ?>" href="?type=financial">Financial Breakdown</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'researcher' ? 'active' : '' ?>" href="?type=researcher">Researchers</a></li>
            <li class="nav-item"><a class="nav-link <?= $type === 'reviewer' ? 'active' : '' ?>" href="?type=reviewer">Reviewers</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-capitalize">
                    <?= $type ?> Report
                    <?= $statusFilter ? ' - Status: <span class="badge bg-secondary">' . htmlspecialchars(ucwords(str_replace('_', ' ', $statusFilter))) . '</span>' : '' ?>
                </h6>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Report</button>
            </div>
        </div>

        <?php if ($type === 'application'): ?>
        <div class="mb-3 d-flex flex-wrap gap-2 no-print">
            <a href="?type=application" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary' ?>">All Statuses</a>
            <a href="?type=application&status=approved" class="btn btn-sm <?= $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>"><i class="bi bi-check-circle me-1"></i>Approved Grants</a>
            <a href="?type=application&status=submitted" class="btn btn-sm <?= $statusFilter === 'submitted' ? 'btn-info' : 'btn-outline-info' ?>">Submitted</a>
            <a href="?type=application&status=under_review" class="btn btn-sm <?= $statusFilter === 'under_review' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">Under Review</a>
            <a href="?type=application&status=rejected" class="btn btn-sm <?= $statusFilter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
        </div>
        <?php elseif ($type === 'project'): ?>
        <div class="mb-3 d-flex flex-wrap gap-2 no-print">
            <a href="?type=project" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary' ?>">All Projects</a>
            <a href="?type=project&status=active" class="btn btn-sm <?= $statusFilter === 'active' ? 'btn-success' : 'btn-outline-success' ?>"><i class="bi bi-play-circle me-1"></i>Ongoing Projects</a>
            <a href="?type=project&status=completed" class="btn btn-sm <?= $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline-primary' ?>"><i class="bi bi-award me-1"></i>Completed Research</a>
            <a href="?type=project&status=suspended" class="btn btn-sm <?= $statusFilter === 'suspended' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">Suspended</a>
            <a href="?type=project&status=terminated" class="btn btn-sm <?= $statusFilter === 'terminated' ? 'btn-danger' : 'btn-outline-danger' ?>">Terminated</a>
        </div>
        <?php endif; ?>

        <div id="reportContent">
            <?php if ($type === 'grant'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Funding</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Approved</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grantReport as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['title']) ?></td>
                            <td><?= htmlspecialchars($g['category_name'] ?? '-') ?></td>
                            <td><?= $g['funding_amount'] ? formatAmount($g['funding_amount']) : '-' ?></td>
                            <td><?= getStatusBadge($g['status']) ?></td>
                            <td><?= $g['total_applications'] ?></td>
                            <td><?= $g['approved_apps'] ?></td>
                            <td><?= $g['application_end_date'] ? formatDate($g['application_end_date']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($type === 'application'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Proposal Title</th>
                            <th>Researcher</th>
                            <th>Grant Opportunity</th>
                            <th>Requested Budget</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Submitted Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appReport)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No applications matching criteria</td></tr>
                        <?php else: ?>
                        <?php foreach ($appReport as $a): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($a['title']) ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($a['researcher_email'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($a['grant_title']) ?></td>
                            <td><?= $a['budget_amount'] ? formatAmount($a['budget_amount']) : 'N/A' ?></td>
                            <td><?= getStatusBadge($a['status']) ?></td>
                            <td><?= $a['review_score'] !== null ? number_format($a['review_score'], 1) . ' / 100' : '-' ?></td>
                            <td><?= $a['submitted_at'] ? formatDate($a['submitted_at']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($type === 'researcher'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Institution</th>
                            <th>Proposals</th>
                            <th>Approved</th>
                            <th>Awards</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($researcherReport as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><?= htmlspecialchars($r['department'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['institution'] ?? '-') ?></td>
                            <td><?= $r['total_proposals'] ?></td>
                            <td><?= $r['approved_proposals'] ?></td>
                            <td><?= $r['total_awards'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($type === 'reviewer'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Expertise</th>
                            <th>Total Reviews</th>
                            <th>Completed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviewerReport as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><?= htmlspecialchars($r['expertise_area'] ?? '-') ?></td>
                            <td><?= $r['total_reviews'] ?></td>
                            <td><?= $r['completed_reviews'] ?></td>
                            <td><?= getStatusBadge($r['is_active'] ? 'active' : 'inactive') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($type === 'project'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project / Proposal</th>
                            <th>Researcher</th>
                            <th>Grant</th>
                            <th>Award Amount</th>
                            <th>Status</th>
                            <th>Milestones</th>
                            <th>Completed</th>
                            <th>Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projectReport)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No projects matching criteria</td></tr>
                        <?php else: ?>
                        <?php foreach ($projectReport as $pr): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($pr['proposal_title'] ?? 'Research Project') ?></strong></td>
                            <td><?= htmlspecialchars($pr['first_name'] . ' ' . $pr['last_name']) ?></td>
                            <td><?= htmlspecialchars($pr['grant_title']) ?></td>
                            <td><span class="text-success fw-bold"><?= formatAmount($pr['awarded_amount']) ?></span></td>
                            <td><?= getStatusBadge($pr['status']) ?></td>
                            <td><?= $pr['total_milestones'] ?></td>
                            <td><?= $pr['completed_milestones'] ?></td>
                            <td><?= formatDate($pr['start_date']) ?> - <?= $pr['end_date'] ? formatDate($pr['end_date']) : 'Ongoing' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($type === 'financial'): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Grants</th>
                            <th>Total Funding</th>
                            <th>Average Funding</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financialReport as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['category'] ?? 'Uncategorized') ?></td>
                            <td><?= $f['total_grants'] ?></td>
                            <td><?= formatAmount($f['total_funding'] ?? 0) ?></td>
                            <td><?= formatAmount($f['avg_funding'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
