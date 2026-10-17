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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireCSRF();
    $proposalId = (int)($_POST['proposal_id'] ?? 0);
    $action = $_POST['action'];

    $stmt = $db->prepare("SELECT * FROM proposals WHERE id = :id AND researcher_id = :researcher_id LIMIT 1");
    $stmt->execute([':id' => $proposalId, ':researcher_id' => $userId]);
    $proposal = $stmt->fetch();

    if (!$proposal) {
        $message = 'Proposal not found.';
        $messageType = 'danger';
    } elseif ($action === 'withdraw' && $proposal['status'] === 'submitted') {
        $stmt = $db->prepare("UPDATE proposals SET status = 'withdrawn' WHERE id = :id");
        $stmt->execute([':id' => $proposalId]);
        logAudit($userId, 'proposal_withdrawn', 'proposals', $proposalId, 'Proposal withdrawn');
        $message = 'Proposal withdrawn successfully.';
        $messageType = 'success';
    } elseif ($action === 'delete' && in_array($proposal['status'], ['draft', 'withdrawn'])) {
        $stmt = $db->prepare("DELETE FROM proposal_documents WHERE proposal_id = :proposal_id");
        $stmt->execute([':proposal_id' => $proposalId]);
        $stmt = $db->prepare("DELETE FROM proposals WHERE id = :id");
        $stmt->execute([':id' => $proposalId]);
        logAudit($userId, 'proposal_deleted', 'proposals', $proposalId, 'Proposal deleted');
        $message = 'Proposal deleted successfully.';
        $messageType = 'success';
    }
}

$stmt = $db->prepare("SELECT p.*, g.title as grant_title, g.funding_amount FROM proposals p JOIN grants g ON p.grant_id = g.id WHERE p.researcher_id = :researcher_id ORDER BY p.created_at DESC");
$stmt->execute([':researcher_id' => $userId]);
$proposals = $stmt->fetchAll();

$pageTitle = 'My Proposals';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Proposals</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Proposals</li>
            </ol>
        </nav>
    </div>
    <a href="<?= SITE_URL ?>researcher/pages/grants.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>New Application
    </a>
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
            <table class="table table-hover" id="proposalsTable">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Grant</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                        <th>Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proposals)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No proposals found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($proposals as $proposal): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars(truncateText($proposal['title'], 50)) ?></td>
                        <td><?= htmlspecialchars(truncateText($proposal['grant_title'], 35)) ?></td>
                        <td><?= getStatusBadge($proposal['status']) ?></td>
                        <td><?= $proposal['submitted_at'] ? formatDate($proposal['submitted_at']) : '-' ?></td>
                        <td><?= $proposal['review_score'] ?? '-' ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $proposal['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if ($proposal['status'] === 'draft'): ?>
                            <a href="<?= SITE_URL ?>researcher/pages/apply.php?id=<?= $proposal['grant_id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($proposal['status'] === 'submitted'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Withdraw this proposal?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
                                <input type="hidden" name="action" value="withdraw">
                                <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-x-circle"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if (in_array($proposal['status'], ['draft', 'withdrawn'])): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this proposal permanently?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
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

<?php foreach ($proposals as $proposal): ?>
<div class="modal fade" id="viewModal<?= $proposal['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><?= htmlspecialchars($proposal['title']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Grant</small>
                        <strong><?= htmlspecialchars($proposal['grant_title']) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <?= getStatusBadge($proposal['status']) ?>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Score</small>
                        <strong><?= $proposal['review_score'] ?? 'Not reviewed' ?></strong>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold">Abstract</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($proposal['abstract'])) ?></p>

                <?php if ($proposal['problem_statement']): ?>
                <h6 class="fw-bold mt-3">Problem Statement</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($proposal['problem_statement'])) ?></p>
                <?php endif; ?>

                <?php if ($proposal['methodology']): ?>
                <h6 class="fw-bold mt-3">Methodology</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($proposal['methodology'])) ?></p>
                <?php endif; ?>

                <?php if ($proposal['expected_outcomes']): ?>
                <h6 class="fw-bold mt-3">Expected Outcomes</h6>
                <p class="text-muted"><?= nl2br(htmlspecialchars($proposal['expected_outcomes'])) ?></p>
                <?php endif; ?>

                <?php if ($proposal['budget_amount']): ?>
                <h6 class="fw-bold mt-3">Budget</h6>
                <p class="text-muted">Amount: <?= formatAmount($proposal['budget_amount']) ?></p>
                <?php if ($proposal['budget_breakdown']): ?>
                <p class="text-muted"><?= nl2br(htmlspecialchars($proposal['budget_breakdown'])) ?></p>
                <?php endif; ?>
                <?php endif; ?>

                <?php
                $stmt = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :proposal_id");
                $stmt->execute([':proposal_id' => $proposal['id']]);
                $docs = $stmt->fetchAll();
                ?>
                <?php if (!empty($docs)): ?>
                <h6 class="fw-bold mt-3">Documents</h6>
                <ul class="list-group">
                    <?php foreach ($docs as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi <?= getFileIcon($doc['file_name']) ?> me-2"></i><?= htmlspecialchars($doc['file_name']) ?></span>
                        <a href="<?= SITE_URL ?>uploads/proposals/<?= urlencode($doc['file_name']) ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-download"></i></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <small class="text-muted">Created: <?= formatDateTime($proposal['created_at']) ?></small>
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
            var $t = jQuery('#proposalsTable');
            var emptyMsg = 'No proposals found';
            $t.find('tbody tr').each(function() {
                var $td = jQuery(this).find('td');
                if ($td.length === 1 && $td.attr('colspan')) { emptyMsg = $td.text().trim(); jQuery(this).remove(); }
            });
            $t.DataTable({
                responsive: true, order: [[3, 'desc']], pageLength: 25,
                language: { search: "", searchPlaceholder: "Search proposals...", infoEmpty: emptyMsg, zeroRecords: emptyMsg }
            });
    });
})();
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
