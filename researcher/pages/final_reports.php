<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();
$message = '';
$messageType = '';

$stmt = $db->prepare("SELECT a.*, g.title as grant_title FROM grant_awards a JOIN grants g ON a.grant_id = g.id WHERE a.researcher_id = :researcher_id AND a.status IN ('active', 'completed') ORDER BY g.title");
$stmt->execute([':researcher_id' => $userId]);
$awards = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    requireCSRF();
    $awardId = (int)($_POST['award_id'] ?? 0);
    $title = sanitizeInput($_POST['title'] ?? '');
    $executiveSummary = sanitizeInput($_POST['executive_summary'] ?? '');
    $introduction = sanitizeInput($_POST['introduction'] ?? '');
    $methodology = sanitizeInput($_POST['methodology'] ?? '');
    $results = sanitizeInput($_POST['results'] ?? '');
    $discussion = sanitizeInput($_POST['discussion'] ?? '');
    $conclusion = sanitizeInput($_POST['conclusion'] ?? '');
    $recommendations = sanitizeInput($_POST['recommendations'] ?? '');
    $references = sanitizeInput($_POST['references'] ?? '');
    $financialReport = sanitizeInput($_POST['financial_report'] ?? '');
    $action = $_POST['action'] ?? 'draft';

    if (empty($awardId) || empty($title) || empty($executiveSummary)) {
        $message = 'Award, title, and executive summary are required.';
        $messageType = 'danger';
    } else {
        $status = ($action === 'submit') ? 'submitted' : 'draft';
        $submittedAt = ($status === 'submitted') ? date('Y-m-d H:i:s') : null;
        $filePath = null;

        if (!empty($_FILES['report_file']['name'])) {
            $upload = uploadFile($_FILES['report_file'], REPORT_PATH);
            if ($upload['success']) {
                $filePath = $upload['file_name'];
            } else {
                $message = $upload['message'];
                $messageType = 'danger';
            }
        }

        if (empty($message)) {
            $stmt = $db->prepare("INSERT INTO final_reports (award_id, researcher_id, title, executive_summary, introduction, methodology, results, discussion, conclusion, recommendations, references, financial_report, file_path, status, submitted_at, created_at) VALUES (:award_id, :researcher_id, :title, :executive_summary, :introduction, :methodology, :results, :discussion, :conclusion, :recommendations, :references, :financial_report, :file_path, :status, :submitted_at, NOW())");
            $stmt->execute([
                ':award_id' => $awardId,
                ':researcher_id' => $userId,
                ':title' => $title,
                ':executive_summary' => $executiveSummary,
                ':introduction' => $introduction,
                ':methodology' => $methodology,
                ':results' => $results,
                ':discussion' => $discussion,
                ':conclusion' => $conclusion,
                ':recommendations' => $recommendations,
                ':references' => $references,
                ':financial_report' => $financialReport,
                ':file_path' => $filePath,
                ':status' => $status,
                ':submitted_at' => $submittedAt,
            ]);

            logAudit($userId, 'final_report_' . $status, 'final_reports', $db->lastInsertId(), 'Final report ' . $status);

            $message = $status === 'submitted' ? 'Final report submitted successfully.' : 'Final report saved as draft.';
            $messageType = 'success';
        }
    }
}

$stmt = $db->prepare("SELECT fr.*, g.title as grant_title FROM final_reports fr JOIN grant_awards a ON fr.award_id = a.id JOIN grants g ON a.grant_id = g.id WHERE fr.researcher_id = :researcher_id ORDER BY fr.created_at DESC");
$stmt->execute([':researcher_id' => $userId]);
$reports = $stmt->fetchAll();

$pageTitle = 'Final Reports';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Final Reports</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Final Reports</li>
            </ol>
        </nav>
    </div>
    <?php if (!empty($awards)): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
        <i class="bi bi-plus-circle me-1"></i>Submit Final Report
    </button>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="reportsTable">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Grant</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No final reports yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars(truncateText($report['title'], 50)) ?></td>
                        <td><?= htmlspecialchars(truncateText($report['grant_title'], 30)) ?></td>
                        <td><?= getStatusBadge($report['status']) ?></td>
                        <td><?= $report['submitted_at'] ? formatDate($report['submitted_at']) : '-' ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $report['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if ($report['file_path']): ?>
                            <a href="<?= SITE_URL ?>uploads/reports/<?= urlencode($report['file_path']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($awards)): ?>
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Submit Final Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Award <span class="text-danger">*</span></label>
                        <select name="award_id" class="form-select" required>
                            <option value="">Select Award</option>
                            <?php foreach ($awards as $award): ?>
                            <option value="<?= $award['id'] ?>"><?= htmlspecialchars($award['grant_title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Report Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Executive Summary <span class="text-danger">*</span></label>
                        <textarea name="executive_summary" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Introduction</label>
                        <textarea name="introduction" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Methodology</label>
                        <textarea name="methodology" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Results</label>
                        <textarea name="results" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Discussion</label>
                        <textarea name="discussion" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Conclusion</label>
                        <textarea name="conclusion" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Recommendations</label>
                        <textarea name="recommendations" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">References</label>
                        <textarea name="references" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Financial Report</label>
                        <textarea name="financial_report" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Attach File</label>
                        <input type="file" name="report_file" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_report" value="1" class="btn btn-secondary">
                            <i class="bi bi-save me-1"></i>Save as Draft
                        </button>
                        <button type="submit" name="submit_report" value="1" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Submit Report
                        </button>
                    </div>
                    <input type="hidden" name="action" id="reportAction" value="draft">
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php foreach ($reports as $report): ?>
<div class="modal fade" id="viewModal<?= $report['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><?= htmlspecialchars($report['title']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Grant</small>
                        <strong><?= htmlspecialchars($report['grant_title']) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <?= getStatusBadge($report['status']) ?>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Submitted</small>
                        <strong><?= $report['submitted_at'] ? formatDate($report['submitted_at']) : '-' ?></strong>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold">Executive Summary</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['executive_summary'])) ?></p>
                <?php if ($report['introduction']): ?>
                <h6 class="fw-bold mt-3">Introduction</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['introduction'])) ?></p>
                <?php endif; ?>
                <?php if ($report['methodology']): ?>
                <h6 class="fw-bold mt-3">Methodology</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['methodology'])) ?></p>
                <?php endif; ?>
                <?php if ($report['results']): ?>
                <h6 class="fw-bold mt-3">Results</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['results'])) ?></p>
                <?php endif; ?>
                <?php if ($report['discussion']): ?>
                <h6 class="fw-bold mt-3">Discussion</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['discussion'])) ?></p>
                <?php endif; ?>
                <?php if ($report['conclusion']): ?>
                <h6 class="fw-bold mt-3">Conclusion</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['conclusion'])) ?></p>
                <?php endif; ?>
                <?php if ($report['recommendations']): ?>
                <h6 class="fw-bold mt-3">Recommendations</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['recommendations'])) ?></p>
                <?php endif; ?>
                <?php if ($report['references']): ?>
                <h6 class="fw-bold mt-3">References</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['references'])) ?></p>
                <?php endif; ?>
                <?php if ($report['financial_report']): ?>
                <h6 class="fw-bold mt-3">Financial Report</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($report['financial_report'])) ?></p>
                <?php endif; ?>
                <?php if ($report['file_path']): ?>
                <hr>
                <a href="<?= SITE_URL ?>uploads/reports/<?= urlencode($report['file_path']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-download me-1"></i>Download File
                </a>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <small class="text-muted">Created: <?= formatDateTime($report['created_at']) ?></small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
(function initDT() {
    if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') { setTimeout(initDT, 50); return; }
    jQuery(function() {
            var $t = jQuery('#reportsTable');
            var emptyMsg = 'No reports found';
            $t.find('tbody tr').each(function() {
                var $td = jQuery(this).find('td');
                if ($td.length === 1 && $td.attr('colspan')) { emptyMsg = $td.text().trim(); jQuery(this).remove(); }
            });
            $t.DataTable({
                responsive: true, order: [[3, 'desc']], pageLength: 25,
                language: { search: "", searchPlaceholder: "Search reports...", infoEmpty: emptyMsg, zeroRecords: emptyMsg }
            });
    });
})();

document.querySelectorAll('[name="submit_report"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const action = this.classList.contains('btn-primary') ? 'submit' : 'draft';
        document.getElementById('reportAction').value = action;
    });
});
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
