<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Applications & Decision Making';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    requireCSRF();
    $proposal_id = (int)$_POST['proposal_id'];
    $new_status = sanitizeInput($_POST['status']);
    
    // Check current proposal
    $stmt = $db->prepare("SELECT p.*, g.funding_amount, g.title as grant_title, u.email as researcher_email, u.first_name, u.last_name FROM proposals p JOIN grants g ON p.grant_id = g.id JOIN users u ON p.researcher_id = u.id WHERE p.id = :id");
    $stmt->execute([':id' => $proposal_id]);
    $prop = $stmt->fetch();

    if (!$prop) {
        $_SESSION['error_message'] = 'Proposal not found.';
        header('Location: ' . SITE_URL . 'admin/pages/applications.php');
        exit();
    }

    $stmt = $db->prepare("UPDATE proposals SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id");
    $stmt->execute([':status' => $new_status, ':reviewed_by' => $_SESSION['user_id'], ':id' => $proposal_id]);

    if ($new_status === 'approved') {
        $stmtAward = $db->prepare("SELECT id FROM grant_awards WHERE proposal_id = :proposal_id LIMIT 1");
        $stmtAward->execute([':proposal_id' => $proposal_id]);
        $existingAward = $stmtAward->fetch();

        // Admin-specified approved amount (from modal input), fallback to budget or grant amount
        $approvedAmount = isset($_POST['approved_amount']) && (float)$_POST['approved_amount'] > 0
            ? (float)$_POST['approved_amount']
            : ($prop['budget_amount'] > 0 ? $prop['budget_amount'] : $prop['funding_amount']);
        $awardAmount = $prop['budget_amount'] > 0 ? $prop['budget_amount'] : $prop['funding_amount'];

        if (!$existingAward) {
            $stmt = $db->prepare("INSERT INTO grant_awards (proposal_id, grant_id, researcher_id, awarded_amount, approved_amount, award_date, start_date, end_date, status, awarded_by) VALUES (:proposal_id, :grant_id, :researcher_id, :amount, :approved_amount, CURDATE(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'active', :awarded_by)");
            $stmt->execute([
                ':proposal_id'    => $proposal_id,
                ':grant_id'       => $prop['grant_id'],
                ':researcher_id'  => $prop['researcher_id'],
                ':amount'         => $awardAmount,
                ':approved_amount'=> $approvedAmount,
                ':awarded_by'     => $_SESSION['user_id'],
            ]);
            $awardId = $db->lastInsertId();
            logAudit($_SESSION['user_id'], 'approve', 'proposals', $proposal_id, 'Approved proposal, created award #' . $awardId . ', approved amount: ' . $approvedAmount);
        } else {
            // Update approved_amount on existing award
            $db->prepare("UPDATE grant_awards SET approved_amount = :approved_amount WHERE proposal_id = :proposal_id")->execute([':approved_amount' => $approvedAmount, ':proposal_id' => $proposal_id]);
            logAudit($_SESSION['user_id'], 'approve', 'proposals', $proposal_id, 'Approved proposal, updated existing award, approved amount: ' . $approvedAmount);
        }

        // Notify researcher
        createNotification(
            $prop['researcher_id'],
            'grant_awarded',
            'Proposal Approved & Grant Awarded!',
            'Congratulations! Your research proposal "' . $prop['title'] . '" for "' . $prop['grant_title'] . '" has been approved. An official grant award has been issued.',
            SITE_URL . 'researcher/pages/my_proposals.php'
        );
    } elseif ($new_status === 'rejected') {
        logAudit($_SESSION['user_id'], 'reject', 'proposals', $proposal_id, 'Rejected proposal #' . $proposal_id);
        createNotification(
            $prop['researcher_id'],
            'proposal_rejected',
            'Proposal Decision: Not Approved',
            'Your research proposal "' . $prop['title'] . '" was not selected for funding at this time.',
            SITE_URL . 'researcher/pages/my_proposals.php'
        );
    } else {
        logAudit($_SESSION['user_id'], 'update_status', 'proposals', $proposal_id, 'Changed proposal status to ' . $new_status);
        $statusLabel = ucwords(str_replace('_', ' ', $new_status));
        createNotification(
            $prop['researcher_id'],
            'proposal_status',
            'Proposal Status Updated',
            'Your proposal "' . $prop['title'] . '" status has been updated to: ' . $statusLabel . '.',
            SITE_URL . 'researcher/pages/my_proposals.php'
        );
    }

    $_SESSION['success_message'] = 'Application status updated successfully to ' . ucwords(str_replace('_', ' ', $new_status)) . '.';
    header('Location: ' . SITE_URL . 'admin/pages/applications.php');
    exit();
}

$stmt = $db->query("SELECT p.*, u.first_name, u.last_name, u.email as researcher_email, g.title as grant_title, g.funding_amount as grant_funding, (SELECT COUNT(*) FROM proposal_reviews pr WHERE pr.proposal_id = p.id) as review_count, (SELECT AVG(pr.score) FROM proposal_reviews pr WHERE pr.proposal_id = p.id AND pr.status = 'completed') as avg_review_score FROM proposals p JOIN users u ON p.researcher_id = u.id JOIN grants g ON p.grant_id = g.id ORDER BY p.created_at DESC");
$applications = $stmt->fetchAll();

$counts = [
    'all' => count($applications),
    'submitted' => 0,
    'under_review' => 0,
    'approved' => 0,
    'rejected' => 0,
    'revision_needed' => 0,
];
foreach ($applications as $app) {
    if (isset($counts[$app['status']])) {
        $counts[$app['status']]++;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Applications & Decision Making</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Applications</li>
            </ol>
        </nav>
    </div>
</div>

<?= displaySessionMessages() ?>

<!-- Filter Tabs -->
<div class="mb-3 d-flex flex-wrap gap-2">
    <button class="btn btn-sm btn-primary filter-btn active" data-filter="all">All Applications <span class="badge bg-white text-primary ms-1"><?= $counts['all'] ?></span></button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-filter="submitted">Submitted <span class="badge bg-info ms-1"><?= $counts['submitted'] ?></span></button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-filter="under_review">Under Review <span class="badge bg-warning text-dark ms-1"><?= $counts['under_review'] ?></span></button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-filter="approved">Approved <span class="badge bg-success ms-1"><?= $counts['approved'] ?></span></button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-filter="rejected">Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected'] ?></span></button>
</div>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Research Applications</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover align-middle" id="applicationsTable">
                <thead class="table-light">
                    <tr>
                        <th>Applicant</th>
                        <th>Proposal Title</th>
                        <th>Grant Opportunity</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Review Score</th>
                        <th class="no-print text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No applications found</td></tr>
                    <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                    <tr data-status="<?= htmlspecialchars($app['status']) ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($app['first_name'] . ' ' . $app['last_name']) ?>" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['researcher_email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="javascript:void(0)" onclick="viewApplication(<?= $app['id'] ?>)" class="fw-medium text-decoration-none">
                                <?= htmlspecialchars($app['title']) ?>
                            </a>
                            <div class="small text-muted">Budget: <?= $app['budget_amount'] ? formatAmount($app['budget_amount']) : 'N/A' ?></div>
                        </td>
                        <td><?= htmlspecialchars($app['grant_title']) ?></td>
                        <td><?= getStatusBadge($app['status']) ?></td>
                        <td><?= $app['submitted_at'] ? formatDate($app['submitted_at']) : '-' ?></td>
                        <td>
                            <?php if ($app['avg_review_score'] !== null): ?>
                                <span class="badge bg-info text-dark fs-6"><?= number_format($app['avg_review_score'], 1) ?> / 100</span>
                            <?php elseif ($app['review_score'] !== null): ?>
                                <span class="badge bg-info text-dark fs-6"><?= number_format($app['review_score'], 1) ?> / 100</span>
                            <?php else: ?>
                                <span class="text-muted small">No Score</span>
                            <?php endif; ?>
                            <?php if ($app['review_count'] > 0): ?>
                                <small class="d-block text-muted"><?= $app['review_count'] ?> review(s)</small>
                            <?php endif; ?>
                        </td>
                        <td class="no-print text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewApplication(<?= $app['id'] ?>)" title="View Full Details & Evaluations">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                                <?php if (in_array($app['status'], ['submitted', 'under_review', 'revision_needed'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmDecision(<?= $app['id'] ?>, 'approved')" title="Approve & Award">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDecision(<?= $app['id'] ?>, 'rejected')" title="Reject">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Application & Decision Modal -->
<div class="modal fade" id="viewAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalAppTitle">Application Details</h5>
                    <small class="text-muted" id="modalAppSubtitle"></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="modalAppBadge"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="appDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading proposal and evaluation details...</p>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
                <div id="modalActionButtons" class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="statusForm" method="post" style="display:none">
    <input type="hidden" name="change_status" value="1">
    <input type="hidden" name="proposal_id" id="status_proposal_id">
    <input type="hidden" name="status" id="status_value">
    <?= csrfField() ?>
</form>

<script>
let currentProposalData = null;

function viewApplication(id) {
    $('#modalAppTitle').text('Loading Application #' + id + '...');
    $('#modalAppSubtitle').text('');
    $('#modalAppBadge').html('');
    $('#modalActionButtons').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
    $('#viewAppModal').modal('show');
    
    $('#appDetailBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading proposal, documents, and reviewer evaluations...</p>
        </div>
    `);

    $.get('<?= SITE_URL ?>admin/ajax/handle_users.php?action=get_application&id=' + id, function(data) {
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch(e){}
        }
        currentProposalData = data;
        
        $('#modalAppTitle').text(data.title || 'Proposal Details');
        $('#modalAppSubtitle').text('Grant: ' + (data.grant_title || 'N/A'));
        $('#modalAppBadge').html(data.status_badge || '');

        let html = `
            <div class="row g-4">
                <!-- Left: Proposal Details -->
                <div class="col-lg-8">
                    <!-- Key Metrics -->
                    <div class="row g-2 p-3 bg-light rounded mb-3">
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">Requested Budget</small>
                            <span class="fw-bold text-primary">${data.budget_amount ? '<?= CURRENCY_SYMBOL ?>' + parseFloat(data.budget_amount).toLocaleString() : 'N/A'}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">Grant Funding</small>
                            <span class="fw-semibold">${data.grant_funding ? '<?= CURRENCY_SYMBOL ?>' + parseFloat(data.grant_funding).toLocaleString() : 'N/A'}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">Submitted Date</small>
                            <span class="fw-semibold">${data.submitted_at || 'N/A'}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <small class="text-muted d-block">Review Score</small>
                            <span class="fw-bold text-success">${data.review_score !== null && data.review_score !== undefined ? parseFloat(data.review_score).toFixed(1) + ' / 100' : 'Pending'}</span>
                        </div>
                    </div>

                    ${data.abstract ? `
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark"><i class="bi bi-card-text text-primary me-2"></i>Abstract</h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.abstract)}</p>
                        </div>
                    </div>` : ''}

                    ${data.problem_statement ? `
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark"><i class="bi bi-exclamation-diamond text-primary me-2"></i>Problem Statement</h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.problem_statement)}</p>
                        </div>
                    </div>` : ''}

                    ${data.methodology ? `
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark"><i class="bi bi-gear text-primary me-2"></i>Methodology</h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.methodology)}</p>
                        </div>
                    </div>` : ''}

                    ${data.expected_outcomes ? `
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark"><i class="bi bi-bullseye text-primary me-2"></i>Expected Outcomes</h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.expected_outcomes)}</p>
                        </div>
                    </div>` : ''}

                    ${data.budget_breakdown ? `
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark"><i class="bi bi-calculator text-primary me-2"></i>Budget Breakdown</h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.budget_breakdown)}</p>
                        </div>
                    </div>` : ''}

                    <!-- Proposal Documents -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="fw-bold mb-0"><i class="bi bi-paperclip text-primary me-2"></i>Attached Proposal Documents (${(data.documents || []).length})</h6>
                        </div>
                        <div class="card-body p-0">
                            ${(data.documents && data.documents.length > 0) ? `
                                <ul class="list-group list-group-flush">
                                    ${data.documents.map(doc => `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="${doc.file_icon} text-primary me-2"></i>
                                                <span class="fw-medium">${escapeHtml(doc.document_type || doc.file_name)}</span>
                                                <small class="text-muted ms-2">(${escapeHtml(doc.file_name)} - ${doc.file_size_formatted})</small>
                                            </div>
                                            <a href="${doc.download_url}" class="btn btn-sm btn-outline-primary" target="_blank" download>
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>
                                        </li>
                                    `).join('')}
                                </ul>
                            ` : '<p class="text-muted text-center py-3 mb-0">No documents attached.</p>'}
                        </div>
                    </div>
                </div>

                <!-- Right: Researcher & Reviewer Evaluations -->
                <div class="col-lg-4">
                    <!-- Applicant Info -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="fw-bold mb-0"><i class="bi bi-person text-primary me-2"></i>Applicant Details</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">${escapeHtml(data.researcher_name)}</h6>
                            <p class="text-muted small mb-2">${escapeHtml(data.researcher_department || '')} ${data.researcher_department && data.researcher_institution ? '•' : ''} ${escapeHtml(data.researcher_institution || '')}</p>
                            <hr class="my-2">
                            <div class="small">
                                <p class="mb-1"><strong>Email:</strong> <a href="mailto:${escapeHtml(data.researcher_email)}">${escapeHtml(data.researcher_email)}</a></p>
                                ${data.researcher_phone ? `<p class="mb-1"><strong>Phone:</strong> ${escapeHtml(data.researcher_phone)}</p>` : ''}
                                ${data.researcher_qualification ? `<p class="mb-1"><strong>Qualification:</strong> ${escapeHtml(data.researcher_qualification)}</p>` : ''}
                                ${data.researcher_expertise ? `<p class="mb-0"><strong>Expertise:</strong> ${escapeHtml(data.researcher_expertise)}</p>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Reviewer Evaluations -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="fw-bold mb-0"><i class="bi bi-clipboard-check text-primary me-2"></i>Reviewer Evaluations (${(data.reviews || []).length})</h6>
                        </div>
                        <div class="card-body p-2">
                            ${(data.reviews && data.reviews.length > 0) ? `
                                ${data.reviews.map(r => `
                                    <div class="border rounded p-3 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <div class="fw-bold">${escapeHtml(r.reviewer_name)}</div>
                                                <small class="text-muted">${r.completed_at_formatted}</small>
                                            </div>
                                            <div>${r.recommendation_badge}</div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-dark fs-6">${r.score !== null ? parseFloat(r.score).toFixed(1) + ' / 100' : 'Score Pending'}</span>
                                            ${r.recommended_amount_formatted ? `<span class="badge bg-success ms-1"><i class="bi bi-cash-coin me-1"></i>Rec. Funding: ${r.recommended_amount_formatted}</span>` : ''}
                                        </div>
                                        ${r.comments ? `
                                            <div class="mb-2">
                                                <small class="fw-bold d-block text-muted">Comments:</small>
                                                <p class="small mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(r.comments)}</p>
                                            </div>
                                        ` : ''}
                                        ${r.evaluation_report ? `
                                            <div>
                                                <small class="fw-bold d-block text-muted">Evaluation Report:</small>
                                                <p class="small mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(r.evaluation_report)}</p>
                                            </div>
                                        ` : ''}
                                    </div>
                                `).join('')}
                            ` : '<p class="text-muted text-center py-3 mb-0">No reviewer evaluations submitted yet.</p>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#appDetailBody').html(html);

        // Update footer action buttons based on status
        let footerHtml = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        `;
        if (data.status === 'submitted' || data.status === 'under_review' || data.status === 'revision_needed') {
            footerHtml = `
                <button type="button" class="btn btn-danger" onclick="confirmDecision(${data.id}, 'rejected')">
                    <i class="bi bi-x-circle me-1"></i>Reject Proposal
                </button>
                <button type="button" class="btn btn-warning text-dark" onclick="confirmDecision(${data.id}, 'revision_needed')">
                    <i class="bi bi-arrow-repeat me-1"></i>Request Revision
                </button>
                <button type="button" class="btn btn-success" onclick="confirmDecision(${data.id}, 'approved', ${data.budget_amount || 0}, ${data.avg_recommended_amount || data.budget_amount || 0})">
                    <i class="bi bi-check-circle me-1"></i>Approve & Award Grant
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            `;
        }
        $('#modalActionButtons').html(footerHtml);
    }).fail(function() {
        $('#appDetailBody').html('<div class="alert alert-danger">Failed to load application details.</div>');
    });
}

function confirmDecision(id, status, budgetAmount, recommendedAmount) {
    let title = 'Change Status';
    let text = 'Are you sure you want to change status to ' + status + '?';
    let btnColor = '#3085d6';
    let icon = 'question';

    if (status === 'approved') {
        const suggestedAmt = recommendedAmount || budgetAmount || 0;
        title = 'Approve & Award Grant';
        icon = 'success';
        btnColor = '#198754';

        Swal.fire({
            title: title,
            icon: icon,
            html: `
                <p class="mb-3">Set the <strong>Approved Funding Amount</strong> for this grant award.</p>
                <div class="mb-2 text-start">
                    <label class="form-label small fw-semibold">Approved Amount (<?= CURRENCY_SYMBOL ?>)</label>
                    <input type="number" id="swal-approved-amount" class="swal2-input" min="0" step="0.01"
                           placeholder="Enter approved amount" value="${parseFloat(suggestedAmt).toFixed(2)}"
                           style="width:100%;margin:0;">
                    ${suggestedAmt ? `<small class="text-muted">Reviewer recommended: <?= CURRENCY_SYMBOL ?>${parseFloat(suggestedAmt).toLocaleString('en-NG', {minimumFractionDigits:2})}</small>` : ''}
                </div>`,
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve & Award',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            preConfirm: () => {
                const val = document.getElementById('swal-approved-amount').value;
                if (!val || parseFloat(val) <= 0) {
                    Swal.showValidationMessage('Please enter a valid approved amount greater than 0.');
                    return false;
                }
                return parseFloat(val);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('#status_proposal_id').val(id);
                $('#status_value').val(status);
                // Inject approved_amount into form
                let existing = $('#status_approved_amount');
                if (!existing.length) {
                    $('#statusForm').append('<input type="hidden" name="approved_amount" id="status_approved_amount">');
                }
                $('#status_approved_amount').val(result.value);
                $('#statusForm').submit();
            }
        });
        return; // Early return — SweetAlert handles async flow
    } else if (status === 'rejected') {
        title = 'Reject Proposal';
        text = 'Are you sure you want to reject this proposal? The researcher will be notified.';
        btnColor = '#dc3545';
        icon = 'warning';
    } else if (status === 'revision_needed') {
        title = 'Request Revision';
        text = 'Request the researcher to revise and resubmit their proposal?';
        btnColor = '#ffc107';
        icon = 'info';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#status_proposal_id').val(id);
            $('#status_value').val(status);
            $('#statusForm').submit();
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return $('<div>').text(text).html();
}

$(document).ready(function() {
    // Filter buttons
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        
        let filter = $(this).data('filter');
        if (filter === 'all') {
            $('#applicationsTable tbody tr').show();
        } else {
            $('#applicationsTable tbody tr').each(function() {
                if ($(this).data('status') === filter) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    // Auto-open proposal if query param ?id=X exists
    const urlParams = new URLSearchParams(window.location.search);
    const appId = urlParams.get('id');
    if (appId) {
        viewApplication(appId);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

