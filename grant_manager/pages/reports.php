<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
if (!in_array($_SESSION['user_role'], ['admin', 'grant_manager'])) {
    requireRole('admin');
}

$db = getDb();
$pageTitle = 'Monitoring Reports';

$totalProjects = getTableCount('grant_awards');
$totalFunding = $db->query("SELECT SUM(awarded_amount) as total FROM grant_awards")->fetch()['total'];
$completedProjects = getTableCount('grant_awards', "status = 'completed'");
$completionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0;
$avgDuration = $db->query("SELECT AVG(DATEDIFF(COALESCE(end_date, CURDATE()), start_date)) as avg FROM grant_awards WHERE start_date IS NOT NULL")->fetch()['avg'];

$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'status';

$projectStatus = $db->query("SELECT ga.status, COUNT(*) as count, SUM(ga.awarded_amount) as total_amount FROM grant_awards ga GROUP BY ga.status ORDER BY count DESC")->fetchAll();

$fundingDist = $db->query("SELECT g.title as grant_title, COUNT(ga.id) as project_count, SUM(ga.awarded_amount) as total_funding, AVG(ga.awarded_amount) as avg_funding FROM grant_awards ga JOIN grants g ON ga.grant_id = g.id GROUP BY ga.grant_id ORDER BY total_funding DESC")->fetchAll();

$progressOverview = $db->query("SELECT ga.id, ga.awarded_amount, u.first_name, u.last_name, g.title as grant_title, ga.start_date, ga.end_date, ga.status, (SELECT COUNT(*) FROM project_milestones WHERE award_id = ga.id) as total_milestones, (SELECT COUNT(*) FROM project_milestones WHERE award_id = ga.id AND status = 'completed') as completed_milestones, (SELECT COUNT(*) FROM progress_reports WHERE award_id = ga.id) as report_count, (SELECT COUNT(*) FROM progress_reports WHERE award_id = ga.id AND status = 'approved') as approved_reports FROM grant_awards ga JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id ORDER BY ga.created_at DESC")->fetchAll();

$timelineAnalysis = $db->query("SELECT pm.*, u.first_name, u.last_name, g.title as grant_title FROM project_milestones pm JOIN grant_awards ga ON pm.award_id = ga.id JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id ORDER BY pm.due_date ASC")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Monitoring Reports</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-outline-primary btn-sm no-print" onclick="printReport()"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<?= displaySessionMessages() ?>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="0">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stat-value text-primary"><?= $totalProjects ?></div>
                <div class="stat-label">Total Projects</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stat-value text-success"><?= formatAmount($totalFunding ?? 0) ?></div>
                <div class="stat-label">Total Funding</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stat-value text-info"><?= $completionRate ?>%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stat-value text-warning"><?= $avgDuration ? round($avgDuration) : 0 ?> days</div>
                <div class="stat-label">Avg Project Duration</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link <?= $tab === 'status' ? 'active' : '' ?>" href="?tab=status">Project Status</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'funding' ? 'active' : '' ?>" href="?tab=funding">Funding Distribution</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'progress' ? 'active' : '' ?>" href="?tab=progress">Progress Overview</a></li>
            <li class="nav-item"><a class="nav-link <?= $tab === 'timeline' ? 'active' : '' ?>" href="?tab=timeline">Timeline Analysis</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div id="reportContent">
            <?php if ($tab === 'status'): ?>
            <h6 class="fw-bold mb-3">Projects Grouped by Status</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectStatus as $s): ?>
                        <tr>
                            <td><?= getStatusBadge($s['status']) ?></td>
                            <td><?= $s['count'] ?></td>
                            <td><?= formatAmount($s['total_amount'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($tab === 'funding'): ?>
            <h6 class="fw-bold mb-3">Funding Distribution by Grant</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Grant</th>
                            <th>Projects</th>
                            <th>Total Funding</th>
                            <th>Average Funding</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fundingDist as $f): ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($f['grant_title']) ?></td>
                            <td><?= $f['project_count'] ?></td>
                            <td><?= formatAmount($f['total_funding'] ?? 0) ?></td>
                            <td><?= formatAmount($f['avg_funding'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($tab === 'progress'): ?>
            <h6 class="fw-bold mb-3">Project Progress Overview</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Researcher</th>
                            <th>Amount</th>
                            <th>Milestones</th>
                            <th>Completion</th>
                            <th>Reports</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progressOverview as $po): ?>
                        <?php $pct = $po['total_milestones'] > 0 ? round(($po['completed_milestones'] / $po['total_milestones']) * 100) : 0; ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars(truncateText($po['grant_title'], 30)) ?></td>
                            <td><?= htmlspecialchars($po['first_name'] . ' ' . $po['last_name']) ?></td>
                            <td><?= formatAmount($po['awarded_amount']) ?></td>
                            <td><?= $po['completed_milestones'] ?>/<?= $po['total_milestones'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-<?= $pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <small class="ms-2"><?= $pct ?>%</small>
                                </div>
                            </td>
                            <td><?= $po['approved_reports'] ?>/<?= $po['report_count'] ?></td>
                            <td><?= getStatusBadge($po['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($tab === 'timeline'): ?>
            <h6 class="fw-bold mb-3">Milestone Timeline Analysis</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Milestone</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Completed</th>
                            <th>Analysis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $onTrack = 0; $overdue = 0; $completed = 0;
                        foreach ($timelineAnalysis as $t): 
                            if ($t['status'] === 'completed') $completed++;
                            elseif ($t['status'] === 'overdue' || ($t['status'] !== 'completed' && $t['due_date'] && strtotime($t['due_date']) < time())) $overdue++;
                            else $onTrack++;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(truncateText($t['grant_title'], 25)) ?></td>
                            <td><?= htmlspecialchars($t['title']) ?></td>
                            <td><?= $t['due_date'] ? formatDate($t['due_date']) : '-' ?></td>
                            <td><?= getStatusBadge($t['status']) ?></td>
                            <td><?= $t['completion_date'] ? formatDate($t['completion_date']) : '-' ?></td>
                            <td>
                                <?php if ($t['status'] === 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                                <?php elseif ($t['status'] === 'overdue' || ($t['status'] !== 'completed' && $t['due_date'] && strtotime($t['due_date']) < time())): ?>
                                <span class="badge bg-danger">Overdue</span>
                                <?php else: ?>
                                <span class="badge bg-info">On Track</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="card bg-success bg-opacity-10 text-center p-3">
                        <h5 class="mb-0 text-success"><?= $completed ?></h5>
                        <small>Completed</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info bg-opacity-10 text-center p-3">
                        <h5 class="mb-0 text-info"><?= $onTrack ?></h5>
                        <small>On Track</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger bg-opacity-10 text-center p-3">
                        <h5 class="mb-0 text-danger"><?= $overdue ?></h5>
                        <small>Overdue / At Risk</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function printReport() {
    var printContent = document.getElementById('reportContent').innerHTML;
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title><?= SITE_NAME ?> - Monitoring Reports</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>body { padding: 30px; } .no-print { display: none; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h3 class="mb-4">Monitoring Reports - <?= SITE_NAME ?></h3>');
    printWindow.document.write('<p class="text-muted">Generated: <?= date('M d, Y h:i A') ?></p>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
