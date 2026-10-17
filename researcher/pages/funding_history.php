<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();

$stmt = $db->prepare("
    SELECT a.*,
        g.title as grant_title, g.funding_amount as grant_amount,
        p.title as proposal_title,
        COALESCE(a.approved_amount, a.awarded_amount) as effective_approved
    FROM grant_awards a
    JOIN grants g ON a.grant_id = g.id
    JOIN proposals p ON a.proposal_id = p.id
    WHERE a.researcher_id = :researcher_id
    ORDER BY a.award_date DESC
");
$stmt->execute([':researcher_id' => $userId]);
$awards = $stmt->fetchAll();

// Fetch disbursements for all award IDs
$awardIds = array_column($awards, 'id');
$disbsByAward = [];
if (!empty($awardIds)) {
    $placeholders = implode(',', array_fill(0, count($awardIds), '?'));
    $stmtD = $db->prepare("SELECT fd.*, u.first_name, u.last_name FROM fund_disbursements fd LEFT JOIN users u ON fd.disbursed_by = u.id WHERE fd.award_id IN ($placeholders) ORDER BY fd.disbursement_date ASC");
    $stmtD->execute($awardIds);
    foreach ($stmtD->fetchAll() as $d) {
        $disbsByAward[$d['award_id']][] = $d;
    }
}

$totalApproved  = 0;
$totalDisbursed = 0;
foreach ($awards as $award) {
    if (in_array($award['status'], ['active', 'completed'])) {
        $totalApproved  += (float)$award['effective_approved'];
        $totalDisbursed += (float)$award['total_disbursed'];
    }
}

$pageTitle = 'Funding History';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Funding History</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Funding History</li>
            </ol>
        </nav>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= formatAmount($totalApproved) ?></h5>
                        <small class="text-muted">Total Approved Funding</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-send fs-3 text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= formatAmount($totalDisbursed) ?></h5>
                        <small class="text-muted">Total Disbursed to Date</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-trophy fs-3 text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= count($awards) ?></h5>
                        <small class="text-muted">Total Awards</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Institutional Notice -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Note:</strong> Fund disbursements shown here are recorded by the institution. Actual payment is transferred through the institution's authorised financial channels. Contact the grants office for queries.
</div>

<?php if (empty($awards)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-cash-stack fs-1 text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No Funding History Available</h5>
        <p class="text-muted">You have not received any grant awards yet.</p>
        <a href="<?= SITE_URL ?>researcher/pages/grants.php" class="btn btn-primary">Browse Grants</a>
    </div>
</div>
<?php else: ?>

<?php foreach ($awards as $award):
    $approved  = (float)$award['effective_approved'];
    $disbursed = (float)$award['total_disbursed'];
    $remaining = max(0, $approved - $disbursed);
    $pct       = $approved > 0 ? round(($disbursed / $approved) * 100) : 0;
    $barClass  = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-primary');
    $disbList  = $disbsByAward[$award['id']] ?? [];
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($award['proposal_title']) ?></h6>
            <small class="text-muted"><i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($award['grant_title']) ?></small>
        </div>
        <div><?= getStatusBadge($award['status']) ?></div>
    </div>
    <div class="card-body">
        <!-- Fund Summary Row -->
        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-md-3">
                <div class="bg-light rounded p-3 text-center">
                    <small class="text-muted d-block">Awarded Amount</small>
                    <strong class="text-secondary"><?= formatAmount($award['awarded_amount']) ?></strong>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="bg-light rounded p-3 text-center">
                    <small class="text-muted d-block">Approved Fund</small>
                    <strong class="text-success"><?= $approved > 0 ? formatAmount($approved) : '<span class="text-muted">Pending</span>' ?></strong>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="bg-light rounded p-3 text-center">
                    <small class="text-muted d-block">Disbursed</small>
                    <strong class="text-info"><?= formatAmount($disbursed) ?></strong>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="bg-light rounded p-3 text-center">
                    <small class="text-muted d-block">Remaining</small>
                    <strong class="<?= $remaining > 0 ? 'text-warning' : 'text-success' ?>"><?= formatAmount($remaining) ?></strong>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <?php if ($approved > 0): ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Disbursement Progress</small>
                <small class="fw-bold"><?= $pct ?>%</small>
            </div>
            <div class="progress" style="height:8px;">
                <div class="progress-bar <?= $barClass ?>" style="width:<?= $pct ?>%;"></div>
            </div>
            <?php if ($pct >= 100): ?>
            <small class="text-success mt-1 d-block"><i class="bi bi-check-circle me-1"></i>Fully Disbursed</small>
            <?php elseif ($disbursed > 0): ?>
            <small class="text-muted mt-1 d-block"><?= formatAmount($disbursed) ?> disbursed of <?= formatAmount($approved) ?></small>
            <?php else: ?>
            <small class="text-muted mt-1 d-block">No disbursements recorded yet</small>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Award Period -->
        <div class="d-flex gap-3 mb-3">
            <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>Award Date: <?= formatDate($award['award_date']) ?></small>
            <?php if ($award['end_date']): ?>
            <small class="text-muted"><i class="bi bi-calendar-x me-1"></i>End Date: <?= formatDate($award['end_date']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Disbursement History -->
        <?php if (!empty($disbList)): ?>
        <div>
            <h6 class="fw-bold mb-2"><i class="bi bi-clock-history text-primary me-2"></i>Disbursement History</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Stage</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payment Reference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($disbList as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($d['stage']) ?></span></td>
                            <td class="fw-bold text-success"><?= formatAmount($d['disbursed_amount']) ?></td>
                            <td><?= formatDate($d['disbursement_date']) ?></td>
                            <td>
                                <?php if ($d['payment_reference']): ?>
                                <code><?= htmlspecialchars($d['payment_reference']) ?></code>
                                <?php else: ?>
                                <span class="text-muted">â€”</span>
                                <?php endif; ?>
                            </td>
                            <td><?= getStatusBadge($d['status']) ?></td>
                        </tr>
                        <?php if ($d['notes']): ?>
                        <tr>
                            <td colspan="6" class="text-muted small ps-4">
                                <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars($d['notes']) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Total Disbursed</th>
                            <th class="text-success"><?= formatAmount($disbursed) ?></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning border-0 py-2 small mb-0">
            <i class="bi bi-hourglass me-1"></i>No disbursements have been recorded for this award yet. Contact the grants office if you expect a disbursement.
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

