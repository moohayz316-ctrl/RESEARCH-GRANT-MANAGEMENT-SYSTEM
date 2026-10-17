<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
requireRole('reviewer');

$userId = $_SESSION['user_id'];
$db = getDb();

$stmt = $db->prepare("SELECT COUNT(*) as count FROM proposal_reviews WHERE reviewer_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$assignedProposals = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM proposal_reviews WHERE reviewer_id = :user_id AND status = 'completed'");
$stmt->execute([':user_id' => $userId]);
$completedReviews = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM proposal_reviews WHERE reviewer_id = :user_id AND status IN ('assigned','in_progress')");
$stmt->execute([':user_id' => $userId]);
$pendingReviews = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT AVG(score) as avg_score FROM proposal_reviews WHERE reviewer_id = :user_id AND status = 'completed' AND score IS NOT NULL");
$stmt->execute([':user_id' => $userId]);
$avgScore = $stmt->fetch()['avg_score'];

$stmt = $db->prepare("SELECT pr.*, p.title as proposal_title, p.abstract, g.title as grant_title, g.funding_amount, u.first_name as res_first, u.last_name as res_last FROM proposal_reviews pr JOIN proposals p ON pr.proposal_id = p.id JOIN grants g ON p.grant_id = g.id JOIN users u ON p.researcher_id = u.id WHERE pr.reviewer_id = :user_id ORDER BY pr.assigned_at DESC LIMIT 5");
$stmt->execute([':user_id' => $userId]);
$recentAssignments = $stmt->fetchAll();

$stmt = $db->prepare("SELECT pr.*, p.title as proposal_title, g.title as grant_title FROM proposal_reviews pr JOIN proposals p ON pr.proposal_id = p.id JOIN grants g ON p.grant_id = g.id WHERE pr.reviewer_id = :user_id AND pr.status = 'completed' ORDER BY pr.completed_at DESC LIMIT 5");
$stmt->execute([':user_id' => $userId]);
$recentCompleted = $stmt->fetchAll();

$stmt = $db->prepare("SELECT pr.status, COUNT(*) as count FROM proposal_reviews pr WHERE pr.reviewer_id = :user_id GROUP BY pr.status");
$stmt->execute([':user_id' => $userId]);
$statusData = $stmt->fetchAll();
$chartLabels = json_encode(array_column($statusData, 'status'));
$chartCounts = json_encode(array_column($statusData, 'count'));

$pageTitle = 'Reviewer Dashboard';
include_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Reviewer Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <a href="<?= SITE_URL ?>reviewer/pages/assigned_proposals.php" class="btn btn-primary">
        <i class="bi bi-file-earmark-check me-1"></i>View Assigned Proposals
    </a>
</div>

<?= displaySessionMessages() ?>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $assignedProposals ?></h5>
                        <small class="text-muted">Assigned Proposals</small>
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
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $completedReviews ?></h5>
                        <small class="text-muted">Completed Reviews</small>
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
                        <i class="bi bi-star fs-3 text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $avgScore ? number_format($avgScore, 1) : '-' ?></h5>
                        <small class="text-muted">Average Score</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recently Assigned Proposals</h6>
                <a href="<?= SITE_URL ?>reviewer/pages/assigned_proposals.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Proposal</th>
                                <th>Researcher</th>
                                <th>Grant</th>
                                <th>Status</th>
                                <th>Assigned</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentAssignments)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No proposals assigned yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentAssignments as $r): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars(truncateText($r['proposal_title'], 40)) ?></td>
                                <td><?= htmlspecialchars($r['res_first'] . ' ' . $r['res_last']) ?></td>
                                <td><?= htmlspecialchars(truncateText($r['grant_title'], 25)) ?></td>
                                <td><?= getStatusBadge($r['status']) ?></td>
                                <td><?= formatDate($r['assigned_at']) ?></td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-info btn-icon me-1" onclick="viewProposal(<?= $r['proposal_id'] ?>)" title="View Proposal & Researcher Profile"><i class="bi bi-eye"></i></button>
                                    <?php if ($r['status'] !== 'completed'): ?>
                                    <a href="<?= SITE_URL ?>reviewer/pages/review.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Review Proposal"><i class="bi bi-pencil-square"></i></a>
                                    <?php else: ?>
                                    <a href="<?= SITE_URL ?>reviewer/pages/review.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-success btn-icon" title="View Review"><i class="bi bi-check-circle"></i></a>
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
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Reviews by Status</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Recent Review History</h6>
        <a href="<?= SITE_URL ?>reviewer/pages/review_history.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Proposal</th>
                        <th>Grant</th>
                        <th>Score</th>
                        <th>Recommendation</th>
                        <th>Completed</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentCompleted)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No completed reviews yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentCompleted as $r): ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars(truncateText($r['proposal_title'], 40)) ?></td>
                        <td><?= htmlspecialchars(truncateText($r['grant_title'], 25)) ?></td>
                        <td><?= $r['score'] !== null ? number_format($r['score'], 1) : '-' ?></td>
                        <td><?= $r['recommendation'] ? getStatusBadge($r['recommendation']) : '-' ?></td>
                        <td><?= formatDate($r['completed_at']) ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-info btn-icon me-1" onclick="viewProposal(<?= $r['proposal_id'] ?>)" title="View Proposal & Researcher Profile"><i class="bi bi-eye"></i></button>
                            <a href="<?= SITE_URL ?>reviewer/pages/review.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-success btn-icon" title="View Review"><i class="bi bi-file-earmark-text"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Complete Proposal & Researcher Details Modal -->
<div class="modal fade" id="viewProposalModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="proposalModalTitle">Proposal & Researcher Details</h5>
                    <small class="text-muted" id="proposalModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="proposalDetailBody"></div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="proposalModalFooterActions"></div>
            </div>
        </div>
    </div>
</div>

<script>
function viewProposal(id) {
    $('#proposalModalTitle').text('Loading Proposal #' + id + '...');
    $('#proposalModalSubtitle').text('');
    $('#proposalModalFooterActions').html('');
    $('#proposalDetailBody').html(
        '<div class="text-center py-5"><div class="spinner-border text-primary mb-2"></div><p class="text-muted mb-0">Loading complete proposal dossier & researcher academic profile...</p></div>'
    );
    $('#viewProposalModal').modal('show');

    $.get('<?= SITE_URL ?>reviewer/ajax/get_proposal.php?id=' + id, function(data) {
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch(e){}
        }
        var r = data.researcher || {};
        var stats = r.stats || {};

        $('#proposalModalTitle').text(data.title || 'Proposal Details');
        $('#proposalModalSubtitle').html('Grant: <strong class="text-primary">' + data.grant_title + '</strong>');

        // Setup footer actions
        if (data.review_url) {
            if (data.review_status !== 'completed') {
                $('#proposalModalFooterActions').html(
                    '<a href="' + data.review_url + '" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Proceed to Review & Evaluation</a>'
                );
            } else {
                $('#proposalModalFooterActions').html(
                    '<a href="' + data.review_url + '" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>View Completed Review</a>'
                );
            }
        }

        var html = '<div class="row g-4">' +
            // Left Column: Full Researcher Academic Profile & Track Record
            '<div class="col-lg-4">' +
                '<div class="card bg-body-tertiary border-0 h-100">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold">' +
                        '<i class="bi bi-person-badge me-2 text-primary"></i>Assigned Researcher Profile' +
                    '</div>' +
                    '<div class="card-body">' +
                        '<div class="text-center mb-3">' +
                            '<img src="' + r.avatar + '" class="rounded-circle border border-2 border-primary mb-2" width="75" height="75">' +
                            '<h6 class="fw-bold mb-0">' + r.name + '</h6>' +
                            '<small class="text-muted d-block">' + r.department + ' &bull; ' + r.institution + '</small>' +
                        '</div>' +

                        // Track Record Badges
                        '<div class="card bg-body border mt-3 mb-3 p-2">' +
                            '<div class="fw-bold small text-muted text-uppercase mb-2 text-center" style="font-size:0.72rem; letter-spacing:0.5px;"><i class="bi bi-graph-up me-1"></i>Academic Track Record</div>' +
                            '<div class="row g-2 text-center">' +
                                '<div class="col-4">' +
                                    '<div class="p-2 rounded bg-body-tertiary">' +
                                        '<div class="fw-bold fs-6 text-primary">' + (stats.total_proposals || 0) + '</div>' +
                                        '<div class="text-muted" style="font-size:0.7rem;">Proposals</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-4">' +
                                    '<div class="p-2 rounded bg-body-tertiary">' +
                                        '<div class="fw-bold fs-6 text-success">' + (stats.approved_proposals || 0) + '</div>' +
                                        '<div class="text-muted" style="font-size:0.7rem;">Approved</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-4">' +
                                    '<div class="p-2 rounded bg-body-tertiary">' +
                                        '<div class="fw-bold fs-6 text-info">' + (stats.completed_projects || 0) + '</div>' +
                                        '<div class="text-muted" style="font-size:0.7rem;">Completed</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +

                        '<ul class="list-group list-group-flush bg-transparent small">' +
                            '<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-envelope me-1"></i>Email:</strong> <a href="mailto:' + r.email + '">' + r.email + '</a></li>' +
                            '<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-telephone me-1"></i>Phone:</strong> ' + r.phone + '</li>' +
                            '<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-mortarboard me-1"></i>Qualification:</strong> ' + r.qualification + '</li>' +
                            '<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-lightbulb me-1"></i>Expertise Area:</strong> ' + r.expertise_area + '</li>' +
                            (r.orcid_id ? ('<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-fingerprint me-1"></i>ORCID:</strong> <a href="https://orcid.org/' + encodeURIComponent(r.orcid_id) + '" target="_blank">' + r.orcid_id + '</a></li>') : '') +
                            (r.google_scholar ? ('<li class="list-group-item bg-transparent px-0"><strong class="text-muted"><i class="bi bi-mortarboard-fill me-1"></i>Scholar:</strong> <a href="' + r.google_scholar + '" target="_blank">View Google Scholar</a></li>') : '') +
                        '</ul>' +
                        (r.biography ? ('<div class="mt-3"><small class="text-muted fw-bold d-block">Biography:</small><p class="small text-secondary mb-2">' + r.biography + '</p></div>') : '') +
                        (r.research_interests ? ('<div class="mt-2"><small class="text-muted fw-bold d-block">Research Interests:</small><p class="small text-secondary mb-2">' + r.research_interests + '</p></div>') : '') +
                        (r.cv_url ? ('<div class="mt-3 pt-2 border-top"><a href="' + r.cv_url + '" class="btn btn-sm btn-outline-success w-100" target="_blank" download><i class="bi bi-file-earmark-person me-1"></i>Download Researcher CV</a></div>') : '') +
                    '</div>' +
                '</div>' +
            '</div>' +

            // Right Column: Proposal Content & Documents
            '<div class="col-lg-8">' +
                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-body p-3">' +
                        '<div class="row g-2 small mb-2">' +
                            '<div class="col-md-6"><span class="text-muted">Status:</span> ' + data.status_badge + '</div>' +
                            '<div class="col-md-6"><span class="text-muted">Budget Requested:</span> <strong>' + data.budget_amount + '</strong></div>' +
                            '<div class="col-md-6"><span class="text-muted">Grant Opportunity:</span> ' + data.grant_title + '</div>' +
                            '<div class="col-md-6"><span class="text-muted">Submitted Date:</span> ' + data.submitted_at + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                (data.abstract ? ('<div class="card bg-body-tertiary border-0 mb-3"><div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-card-text me-2"></i>Abstract</div><div class="card-body"><p class="mb-0">' + data.abstract + '</p></div></div>') : '') +
                (data.problem_statement ? ('<div class="card bg-body-tertiary border-0 mb-3"><div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-question-circle me-2"></i>Problem Statement</div><div class="card-body"><p class="mb-0">' + data.problem_statement + '</p></div></div>') : '') +
                (data.methodology ? ('<div class="card bg-body-tertiary border-0 mb-3"><div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-gear-wide-connected me-2"></i>Research Methodology</div><div class="card-body"><p class="mb-0">' + data.methodology + '</p></div></div>') : '') +
                (data.expected_outcomes ? ('<div class="card bg-body-tertiary border-0 mb-3"><div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-bullseye me-2"></i>Expected Outcomes</div><div class="card-body"><p class="mb-0">' + data.expected_outcomes + '</p></div></div>') : '') +
                (data.budget_breakdown ? ('<div class="card bg-body-tertiary border-0 mb-3"><div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-receipt me-2"></i>Budget Breakdown</div><div class="card-body"><p class="mb-0">' + data.budget_breakdown + '</p></div></div>') : '') +

                // Attached Documents
                '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-paperclip me-2"></i>Submitted Proposal Documents (' + (data.documents ? data.documents.length : 0) + ')</div>' +
                    '<div class="card-body p-0">';
        if (data.documents && data.documents.length > 0) {
            html += '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Type</th><th>File</th><th>Size</th><th class="text-end">Download</th></tr></thead><tbody>';
            data.documents.forEach(function(d) {
                html += '<tr>' +
                    '<td><span class="badge bg-secondary">' + d.document_type + '</span></td>' +
                    '<td><i class="bi ' + d.file_icon + ' me-1"></i>' + d.file_name + '</td>' +
                    '<td>' + d.file_size_formatted + '</td>' +
                    '<td class="text-end"><a href="' + d.download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download><i class="bi bi-download"></i></a></td>' +
                '</tr>';
            });
            html += '</tbody></table></div>';
        } else {
            html += '<p class="text-muted p-3 mb-0">No documents attached to this proposal.</p>';
        }
        html += '</div></div>' +
            '</div>' +
        '</div>';

        $('#proposalDetailBody').html(html);
    }).fail(function() {
        $('#proposalDetailBody').html('<div class="alert alert-danger">Could not load proposal and researcher details.</div>');
    });
}

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= $chartLabels ?>,
            datasets: [{
                data: <?= $chartCounts ?>,
                backgroundColor: ['#537497', '#c4936f', '#65a390']
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
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
