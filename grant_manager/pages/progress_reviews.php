<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
if (!in_array($_SESSION['user_role'], ['admin', 'grant_manager'])) {
    requireRole('admin');
}

$db = getDb();
$pageTitle = 'Progress Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_report'])) {
    requireCSRF();
    $reportId = (int)$_POST['report_id'];
    $action = sanitizeInput($_POST['review_action']);
    $comment = sanitizeInput($_POST['review_comment']);

    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE progress_reports SET status = 'approved', review_comment = :comment, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id");
    } else {
        $stmt = $db->prepare("UPDATE progress_reports SET status = 'rejected', review_comment = :comment, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id");
    }
    $stmt->execute([
        ':comment' => $comment,
        ':reviewed_by' => $_SESSION['user_id'],
        ':id' => $reportId,
    ]);

    $stmt2 = $db->prepare("SELECT pr.*, ga.researcher_id FROM progress_reports pr JOIN grant_awards ga ON pr.award_id = ga.id WHERE pr.id = :id");
    $stmt2->execute([':id' => $reportId]);
    $report = $stmt2->fetch();

    logAudit($_SESSION['user_id'], 'review', 'progress_reports', $reportId, 'Progress report ' . $action . 'd');
    createNotification($report['researcher_id'], 'progress_report', 'Progress Report ' . ucfirst($action) . 'd', 'Your progress report has been ' . $action . 'd.' . ($comment ? ' Comment: ' . $comment : ''), SITE_URL . 'researcher/pages/progress_reports.php');

    $_SESSION['success_message'] = 'Progress report ' . $action . 'd successfully.';
    header('Location: ' . SITE_URL . 'grant_manager/pages/progress_reviews.php');
    exit();
}

$filterStatus = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$sql = "SELECT pr.*, u.first_name, u.last_name, g.title as grant_title, ga.id as award_id FROM progress_reports pr JOIN grant_awards ga ON pr.award_id = ga.id JOIN users u ON pr.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id";
$params = [];
if ($filterStatus) {
    $sql .= " WHERE pr.status = :status";
    $params[':status'] = $filterStatus;
}
$sql .= " ORDER BY pr.submitted_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Progress Reviews</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Progress Reviews</li>
            </ol>
        </nav>
    </div>
</div>

<?= displaySessionMessages() ?>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Progress Reports</h6>
            <div class="d-flex gap-2">
                <a href="?status=submitted" class="btn btn-sm <?= $filterStatus === 'submitted' ? 'btn-primary' : 'btn-outline-primary' ?>">Submitted</a>
                <a href="?status=approved" class="btn btn-sm <?= $filterStatus === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
                <a href="?status=rejected" class="btn btn-sm <?= $filterStatus === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
                <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Researcher</th>
                        <th>Award</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No reports found</td></tr>
                    <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars(truncateText($r['title'], 40)) ?></td>
                        <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td><?= htmlspecialchars(truncateText($r['grant_title'], 25)) ?></td>
                        <td><?= getStatusBadge($r['report_type']) ?></td>
                        <td><?= $r['report_period_start'] ? formatDate($r['report_period_start']) : '-' ?> to <?= $r['report_period_end'] ? formatDate($r['report_period_end']) : '-' ?></td>
                        <td><?= $r['submitted_at'] ? formatDateTime($r['submitted_at']) : '-' ?></td>
                        <td><?= getStatusBadge($r['status']) ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary btn-icon" onclick="viewReport(<?= $r['id'] ?>)" title="Review"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="reviewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Progress Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="review_report" value="1">
                <input type="hidden" name="report_id" id="review_report_id">
                <div class="modal-body" id="reportDetailContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="review_action" value="reject" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Reject</button>
                    <button type="submit" name="review_action" value="approve" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var reportsData = <?= json_encode($reports) ?>;

function viewReport(id) {
    var r = reportsData.find(function(x) { return x.id == id; });
    if (r) {
        var html = '<div class="row g-3 mb-3">' +
            '<div class="col-md-6"><strong>Title:</strong><br>' + htmlspecialchars(r.title) + '</div>' +
            '<div class="col-md-6"><strong>Researcher:</strong><br>' + htmlspecialchars(r.first_name + ' ' + r.last_name) + '</div>' +
            '<div class="col-md-6"><strong>Report Type:</strong><br>' + htmlspecialchars(r.report_type) + '</div>' +
            '<div class="col-md-6"><strong>Period:</strong><br>' + (r.report_period_start || '-') + ' to ' + (r.report_period_end || '-') + '</div>' +
            '<div class="col-12"><hr></div>' +
            '<div class="col-12"><strong>Summary:</strong><br>' + htmlspecialchars(r.summary || 'N/A') + '</div>' +
            '<div class="col-12"><strong>Achievements:</strong><br>' + htmlspecialchars(r.achievements || 'N/A') + '</div>' +
            '<div class="col-12"><strong>Challenges:</strong><br>' + htmlspecialchars(r.challenges || 'N/A') + '</div>' +
            '<div class="col-12"><strong>Next Steps:</strong><br>' + htmlspecialchars(r.next_steps || 'N/A') + '</div>' +
            '<div class="col-12"><strong>Financial Update:</strong><br>' + htmlspecialchars(r.financial_update || 'N/A') + '</div>';
        if (r.file_path) {
            html += '<div class="col-12"><a href="<?= SITE_URL ?>uploads/reports/' + encodeURIComponent(r.file_path) + '" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-file-earmark me-1"></i>View Attachment</a></div>';
        }
        html += '</div><div class="mb-3"><label class="form-label fw-medium">Review Comment</label><textarea name="review_comment" class="form-control" rows="3" placeholder="Enter your review comment..."></textarea></div>';
        $('#review_report_id').val(r.id);
        $('#reportDetailContent').html(html);
        $('#reviewReportModal').modal('show');
    }
}

function htmlspecialchars(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
