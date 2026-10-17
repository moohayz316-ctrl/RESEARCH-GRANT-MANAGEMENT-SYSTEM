<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_reviewer'])) {
    requireCSRF();
    $proposal_id = (int)$_POST['proposal_id'];
    $reviewer_id = (int)$_POST['reviewer_id'];

    $stmt = $db->prepare("SELECT id FROM proposal_reviews WHERE proposal_id = :proposal_id AND reviewer_id = :reviewer_id LIMIT 1");
    $stmt->execute([':proposal_id' => $proposal_id, ':reviewer_id' => $reviewer_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = 'This reviewer is already assigned to this proposal.';
        header('Location: ' . SITE_URL . 'admin/pages/reviews.php');
        exit();
    }

    $stmt = $db->prepare("INSERT INTO proposal_reviews (proposal_id, reviewer_id, assigned_by, status) VALUES (:proposal_id, :reviewer_id, :assigned_by, 'assigned')");
    $stmt->execute([':proposal_id' => $proposal_id, ':reviewer_id' => $reviewer_id, ':assigned_by' => $_SESSION['user_id']]);
    
    // Automatically transition proposal status from submitted to under_review
    $stmtUp = $db->prepare("UPDATE proposals SET status = 'under_review' WHERE id = :proposal_id AND status = 'submitted'");
    $stmtUp->execute([':proposal_id' => $proposal_id]);

    // Fetch proposal title for notification
    $propStmt = $db->prepare("SELECT title FROM proposals WHERE id = :id");
    $propStmt->execute([':id' => $proposal_id]);
    $propInfo = $propStmt->fetch();
    $propTitle = $propInfo['title'] ?? 'Research Proposal';

    // Notify the assigned reviewer
    createNotification(
        $reviewer_id,
        'review_assignment',
        'New Proposal Assigned for Review',
        'You have been assigned to evaluate the proposal "' . truncateText($propTitle, 50) . '".',
        SITE_URL . 'reviewer/pages/assigned_proposals.php'
    );

    logAudit($_SESSION['user_id'], 'assign_reviewer', 'proposal_reviews', $db->lastInsertId(), 'Assigned reviewer to proposal #' . $proposal_id);
    $_SESSION['success_message'] = 'Reviewer assigned successfully. Proposal is now under review.';
    header('Location: ' . SITE_URL . 'admin/pages/reviews.php');
    exit();
}

$stmt = $db->query("SELECT pr.*, u.first_name as reviewer_first, u.last_name as reviewer_last, p.title as proposal_title, p.researcher_id, r.first_name as res_first, r.last_name as res_last FROM proposal_reviews pr JOIN users u ON pr.reviewer_id = u.id JOIN proposals p ON pr.proposal_id = p.id JOIN users r ON p.researcher_id = r.id ORDER BY pr.created_at DESC");
$reviews = $stmt->fetchAll();

$proposals = $db->query("SELECT p.id, p.title, u.first_name, u.last_name FROM proposals p JOIN users u ON p.researcher_id = u.id WHERE p.status = 'submitted' OR p.status = 'under_review' ORDER BY p.created_at DESC")->fetchAll();
$reviewers = $db->query("SELECT id, first_name, last_name, email FROM users WHERE role = 'reviewer' AND is_active = 1 ORDER BY first_name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Reviews</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Reviews</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>All Reviews</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignReviewerModal"><i class="bi bi-plus-lg me-1"></i>Assign Reviewer</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Proposal</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Recommendation</th>
                        <th>Assigned</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No reviews found</td></tr>
                    <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['reviewer_first'] . ' ' . $r['reviewer_last']) ?></td>
                        <td><?= htmlspecialchars($r['proposal_title']) ?></td>
                        <td><?= $r['score'] !== null ? number_format($r['score'], 1) : '-' ?></td>
                        <td><?= getStatusBadge($r['status']) ?></td>
                        <td><?= $r['recommendation'] ? getStatusBadge($r['recommendation']) : '-' ?></td>
                        <td><?= formatDate($r['assigned_at']) ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-info btn-icon" onclick="viewReview(<?= $r['id'] ?>)" title="View"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="assignReviewerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="assign_reviewer" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Reviewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Proposal <span class="text-danger">*</span></label>
                        <select name="proposal_id" class="form-select" required>
                            <option value="">Select Proposal</option>
                            <?php foreach ($proposals as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reviewer <span class="text-danger">*</span></label>
                        <select name="reviewer_id" class="form-select" required>
                            <option value="">Select Reviewer</option>
                            <?php foreach ($reviewers as $rev): ?>
                            <option value="<?= $rev['id'] ?>"><?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']) ?> (<?= htmlspecialchars($rev['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewReview(id) {
    $.get('<?= SITE_URL ?>admin/ajax/handle_users.php?action=get_review&id=' + id, function(data) {
        if (typeof data === 'string') data = JSON.parse(data);
        var html = '<div class="row g-3">';
        html += '<div class="col-md-6"><small class="text-muted">Reviewer:</small><p class="fw-medium">' + data.reviewer_name + '</p></div>';
        html += '<div class="col-md-6"><small class="text-muted">Proposal:</small><p class="fw-medium">' + data.proposal_title + '</p></div>';
        html += '<div class="col-md-4"><small class="text-muted">Score:</small><p>' + (data.score || '-') + '</p></div>';
        html += '<div class="col-md-4"><small class="text-muted">Status:</small><p>' + data.status_badge + '</p></div>';
        html += '<div class="col-md-4"><small class="text-muted">Recommendation:</small><p>' + (data.recommendation_badge || '-') + '</p></div>';
        if (data.comments) html += '<div class="col-12"><small class="text-muted">Comments:</small><p>' + data.comments + '</p></div>';
        if (data.evaluation_report) html += '<div class="col-12"><small class="text-muted">Evaluation Report:</small><p>' + data.evaluation_report + '</p></div>';
        html += '</div>';
        $('#reviewDetailBody').html(html);
        $('#viewReviewModal').modal('show');
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
