<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();

$stmt = $db->prepare("SELECT a.*, g.title as grant_title, g.funding_amount, p.title as proposal_title FROM grant_awards a JOIN grants g ON a.grant_id = g.id JOIN proposals p ON a.proposal_id = p.id WHERE a.researcher_id = :researcher_id ORDER BY a.created_at DESC");
$stmt->execute([':researcher_id' => $userId]);
$awards = $stmt->fetchAll();

$pageTitle = 'My Awards';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Awards</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Awards</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="awardsTable">
                <thead class="table-light">
                    <tr>
                        <th>Grant Title</th>
                        <th>Amount</th>
                        <th>Award Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($awards)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No awards yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($awards as $award): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars($award['grant_title']) ?></td>
                        <td><?= formatAmount($award['awarded_amount']) ?></td>
                        <td><?= formatDate($award['award_date']) ?></td>
                        <td><?= formatDate($award['end_date']) ?></td>
                        <td><?= getStatusBadge($award['status']) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $award['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if ($award['approval_letter']): ?>
                            <a href="<?= SITE_URL ?>uploads/<?= urlencode($award['approval_letter']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="bi bi-file-earmark-text"></i>
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

<?php foreach ($awards as $award): ?>
<div class="modal fade" id="viewModal<?= $award['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Award Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Grant Title</small>
                        <strong><?= htmlspecialchars($award['grant_title']) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Amount</small>
                        <strong><?= formatAmount($award['awarded_amount']) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <?= getStatusBadge($award['status']) ?>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Award Date</small>
                        <strong><?= formatDate($award['award_date']) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">End Date</small>
                        <strong><?= formatDate($award['end_date']) ?></strong>
                    </div>
                    <?php if ($award['proposal_title']): ?>
                    <div class="col-12">
                        <small class="text-muted d-block">Proposal</small>
                        <strong><?= htmlspecialchars($award['proposal_title']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($award['approval_letter']): ?>
                    <div class="col-12">
                        <small class="text-muted d-block">Approval Letter</small>
                        <a href="<?= SITE_URL ?>uploads/<?= urlencode($award['approval_letter']) ?>" class="btn btn-sm btn-outline-success mt-1" target="_blank">
                            <i class="bi bi-file-earmark-text me-1"></i>Download Letter
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted">Awarded: <?= formatDateTime($award['created_at']) ?></small>
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
            var $t = jQuery('#awardsTable');
            var emptyMsg = 'No awards found';
            $t.find('tbody tr').each(function() {
                var $td = jQuery(this).find('td');
                if ($td.length === 1 && $td.attr('colspan')) { emptyMsg = $td.text().trim(); jQuery(this).remove(); }
            });
            $t.DataTable({
                responsive: true, order: [[2, 'desc']], pageLength: 25,
                language: { search: "", searchPlaceholder: "Search awards...", infoEmpty: emptyMsg, zeroRecords: emptyMsg }
            });
    });
})();
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
