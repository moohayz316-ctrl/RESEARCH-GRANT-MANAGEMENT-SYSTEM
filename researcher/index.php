<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();

$stmt = $db->prepare("SELECT COUNT(*) as count FROM proposals WHERE researcher_id = :researcher_id");
$stmt->execute([':researcher_id' => $userId]);
$myProposals = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM grant_awards WHERE researcher_id = :researcher_id AND status = 'active'");
$stmt->execute([':researcher_id' => $userId]);
$activeAwards = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM proposals WHERE researcher_id = :researcher_id AND status = 'under_review'");
$stmt->execute([':researcher_id' => $userId]);
$pendingReviews = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM progress_reports WHERE researcher_id = :researcher_id");
$stmt->execute([':researcher_id' => $userId]);
$progressReports = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT p.*, g.title as grant_title, g.funding_amount FROM proposals p JOIN grants g ON p.grant_id = g.id WHERE p.researcher_id = :researcher_id ORDER BY p.created_at DESC LIMIT 5");
$stmt->execute([':researcher_id' => $userId]);
$recentProposals = $stmt->fetchAll();

$stmt = $db->prepare("SELECT a.*, g.title as grant_title, g.funding_amount FROM grant_awards a JOIN grants g ON a.grant_id = g.id WHERE a.researcher_id = :researcher_id AND a.status = 'active' ORDER BY a.created_at DESC");
$stmt->execute([':researcher_id' => $userId]);
$myAwards = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':user_id' => $userId]);
$notifications = $stmt->fetchAll();

$stmt = $db->prepare("SELECT p.status, COUNT(*) as count FROM proposals p WHERE p.researcher_id = :researcher_id GROUP BY p.status");
$stmt->execute([':researcher_id' => $userId]);
$statusData = $stmt->fetchAll();
$statusLabels = json_encode(array_column($statusData, 'status'));
$statusCounts = json_encode(array_column($statusData, 'count'));

$stmt = $db->prepare("SELECT DATE_FORMAT(p.created_at, '%Y-%m') as month, COUNT(*) as count FROM proposals p WHERE p.researcher_id = :researcher_id AND p.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");
$stmt->execute([':researcher_id' => $userId]);
$monthlyData = $stmt->fetchAll();
$monthlyLabels = json_encode(array_column($monthlyData, 'month'));
$monthlyCounts = json_encode(array_column($monthlyData, 'count'));

$pageTitle = 'Researcher Dashboard';
include_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Researcher Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <a href="<?= SITE_URL ?>researcher/pages/grants.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Apply for Grant
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $myProposals ?></h5>
                        <small class="text-muted">My Proposals</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                        <i class="bi bi-trophy fs-3 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $activeAwards ?></h5>
                        <small class="text-muted">Active Awards</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                        <i class="bi bi-clock-history fs-3 text-warning"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $pendingReviews ?></h5>
                        <small class="text-muted">Pending Reviews</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-graph-up fs-3 text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $progressReports ?></h5>
                        <small class="text-muted">Progress Reports</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Recent Proposals</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Grant</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentProposals)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No proposals yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentProposals as $proposal): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars(truncateText($proposal['title'], 40)) ?></td>
                                <td><?= htmlspecialchars(truncateText($proposal['grant_title'], 30)) ?></td>
                                <td><?= getStatusBadge($proposal['status']) ?></td>
                                <td><?= formatDate($proposal['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Recent Notifications</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                <p class="text-muted text-center py-4 mb-0">No notifications</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notifications as $notif): ?>
                    <li class="list-group-item py-2 px-3">
                        <a href="<?= $notif['link'] ?: '#' ?>" class="text-decoration-none text-dark">
                            <small class="fw-medium d-block"><?= htmlspecialchars($notif['title']) ?></small>
                            <small class="text-muted"><?= truncateText($notif['message'] ?? '', 50) ?></small>
                            <small class="text-muted d-block mt-1"><?= timeAgo($notif['created_at']) ?></small>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Proposals by Status</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Monthly Applications</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($myAwards)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">My Active Awards</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Grant Title</th>
                        <th>Amount</th>
                        <th>Award Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myAwards as $award): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars($award['grant_title']) ?></td>
                        <td><?= formatAmount($award['awarded_amount']) ?></td>
                        <td><?= formatDate($award['award_date']) ?></td>
                        <td><?= formatDate($award['end_date']) ?></td>
                        <td><?= getStatusBadge($award['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= $statusLabels ?>,
            datasets: [{
                data: <?= $statusCounts ?>,
                backgroundColor: ['#537497', '#69819c', '#c4936f', '#65a390', '#c15757', '#96b2c6']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true }
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?= $monthlyLabels ?>,
            datasets: [{
                label: 'Applications',
                data: <?= $monthlyCounts ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: '#537497',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
