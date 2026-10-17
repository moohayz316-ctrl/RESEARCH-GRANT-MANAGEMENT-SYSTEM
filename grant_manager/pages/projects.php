<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
if (!in_array($_SESSION['user_role'], ['admin', 'grant_manager'])) {
    requireRole('admin');
}

$db = getDb();
$pageTitle = 'Monitored Projects';

$filterStatus = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$sql = "SELECT ga.*, u.first_name, u.last_name, u.email, g.title as grant_title, p.title as proposal_title 
        FROM grant_awards ga 
        JOIN users u ON ga.researcher_id = u.id 
        JOIN grants g ON ga.grant_id = g.id 
        JOIN proposals p ON ga.proposal_id = p.id";
$params = [];
if ($filterStatus) {
    $sql .= " WHERE ga.status = :status";
    $params[':status'] = $filterStatus;
}
$sql .= " ORDER BY ga.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Counts for filter badges
$counts = [
    'all' => (int)getTableCount('grant_awards'),
    'active' => (int)getTableCount('grant_awards', "status = 'active'"),
    'completed' => (int)getTableCount('grant_awards', "status = 'completed'"),
    'suspended' => (int)getTableCount('grant_awards', "status = 'suspended'"),
    'terminated' => (int)getTableCount('grant_awards', "status = 'terminated'"),
];

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Monitored Projects</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Projects</li>
            </ol>
        </nav>
    </div>
</div>

<?= displaySessionMessages() ?>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold">All Projects</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="?" class="btn btn-sm <?= empty($filterStatus) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    All <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
                </a>
                <a href="?status=active" class="btn btn-sm <?= $filterStatus === 'active' ? 'btn-success' : 'btn-outline-success' ?>">
                    Active <span class="badge bg-secondary ms-1"><?= $counts['active'] ?></span>
                </a>
                <a href="?status=completed" class="btn btn-sm <?= $filterStatus === 'completed' ? 'btn-success' : 'btn-outline-success' ?>">
                    Completed <span class="badge bg-secondary ms-1"><?= $counts['completed'] ?></span>
                </a>
                <a href="?status=suspended" class="btn btn-sm <?= $filterStatus === 'suspended' ? 'btn-warning' : 'btn-outline-warning' ?>">
                    Suspended <span class="badge bg-secondary ms-1"><?= $counts['suspended'] ?></span>
                </a>
                <a href="?status=terminated" class="btn btn-sm <?= $filterStatus === 'terminated' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    Terminated <span class="badge bg-secondary ms-1"><?= $counts['terminated'] ?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover align-middle">
                <thead>
                    <tr>
                        <th>Project Title</th>
                        <th>Researcher</th>
                        <th>Grant Opportunity</th>
                        <th>Award Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No projects found</td></tr>
                    <?php else: ?>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td class="fw-medium">
                            <a href="javascript:void(0)" onclick="viewProject(<?= $p['id'] ?>)" class="text-decoration-none fw-semibold text-primary" title="<?= htmlspecialchars($p['proposal_title']) ?>">
                                <i class="bi bi-file-earmark-text me-1 text-muted"></i><?= htmlspecialchars(truncateText($p['proposal_title'], 42)) ?>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($p['first_name'] . ' ' . $p['last_name']) ?>" class="rounded-circle me-2" width="30" height="30" alt="Avatar">
                                <div>
                                    <div class="fw-medium"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span title="<?= htmlspecialchars($p['grant_title']) ?>" class="text-secondary">
                                <?= htmlspecialchars(truncateText($p['grant_title'], 28)) ?>
                            </span>
                        </td>
                        <td class="fw-semibold"><?= formatAmount($p['awarded_amount']) ?></td>
                        <td><?= $p['start_date'] ? formatDate($p['start_date']) : '-' ?></td>
                        <td><?= $p['end_date'] ? formatDate($p['end_date']) : '-' ?></td>
                        <td><?= getStatusBadge($p['status']) ?></td>
                        <td class="no-print">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-info btn-icon" title="View Full Project Details" onclick="viewProject(<?= $p['id'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="<?= SITE_URL ?>grant_manager/pages/milestones.php?award_id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-icon" title="Manage Milestones">
                                    <i class="bi bi-signpost-2"></i>
                                </a>
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

<!-- Modal for Viewing Full Project Details -->
<div class="modal fade" id="viewProjectModal" tabindex="-1" aria-labelledby="viewProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2 flex-shrink-0">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold text-truncate mb-0" id="viewProjectModalLabel">Project Details</h5>
                        <small class="text-muted text-truncate d-block" id="viewProjectSubtitle">Monitored Research Grant Project</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span id="projectStatusBadge"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="projectDetailContent">
                <!-- Loaded dynamically via AJAX -->
            </div>
            <div class="modal-footer border-top bg-light-subtle d-flex justify-content-between align-items-center">
                <div id="projectModalFooterLinks" class="d-flex gap-2"></div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewProject(id) {
    var $modal = $('#viewProjectModal');
    var $content = $('#projectDetailContent');
    var $title = $('#viewProjectModalLabel');
    var $subtitle = $('#viewProjectSubtitle');
    var $badge = $('#projectStatusBadge');
    var $footerLinks = $('#projectModalFooterLinks');

    $title.text('Loading Project Details...');
    $subtitle.text('Please wait while data is retrieved');
    $badge.empty();
    $footerLinks.empty();

    $content.html(
        '<div class="text-center py-5">' +
            '<div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">' +
                '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<h6 class="text-muted fw-normal">Loading full project and proposal details...</h6>' +
        '</div>'
    );

    $modal.modal('show');

    $.ajax({
        url: '<?= SITE_URL ?>grant_manager/ajax/get_project_details.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(res) {
            if (!res.success) {
                $content.html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>' + escapeHtml(res.message || 'Error loading project') + '</div>');
                return;
            }

            var p = res.proposal;
            var a = res.award;
            var r = res.researcher;
            var g = res.grant;
            var docs = res.documents || [];
            var ms = res.milestones || { items: [], total: 0, completed: 0, progress_percent: 0 };
            var reps = res.reports || { items: [], total: 0 };
            var fr = res.final_report;

            $title.text(p.title || 'Project Details');
            $subtitle.html('Grant: <strong class="text-light">' + escapeHtml(g.title) + '</strong> &bull; Category: <span class="badge bg-secondary">' + escapeHtml(g.category_name) + '</span>');
            $badge.html(a.status_badge);

            // Build footer action links
            $footerLinks.html(
                '<a href="<?= SITE_URL ?>grant_manager/pages/milestones.php?award_id=' + a.id + '" class="btn btn-sm btn-outline-primary">' +
                    '<i class="bi bi-signpost-2 me-1"></i>Manage Milestones (' + ms.total + ')' +
                '</a>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="printProjectDetails()">' +
                    '<i class="bi bi-printer me-1"></i>Print Summary' +
                '</button>'
            );

            // Construct Quick KPI Stat Cards
            var html = '<div class="row g-3 mb-4">' +
                '<div class="col-md-3 col-sm-6">' +
                    '<div class="card bg-body-tertiary border-0 h-100 shadow-sm">' +
                        '<div class="card-body p-3">' +
                            '<small class="text-muted d-block mb-1">Awarded Amount</small>' +
                            '<h5 class="fw-bold text-success mb-1">' + a.awarded_amount_formatted + '</h5>' +
                            '<small class="text-muted">Proposed: ' + p.budget_amount_formatted + '</small>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 col-sm-6">' +
                    '<div class="card bg-body-tertiary border-0 h-100 shadow-sm">' +
                        '<div class="card-body p-3">' +
                            '<small class="text-muted d-block mb-1">Project Period</small>' +
                            '<h6 class="fw-bold mb-1">' + a.start_date_formatted + '</h6>' +
                            '<small class="text-muted">to ' + a.end_date_formatted + '</small>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 col-sm-6">' +
                    '<div class="card bg-body-tertiary border-0 h-100 shadow-sm">' +
                        '<div class="card-body p-3">' +
                            '<small class="text-muted d-block mb-1">Researcher</small>' +
                            '<h6 class="fw-bold text-truncate mb-1">' + escapeHtml(r.name) + '</h6>' +
                            '<small class="text-muted text-truncate d-block">' + escapeHtml(r.institution || r.department || 'Researcher') + '</small>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 col-sm-6">' +
                    '<div class="card bg-body-tertiary border-0 h-100 shadow-sm">' +
                        '<div class="card-body p-3">' +
                            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                '<small class="text-muted">Milestones</small>' +
                                '<small class="fw-bold text-primary">' + ms.progress_percent + '%</small>' +
                            '</div>' +
                            '<div class="progress mb-1" style="height: 6px;">' +
                                '<div class="progress-bar bg-primary" role="progressbar" style="width: ' + ms.progress_percent + '%"></div>' +
                            '</div>' +
                            '<small class="text-muted">' + ms.completed + ' of ' + ms.total + ' completed</small>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            // Navigation Tabs
            html += '<ul class="nav nav-tabs mb-4" id="projectDetailsTabs" role="tablist">' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link active" id="proposal-tab" data-bs-toggle="tab" data-bs-target="#proposal-pane" type="button" role="tab">' +
                        '<i class="bi bi-file-earmark-text me-1"></i>Project Proposal' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="budget-tab" data-bs-toggle="tab" data-bs-target="#budget-pane" type="button" role="tab">' +
                        '<i class="bi bi-cash-coin me-1"></i>Budget & Award' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="researcher-tab" data-bs-toggle="tab" data-bs-target="#researcher-pane" type="button" role="tab">' +
                        '<i class="bi bi-person-badge me-1"></i>Researcher Info' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#milestones-pane" type="button" role="tab">' +
                        '<i class="bi bi-signpost-2 me-1"></i>Milestones <span class="badge bg-secondary ms-1">' + ms.total + '</span>' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-pane" type="button" role="tab">' +
                        '<i class="bi bi-clipboard-data me-1"></i>Progress Reports <span class="badge bg-secondary ms-1">' + reps.total + '</span>' +
                    '</button>' +
                '</li>' +
            '</ul>';

            html += '<div class="tab-content" id="projectDetailsTabContent">';

            // TAB 1: Proposal Details (Submitted by User)
            html += '<div class="tab-pane fade show active" id="proposal-pane" role="tabpanel">' +
                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-body p-3">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<h6 class="fw-bold mb-0 text-primary"><i class="bi bi-card-heading me-2"></i>Project Title</h6>' +
                            '<div>' + p.status_badge + (p.review_score ? ' <span class="badge bg-info ms-1">Score: ' + p.review_score + '</span>' : '') + '</div>' +
                        '</div>' +
                        '<h5 class="fw-bold mb-2">' + escapeHtml(p.title) + '</h5>' +
                        '<small class="text-muted"><i class="bi bi-clock me-1"></i>Submitted on: ' + p.submitted_at_formatted + '</small>' +
                    '</div>' +
                '</div>' +

                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-card-text me-2"></i>Abstract / Executive Summary</div>' +
                    '<div class="card-body">' +
                        '<p class="mb-0 text-body lh-base">' + nl2br(p.abstract) + '</p>' +
                    '</div>' +
                '</div>' +

                '<div class="row g-3 mb-3">' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-question-circle me-2"></i>Problem Statement</div>' +
                            '<div class="card-body">' +
                                '<p class="mb-0 text-body lh-base">' + nl2br(p.problem_statement) + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-gear-wide-connected me-2"></i>Research Methodology</div>' +
                            '<div class="card-body">' +
                                '<p class="mb-0 text-body lh-base">' + nl2br(p.methodology) + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-bullseye me-2"></i>Expected Outcomes & Impact</div>' +
                    '<div class="card-body">' +
                        '<p class="mb-0 text-body lh-base">' + nl2br(p.expected_outcomes) + '</p>' +
                    '</div>' +
                '</div>' +

                // Attached Proposal Documents
                '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">' +
                        '<span class="fw-bold"><i class="bi bi-paperclip me-2"></i>Submitted Proposal Documents (' + docs.length + ')</span>' +
                    '</div>' +
                    '<div class="card-body p-0">';

            if (docs.length === 0) {
                html += '<div class="text-center py-4 text-muted"><i class="bi bi-file-earmark-x fs-3 d-block mb-1"></i>No attached documents uploaded for this proposal.</div>';
            } else {
                html += '<div class="table-responsive"><table class="table table-hover mb-0">' +
                    '<thead class="table-light">' +
                        '<tr>' +
                            '<th>Document Type</th>' +
                            '<th>File Name</th>' +
                            '<th>Size</th>' +
                            '<th>Uploaded Date</th>' +
                            '<th class="text-end">Action</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
                docs.forEach(function(doc) {
                    html += '<tr>' +
                        '<td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">' + escapeHtml(doc.document_type_label) + '</span></td>' +
                        '<td><i class="bi ' + escapeHtml(doc.file_icon) + ' me-2 fs-5 align-middle"></i>' + escapeHtml(doc.file_name) + '</td>' +
                        '<td>' + escapeHtml(doc.file_size_formatted) + '</td>' +
                        '<td>' + (doc.uploaded_at || '-') + '</td>' +
                        '<td class="text-end">' +
                            '<a href="' + doc.download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download>' +
                                '<i class="bi bi-download me-1"></i>Download' +
                            '</a>' +
                        '</td>' +
                    '</tr>';
                });
                html += '</tbody></table></div>';
            }

            html += '</div></div></div>'; // End Tab 1

            // TAB 2: Budget & Award
            html += '<div class="tab-pane fade" id="budget-pane" role="tabpanel">' +
                '<div class="row g-3 mb-3">' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-wallet2 me-2"></i>Financial Allocation</div>' +
                            '<div class="card-body">' +
                                '<ul class="list-group list-group-flush bg-transparent">' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Awarded Amount:</span>' +
                                        '<strong class="text-success fs-5">' + a.awarded_amount_formatted + '</strong>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Proposed Budget:</span>' +
                                        '<span class="fw-semibold">' + p.budget_amount_formatted + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Grant Maximum Funding:</span>' +
                                        '<span>' + g.funding_amount_formatted + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Award Status:</span>' +
                                        '<span>' + a.status_badge + '</span>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-calendar3 me-2"></i>Award Governance & Timeline</div>' +
                            '<div class="card-body">' +
                                '<ul class="list-group list-group-flush bg-transparent">' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Award Date:</span>' +
                                        '<span>' + a.award_date_formatted + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Start Date:</span>' +
                                        '<span>' + a.start_date_formatted + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">End Date:</span>' +
                                        '<span>' + a.end_date_formatted + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Awarded By:</span>' +
                                        '<span>' + escapeHtml(a.awarded_by_name || 'System Admin') + '</span>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-receipt me-2"></i>Budget Breakdown / Expenditure Plan</div>' +
                    '<div class="card-body">' +
                        '<p class="mb-0 text-body lh-base">' + nl2br(p.budget_breakdown) + '</p>' +
                    '</div>' +
                '</div>' +

                '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-shield-check me-2"></i>Terms, Conditions & Formal Approval</div>' +
                    '<div class="card-body">' +
                        '<div class="mb-3">' +
                            '<strong class="d-block mb-1 text-muted">Award Terms:</strong>' +
                            '<p class="mb-0 text-body">' + (a.terms_conditions ? nl2br(a.terms_conditions) : '<span class="text-muted fst-italic">Standard institutional research grant agreement terms and compliance rules apply.</span>') + '</p>' +
                        '</div>';
            if (a.approval_letter_url) {
                html += '<div class="pt-2 border-top">' +
                    '<strong class="d-block mb-1 text-muted">Approval Letter:</strong>' +
                    '<a href="' + a.approval_letter_url + '" class="btn btn-sm btn-outline-success" target="_blank">' +
                        '<i class="bi bi-file-earmark-check me-1"></i>Download Official Approval Letter' +
                    '</a>' +
                '</div>';
            }
            html += '</div></div></div>'; // End Tab 2

            // TAB 3: Researcher Profile
            html += '<div class="tab-pane fade" id="researcher-pane" role="tabpanel">' +
                '<div class="card bg-body-tertiary border-0 mb-3">' +
                    '<div class="card-body p-4">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<img src="' + r.avatar + '" class="rounded-circle border border-2 border-primary" width="70" height="70" alt="' + escapeHtml(r.name) + '">' +
                            '<div>' +
                                '<h5 class="fw-bold mb-1">' + escapeHtml(r.name) + '</h5>' +
                                '<p class="text-muted mb-0"><i class="bi bi-building me-1"></i>' + escapeHtml(r.department) + ' &bull; ' + escapeHtml(r.institution) + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                '<div class="row g-3 mb-3">' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-telephone me-2"></i>Contact Details</div>' +
                            '<div class="card-body">' +
                                '<ul class="list-group list-group-flush bg-transparent">' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Email:</span>' +
                                        '<a href="mailto:' + escapeHtml(r.email) + '" class="text-decoration-none fw-medium">' + escapeHtml(r.email) + '</a>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Phone:</span>' +
                                        '<span>' + escapeHtml(r.phone) + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Institution:</span>' +
                                        '<span>' + escapeHtml(r.institution) + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Department:</span>' +
                                        '<span>' + escapeHtml(r.department) + '</span>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<div class="card bg-body-tertiary border-0 h-100">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold"><i class="bi bi-mortarboard me-2"></i>Academic Background</div>' +
                            '<div class="card-body">' +
                                '<ul class="list-group list-group-flush bg-transparent">' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Qualification:</span>' +
                                        '<span>' + escapeHtml(r.qualification) + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Expertise Area:</span>' +
                                        '<span>' + escapeHtml(r.expertise_area) + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">ORCID ID:</span>' +
                                        '<span>' + (r.orcid_id ? ('<a href="https://orcid.org/' + encodeURIComponent(r.orcid_id) + '" target="_blank">' + escapeHtml(r.orcid_id) + '</a>') : '<span class="text-muted">N/A</span>') + '</span>' +
                                    '</li>' +
                                    '<li class="list-group-item bg-transparent d-flex justify-content-between px-0">' +
                                        '<span class="text-muted">Google Scholar:</span>' +
                                        '<span>' + (r.google_scholar ? ('<a href="' + escapeHtml(r.google_scholar) + '" target="_blank">View Profile</a>') : '<span class="text-muted">N/A</span>') + '</span>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            if (r.biography || r.research_interests) {
                html += '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-body">' +
                        (r.biography ? ('<h6 class="fw-bold mb-2">Biography</h6><p class="text-body mb-3">' + nl2br(r.biography) + '</p>') : '') +
                        (r.research_interests ? ('<h6 class="fw-bold mb-2">Research Interests</h6><p class="text-body mb-0">' + nl2br(r.research_interests) + '</p>') : '') +
                    '</div>' +
                '</div>';
            }

            html += '</div>'; // End Tab 3

            // TAB 4: Milestones
            html += '<div class="tab-pane fade" id="milestones-pane" role="tabpanel">' +
                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<h6 class="fw-bold mb-0"><i class="bi bi-flag me-2"></i>Project Milestones & Deliverables</h6>' +
                    '<a href="<?= SITE_URL ?>grant_manager/pages/milestones.php?award_id=' + a.id + '" class="btn btn-sm btn-primary">' +
                        '<i class="bi bi-gear me-1"></i>Manage Milestones' +
                    '</a>' +
                '</div>';

            if (ms.items.length === 0) {
                html += '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-body text-center py-5 text-muted">' +
                        '<i class="bi bi-signpost-split fs-2 d-block mb-2 text-secondary"></i>' +
                        '<p class="mb-3">No milestones configured for this project yet.</p>' +
                        '<a href="<?= SITE_URL ?>grant_manager/pages/milestones.php?award_id=' + a.id + '" class="btn btn-sm btn-primary">' +
                            '<i class="bi bi-plus-circle me-1"></i>Create First Milestone' +
                        '</a>' +
                    '</div>' +
                '</div>';
            } else {
                html += '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-body p-0">' +
                        '<div class="table-responsive">' +
                            '<table class="table table-hover mb-0">' +
                                '<thead class="table-light">' +
                                    '<tr>' +
                                        '<th>#</th>' +
                                        '<th>Milestone Title</th>' +
                                        '<th>Due Date</th>' +
                                        '<th>Completion Date</th>' +
                                        '<th>Deliverables</th>' +
                                        '<th>Status</th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody>';
                ms.items.forEach(function(m, idx) {
                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td>' +
                            '<strong>' + escapeHtml(m.title) + '</strong>' +
                            (m.description ? ('<small class="d-block text-muted">' + escapeHtml(m.description) + '</small>') : '') +
                        '</td>' +
                        '<td>' + m.due_date + '</td>' +
                        '<td>' + (m.completion_date || '-') + '</td>' +
                        '<td>' + (m.deliverables ? escapeHtml(m.deliverables) : '-') + '</td>' +
                        '<td>' + m.status_badge + '</td>' +
                    '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }
            html += '</div>'; // End Tab 4

            // TAB 5: Progress Reports
            html += '<div class="tab-pane fade" id="reports-pane" role="tabpanel">' +
                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Submitted Progress & Final Reports</h6>' +
                    '<a href="<?= SITE_URL ?>grant_manager/pages/progress_reviews.php" class="btn btn-sm btn-outline-primary">' +
                        '<i class="bi bi-clipboard-check me-1"></i>Progress Reviews' +
                    '</a>' +
                '</div>';

            if (reps.items.length === 0 && !fr) {
                html += '<div class="card bg-body-tertiary border-0">' +
                    '<div class="card-body text-center py-5 text-muted">' +
                        '<i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>' +
                        '<p class="mb-0">No progress reports or final reports have been submitted by the researcher yet.</p>' +
                    '</div>' +
                '</div>';
            } else {
                if (reps.items.length > 0) {
                    html += '<div class="d-flex flex-column gap-3 mb-4">';
                    reps.items.forEach(function(rItem) {
                        html += '<div class="card bg-body-tertiary border-0 shadow-sm">' +
                            '<div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">' +
                                '<div>' +
                                    '<span class="badge bg-secondary me-2">' + escapeHtml(rItem.report_type_label) + '</span>' +
                                    '<strong class="fs-6">' + escapeHtml(rItem.title) + '</strong>' +
                                '</div>' +
                                '<div>' + rItem.status_badge + '</div>' +
                            '</div>' +
                            '<div class="card-body">' +
                                '<div class="row g-2 mb-3 text-muted small">' +
                                    '<div class="col-sm-6"><i class="bi bi-calendar-range me-1"></i>Period: ' + rItem.report_period_start + ' to ' + rItem.report_period_end + '</div>' +
                                    '<div class="col-sm-6"><i class="bi bi-send me-1"></i>Submitted: ' + rItem.submitted_at + '</div>' +
                                '</div>' +
                                (rItem.summary ? ('<div class="mb-2"><strong class="d-block small text-muted">Summary:</strong><p class="mb-0">' + nl2br(rItem.summary) + '</p></div>') : '') +
                                (rItem.achievements ? ('<div class="mb-2"><strong class="d-block small text-muted">Key Achievements:</strong><p class="mb-0">' + nl2br(rItem.achievements) + '</p></div>') : '') +
                                (rItem.challenges ? ('<div class="mb-2"><strong class="d-block small text-muted">Challenges & Solutions:</strong><p class="mb-0">' + nl2br(rItem.challenges) + '</p></div>') : '') +
                                (rItem.financial_update ? ('<div class="mb-2"><strong class="d-block small text-muted">Financial Update:</strong><p class="mb-0">' + nl2br(rItem.financial_update) + '</p></div>') : '') +
                                (rItem.review_comment ? ('<div class="mt-3 p-2 bg-dark bg-opacity-25 rounded"><strong class="d-block small text-warning"><i class="bi bi-chat-left-quote me-1"></i>Manager Review Comment:</strong><p class="mb-0 small">' + escapeHtml(rItem.review_comment) + '</p></div>') : '') +
                                (rItem.download_url ? ('<div class="mt-3"><a href="' + rItem.download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download><i class="bi bi-file-earmark-arrow-down me-1"></i>Download Report Attachment</a></div>') : '') +
                            '</div>' +
                        '</div>';
                    });
                    html += '</div>';
                }

                if (fr) {
                    html += '<div class="card bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">' +
                        '<div class="card-header bg-transparent border-bottom border-success border-opacity-25 d-flex justify-content-between align-items-center py-3">' +
                            '<div>' +
                                '<span class="badge bg-success me-2">Final Report</span>' +
                                '<strong class="fs-6">' + escapeHtml(fr.title) + '</strong>' +
                            '</div>' +
                            '<div>' + fr.status_badge + '</div>' +
                        '</div>' +
                        '<div class="card-body">' +
                            '<small class="text-muted d-block mb-2"><i class="bi bi-send me-1"></i>Submitted on: ' + fr.submitted_at + '</small>' +
                            (fr.executive_summary ? ('<div class="mb-2"><strong class="d-block small text-muted">Executive Summary:</strong><p class="mb-0">' + nl2br(fr.executive_summary) + '</p></div>') : '') +
                            (fr.results ? ('<div class="mb-2"><strong class="d-block small text-muted">Results:</strong><p class="mb-0">' + nl2br(fr.results) + '</p></div>') : '') +
                            (fr.download_url ? ('<div class="mt-3"><a href="' + fr.download_url + '" class="btn btn-sm btn-success" target="_blank" download><i class="bi bi-download me-1"></i>Download Complete Final Report</a></div>') : '') +
                        '</div>' +
                    '</div>';
                }
            }
            html += '</div>'; // End Tab 5

            html += '</div>'; // End Tab Content

            $content.html(html);
        },
        error: function(xhr) {
            var msg = 'Failed to load project details. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $content.html(
                '<div class="alert alert-danger my-4">' +
                    '<i class="bi bi-exclamation-octagon me-2"></i>' + escapeHtml(msg) +
                '</div>'
            );
        }
    });
}

function printProjectDetails() {
    var title = $('#viewProjectModalLabel').text();
    var content = document.getElementById('projectDetailContent').innerHTML;
    var printWindow = window.open('', '_blank');
    printWindow.document.write(
        '<!DOCTYPE html><html><head><title>' + escapeHtml(title) + '</title>' +
        '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">' +
        '<style>body { font-family: sans-serif; padding: 20px; color: #111; background: #fff !important; } .card { background: #fff !important; color: #111 !important; border: 1px solid #ddd !important; } .tab-content > .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; margin-bottom: 24px; } .nav-tabs { display: none !important; } a { text-decoration: none; color: #0d6efd; }</style>' +
        '</head><body>' +
        '<h3 class="mb-1">' + escapeHtml(title) + '</h3>' +
        '<p class="text-muted mb-4">' + document.getElementById('viewProjectSubtitle').innerText + '</p>' +
        '<hr>' +
        content +
        '<script>window.onload = function() { window.print(); }<\/script>' +
        '</body></html>'
    );
    printWindow.document.close();
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function nl2br(str) {
    if (!str) return '<span class="text-muted fst-italic">Not provided</span>';
    return escapeHtml(str).replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
}

// Auto open modal if ?id=X or ?view=X is in URL
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var autoId = urlParams.get('id') || urlParams.get('view') || urlParams.get('award_id');
    if (autoId && !isNaN(autoId)) {
        viewProject(parseInt(autoId));
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
