<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('reviewer');

$userId = $_SESSION['user_id'];
$db = getDb();
$reviewId = (int)($_GET['id'] ?? 0);

if (!$reviewId) {
    $_SESSION['danger_message'] = 'Invalid review ID.';
    header('Location: ' . SITE_URL . 'reviewer/pages/assigned_proposals.php');
    exit();
}

$stmt = $db->prepare("SELECT pr.*, p.id as proposal_id, p.researcher_id, p.title as proposal_title, p.abstract, p.problem_statement, p.methodology, p.expected_outcomes, p.budget_breakdown, p.status as proposal_status, p.budget_amount, p.submitted_at, p.review_score, g.title as grant_title, g.funding_amount, u.first_name as res_first, u.last_name as res_last, u.email as res_email, u.phone as res_phone, u.department as res_department, u.institution as res_institution, u.qualification as res_qualification, u.expertise_area as res_expertise, u.orcid_id as res_orcid, u.google_scholar as res_scholar, u.biography as res_bio, u.research_interests as res_interests, u.cv_file as res_cv, u.profile_picture as res_picture FROM proposal_reviews pr JOIN proposals p ON pr.proposal_id = p.id JOIN grants g ON p.grant_id = g.id JOIN users u ON p.researcher_id = u.id WHERE pr.id = :id AND pr.reviewer_id = :user_id LIMIT 1");
$stmt->execute([':id' => $reviewId, ':user_id' => $userId]);
$review = $stmt->fetch();

if (!$review) {
    $_SESSION['danger_message'] = 'Review not found or access denied.';
    header('Location: ' . SITE_URL . 'reviewer/pages/assigned_proposals.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :proposal_id ORDER BY uploaded_at DESC");
$stmt->execute([':proposal_id' => $review['proposal_id']]);
$documents = $stmt->fetchAll();

$isCompleted = $review['status'] === 'completed';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isCompleted) {
    requireCSRF();
    $score = (float)($_POST['score'] ?? 0);
    $comments = sanitizeInput($_POST['comments'] ?? '');
    $recommendation = sanitizeInput($_POST['recommendation'] ?? '');
    $evaluationReport = sanitizeInput($_POST['evaluation_report'] ?? '');
    $recommendedAmount = isset($_POST['recommended_amount']) && $_POST['recommended_amount'] !== '' ? (float)$_POST['recommended_amount'] : null;

    if ($score < 0 || $score > 100) {
        $message = 'Score must be between 0 and 100.';
        $messageType = 'danger';
    } elseif (empty($comments)) {
        $message = 'Comments are required.';
        $messageType = 'danger';
    } elseif (!in_array($recommendation, ['approve', 'reject', 'revision'])) {
        $message = 'Invalid recommendation.';
        $messageType = 'danger';
    } elseif ($recommendedAmount !== null && $recommendedAmount < 0) {
        $message = 'Recommended amount cannot be negative.';
        $messageType = 'danger';
    } else {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE proposal_reviews SET score = :score, comments = :comments, recommendation = :recommendation, recommended_amount = :recommended_amount, evaluation_report = :evaluation_report, status = 'completed', completed_at = NOW() WHERE id = :id AND reviewer_id = :user_id");
            $stmt->execute([
                ':score' => $score,
                ':comments' => $comments,
                ':recommendation' => $recommendation,
                ':recommended_amount' => $recommendedAmount,
                ':evaluation_report' => $evaluationReport,
                ':id' => $reviewId,
                ':user_id' => $userId,
            ]);

            $stmt = $db->prepare("UPDATE proposals SET review_score = :score, reviewed_by = :reviewed_by, reviewed_at = NOW(), status = CASE WHEN status = 'submitted' THEN 'under_review' ELSE status END WHERE id = :proposal_id");
            $stmt->execute([
                ':score' => $score,
                ':reviewed_by' => $userId,
                ':proposal_id' => $review['proposal_id'],
            ]);

            $db->commit();

            logAudit($userId, 'submit_review', 'proposal_reviews', $reviewId, 'Review submitted for proposal #' . $review['proposal_id']);

            createNotification($review['researcher_id'], 'review_submitted', 'Review Completed', 'Your proposal "' . $review['proposal_title'] . '" has been reviewed.', SITE_URL . 'researcher/pages/my_proposals.php');

            // Notify active administrators
            $adminUsers = $db->query("SELECT id FROM users WHERE role = 'admin' AND is_active = 1")->fetchAll();
            $revName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? 'Reviewer'));
            $recAmtStr = $recommendedAmount ? ' Recommended funding: ' . formatAmount($recommendedAmount) . '.' : '';
            foreach ($adminUsers as $adm) {
                createNotification(
                    $adm['id'],
                    'review_completed',
                    'Review Completed',
                    "Reviewer {$revName} completed evaluation for proposal '{$review['proposal_title']}' with score {$score}/100 ({$recommendation}).{$recAmtStr}",
                    SITE_URL . 'admin/pages/applications.php'
                );
            }

            $_SESSION['success_message'] = 'Review submitted successfully.';
            header('Location: ' . SITE_URL . 'reviewer/pages/review.php?id=' . $reviewId);
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $message = 'An error occurred while submitting the review.';
            $messageType = 'danger';
        }
    }
}

// Reload review data for display
$stmt = $db->prepare("SELECT pr.*, p.id as proposal_id, p.researcher_id, p.title as proposal_title, p.abstract, p.problem_statement, p.methodology, p.expected_outcomes, p.budget_breakdown, p.status as proposal_status, p.budget_amount, p.submitted_at, p.review_score, g.title as grant_title, g.funding_amount, u.first_name as res_first, u.last_name as res_last, u.email as res_email, u.phone as res_phone, u.department as res_department, u.institution as res_institution, u.qualification as res_qualification, u.expertise_area as res_expertise, u.orcid_id as res_orcid, u.google_scholar as res_scholar, u.biography as res_bio, u.research_interests as res_interests, u.cv_file as res_cv, u.profile_picture as res_picture FROM proposal_reviews pr JOIN proposals p ON pr.proposal_id = p.id JOIN grants g ON p.grant_id = g.id JOIN users u ON p.researcher_id = u.id WHERE pr.id = :id AND pr.reviewer_id = :user_id LIMIT 1");
$stmt->execute([':id' => $reviewId, ':user_id' => $userId]);
$review = $stmt->fetch();

// Researcher track record
$resStatsStmt = $db->prepare("SELECT COUNT(*) as total_proposals, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_proposals FROM proposals WHERE researcher_id = :rid AND status != 'draft'");
$resStatsStmt->execute([':rid' => $review['researcher_id']]);
$resStats = $resStatsStmt->fetch() ?: ['total_proposals' => 0, 'approved_proposals' => 0];

$resAwardsStmt = $db->prepare("SELECT COUNT(*) as total_awards, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_projects FROM grant_awards WHERE researcher_id = :rid");
$resAwardsStmt->execute([':rid' => $review['researcher_id']]);
$resAwards = $resAwardsStmt->fetch() ?: ['total_awards' => 0, 'completed_projects' => 0];

$pageTitle = 'Review Proposal';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Review Proposal</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>reviewer/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>reviewer/pages/assigned_proposals.php">Assigned Proposals</a></li>
                <li class="breadcrumb-item active">Review</li>
            </ol>
        </nav>
    </div>
    <span><?= getStatusBadge($review['status']) ?></span>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?= displaySessionMessages() ?>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i><?= htmlspecialchars($review['proposal_title']) ?></h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4 p-3 bg-light rounded">
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">Grant</small>
                        <span class="fw-semibold text-break"><?= htmlspecialchars($review['grant_title']) ?></span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">Funding Available</small>
                        <span class="fw-semibold"><?= $review['funding_amount'] ? formatAmount($review['funding_amount']) : 'N/A' ?></span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">Budget Requested</small>
                        <span class="fw-semibold text-primary"><?= $review['budget_amount'] ? formatAmount($review['budget_amount']) : 'N/A' ?></span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block">Submitted Date</small>
                        <span class="fw-semibold"><?= $review['submitted_at'] ? formatDate($review['submitted_at']) : 'N/A' ?></span>
                    </div>
                </div>

                <?php if ($review['abstract']): ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-card-text me-2 text-primary"></i>Abstract</h6>
                    <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($review['abstract']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($review['problem_statement']): ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-exclamation-diamond me-2 text-primary"></i>Problem Statement</h6>
                    <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($review['problem_statement']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($review['methodology']): ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Methodology & Approach</h6>
                    <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($review['methodology']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($review['expected_outcomes']): ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-bullseye me-2 text-primary"></i>Expected Outcomes & Impact</h6>
                    <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($review['expected_outcomes']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($review['budget_breakdown']): ?>
                <div class="mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-calculator me-2 text-primary"></i>Budget Breakdown</h6>
                    <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($review['budget_breakdown']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($documents)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip text-primary me-2"></i>Attached Proposal Documents (<?= count($documents) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($documents as $doc): ?>
                    <?php 
                    $docPath = !empty($doc['file_path']) ? $doc['file_path'] : $doc['file_name'];
                    $docUrl = SITE_URL . 'uploads/proposals/' . urlencode($docPath);
                    ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="<?= getFileIcon($doc['file_name']) ?> me-2 text-primary"></i>
                            <span class="fw-medium"><?= htmlspecialchars($doc['document_type'] ?? $doc['file_name']) ?></span>
                            <small class="text-muted ms-2">(<?= htmlspecialchars($doc['file_name']) ?><?= $doc['file_size'] ? ' - ' . round($doc['file_size'] / 1024, 1) . ' KB' : '' ?>)</small>
                        </div>
                        <a href="<?= $docUrl ?>" class="btn btn-sm btn-outline-primary" target="_blank" download>
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Full Researcher Details Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge text-primary me-2"></i>Researcher Profile</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if (!empty($review['res_picture']) && file_exists(PROFILE_PATH . $review['res_picture'])): ?>
                        <img src="<?= SITE_URL ?>uploads/profiles/<?= htmlspecialchars($review['res_picture']) ?>" class="rounded-circle mb-2 object-fit-cover shadow-sm" width="80" height="80" alt="Avatar">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 72px; height: 72px; font-size: 1.75rem; font-weight: bold;">
                            <?= strtoupper(substr($review['res_first'] ?? 'R', 0, 1) . substr($review['res_last'] ?? '', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($review['res_first'] . ' ' . $review['res_last']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($review['res_department'] ?? '') ?><?= !empty($review['res_department']) && !empty($review['res_institution']) ? ' • ' : '' ?><?= htmlspecialchars($review['res_institution'] ?? '') ?></small>
                </div>

                <!-- Academic Track Record -->
                <div class="card bg-body border mt-3 mb-3 p-2">
                    <div class="fw-bold small text-muted text-uppercase mb-2 text-center" style="font-size:0.72rem; letter-spacing:0.5px;"><i class="bi bi-graph-up me-1"></i>Academic Track Record</div>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded bg-body-tertiary">
                                <div class="fw-bold fs-6 text-primary"><?= (int)($resStats['total_proposals'] ?? 0) ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">Proposals</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-body-tertiary">
                                <div class="fw-bold fs-6 text-success"><?= (int)($resStats['approved_proposals'] ?? 0) ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">Approved</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-body-tertiary">
                                <div class="fw-bold fs-6 text-info"><?= (int)($resAwards['completed_projects'] ?? 0) ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group list-group-flush small mb-3">
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i>Email:</span>
                        <a href="mailto:<?= htmlspecialchars($review['res_email']) ?>" class="text-decoration-none fw-medium text-break"><?= htmlspecialchars($review['res_email']) ?></a>
                    </div>
                    <?php if (!empty($review['res_phone'])): ?>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i>Phone:</span>
                        <span class="fw-medium"><?= htmlspecialchars($review['res_phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($review['res_qualification'])): ?>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-mortarboard me-1"></i>Qualification:</span>
                        <span class="fw-medium text-end"><?= htmlspecialchars($review['res_qualification']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($review['res_expertise'])): ?>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-lightbulb me-1"></i>Expertise:</span>
                        <span class="fw-medium text-end"><?= htmlspecialchars($review['res_expertise']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($review['res_orcid'])): ?>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-fingerprint me-1"></i>ORCID:</span>
                        <a href="https://orcid.org/<?= htmlspecialchars($review['res_orcid']) ?>" target="_blank" class="text-decoration-none fw-medium"><?= htmlspecialchars($review['res_orcid']) ?></a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($review['res_scholar'])): ?>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-mortarboard-fill me-1"></i>Scholar:</span>
                        <a href="<?= htmlspecialchars($review['res_scholar']) ?>" target="_blank" class="text-decoration-none fw-medium">Profile <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($review['res_interests'])): ?>
                <div class="mb-3">
                    <small class="text-muted fw-bold d-block mb-1">Research Interests</small>
                    <p class="small text-secondary mb-0 bg-light p-2 rounded"><?= nl2br(htmlspecialchars($review['res_interests'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($review['res_bio'])): ?>
                <div class="mb-3">
                    <small class="text-muted fw-bold d-block mb-1">Biography</small>
                    <p class="small text-secondary mb-0 bg-light p-2 rounded"><?= nl2br(htmlspecialchars($review['res_bio'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($review['res_cv'])): ?>
                <div class="d-grid mt-3">
                    <a href="<?= SITE_URL ?>uploads/cvs/<?= urlencode($review['res_cv']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" download>
                        <i class="bi bi-file-earmark-person me-1"></i>Download Researcher CV
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><?= $isCompleted ? 'Review Details' : 'Submit Your Review' ?></h6>
    </div>
    <div class="card-body">
        <?php if ($isCompleted): ?>
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">Score</small>
                <p class="fw-bold fs-4 mb-0"><?= number_format($review['score'], 1) ?> / 100</p>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Recommendation</small>
                <p class="mb-0"><?= $review['recommendation'] ? getStatusBadge($review['recommendation']) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Recommended Funding</small>
                <p class="mb-0 fw-bold text-success">
                    <?= !empty($review['recommended_amount']) ? formatAmount($review['recommended_amount']) : '<span class="text-muted">—</span>' ?>
                </p>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Completed</small>
                <p class="mb-0"><?= $review['completed_at'] ? formatDateTime($review['completed_at']) : 'N/A' ?></p>
            </div>
            <?php if ($review['comments']): ?>
            <div class="col-12">
                <small class="text-muted">Comments</small>
                <p class="mb-0"><?= nl2br(htmlspecialchars($review['comments'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($review['evaluation_report']): ?>
            <div class="col-12">
                <small class="text-muted">Evaluation Report</small>
                <p class="mb-0"><?= nl2br(htmlspecialchars($review['evaluation_report'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <form method="POST" id="reviewForm">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-medium">Score (0-100) <span class="text-danger">*</span></label>
                    <input type="number" name="score" class="form-control" min="0" max="100" step="0.1" value="<?= htmlspecialchars($review['score'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium">Recommendation <span class="text-danger">*</span></label>
                    <select name="recommendation" id="recommendationSelect" class="form-select" required>
                        <option value="">Select Recommendation</option>
                        <option value="approve" <?= ($review['recommendation'] ?? '') === 'approve' ? 'selected' : '' ?>>Approve</option>
                        <option value="reject" <?= ($review['recommendation'] ?? '') === 'reject' ? 'selected' : '' ?>>Reject</option>
                        <option value="revision" <?= ($review['recommendation'] ?? '') === 'revision' ? 'selected' : '' ?>>Revision Needed</option>
                    </select>
                </div>
                <!-- Recommended Funding Amount — shown when Approve is selected -->
                <div class="col-12" id="recommendedAmountWrap" style="display:none;">
                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <label class="form-label small fw-medium">
                                <i class="bi bi-cash-coin text-success me-1"></i>Recommended Funding Amount
                                <span class="text-muted fw-normal ms-1">(optional)</span>
                            </label>
                            <div class="input-group" style="max-width:340px;">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="recommended_amount" id="recommendedAmount" class="form-control"
                                    min="0" step="0.01" placeholder="0.00"
                                    value="<?= htmlspecialchars($review['recommended_amount'] ?? '') ?>">
                            </div>
                            <small class="text-muted">Your funding recommendation will be shared with the Administrator for the final approval decision.</small>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Comments <span class="text-danger">*</span></label>
                    <textarea name="comments" class="form-control" rows="5" required><?= htmlspecialchars($review['comments'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Evaluation Report</label>
                    <textarea name="evaluation_report" class="form-control" rows="6"><?= htmlspecialchars($review['evaluation_report'] ?? '') ?></textarea>
                    <small class="text-muted">Detailed evaluation of the proposal strengths and weaknesses.</small>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit this review? This action cannot be undone.')">
                    <i class="bi bi-check-lg me-1"></i>Submit Review
                </button>
                <a href="<?= SITE_URL ?>reviewer/pages/assigned_proposals.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        <script>
        (function() {
            const sel = document.getElementById('recommendationSelect');
            const wrap = document.getElementById('recommendedAmountWrap');
            function toggleAmountField() {
                wrap.style.display = sel.value === 'approve' ? '' : 'none';
            }
            sel.addEventListener('change', toggleAmountField);
            toggleAmountField(); // run on load
        })();
        </script>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
