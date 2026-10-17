<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Grant Awards & Project Monitoring';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_award'])) {
    requireCSRF();
    $award_id = (int)$_POST['award_id'];
    $status = sanitizeInput($_POST['status']);
    $end_date = sanitizeInput($_POST['end_date']) ?: null;
    $stmt = $db->prepare("UPDATE grant_awards SET status = :status, end_date = COALESCE(:end_date, end_date) WHERE id = :id");
    $stmt->execute([':status' => $status, ':end_date' => $end_date, ':id' => $award_id]);
    logAudit($_SESSION['user_id'], 'update', 'grant_awards', $award_id, 'Updated award status to ' . $status);
    $_SESSION['success_message'] = 'Award updated successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/awards.php');
    exit();
}

$stmt = $db->query("SELECT ga.*, u.first_name, u.last_name, u.email, g.title as grant_title, p.title as proposal_title, (SELECT COUNT(*) FROM project_milestones pm WHERE pm.award_id = ga.id) as milestone_count, (SELECT COUNT(*) FROM project_milestones pm WHERE pm.award_id = ga.id AND pm.status = 'completed') as completed_milestones, (SELECT COUNT(*) FROM progress_reports pr WHERE pr.award_id = ga.id) as report_count FROM grant_awards ga JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id JOIN proposals p ON ga.proposal_id = p.id ORDER BY ga.created_at DESC");
$awards = $stmt->fetchAll();

$counts = [
    'all' => count($awards),
    'active' => 0,
    'completed' => 0,
    'suspended' => 0,
    'terminated' => 0,
];
foreach ($awards as $a) {
    if (isset($counts[$a['status']])) {
        $counts[$a['status']]++;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Grant Awards & Project Monitoring</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Awards & Projects</li>
            </ol>
        </nav>
    </div>
</div>

<?= displaySessionMessages() ?>

<!-- Filter Tabs -->
<div class="mb-3 d-flex flex-wrap gap-2">
    <button class="btn btn-sm btn-primary award-filter-btn active" data-filter="all">All Awards <span class="badge bg-white text-primary ms-1"><?= $counts['all'] ?></span></button>
    <button class="btn btn-sm btn-outline-success award-filter-btn" data-filter="active">Active <span class="badge bg-success ms-1"><?= $counts['active'] ?></span></button>
    <button class="btn btn-sm btn-outline-primary award-filter-btn" data-filter="completed">Completed <span class="badge bg-primary ms-1"><?= $counts['completed'] ?></span></button>
    <button class="btn btn-sm btn-outline-warning award-filter-btn" data-filter="suspended">Suspended <span class="badge bg-warning text-dark ms-1"><?= $counts['suspended'] ?></span></button>
    <button class="btn btn-sm btn-outline-danger award-filter-btn" data-filter="terminated">Terminated <span class="badge bg-danger ms-1"><?= $counts['terminated'] ?></span></button>
</div>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-award text-primary me-2"></i>Funded Research Projects</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover align-middle" id="awardsTable">
                <thead class="table-light">
                    <tr>
                        <th>Researcher</th>
                        <th>Project & Grant</th>
                        <th>Awarded Amount</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th class="no-print text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($awards)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No awards found</td></tr>
                    <?php else: ?>
                    <?php foreach ($awards as $a): ?>
                    <?php
                    $pct = $a['milestone_count'] > 0 ? round(($a['completed_milestones'] / $a['milestone_count']) * 100) : 0;
                    ?>
                    <tr data-status="<?= htmlspecialchars($a['status']) ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($a['first_name'] . ' ' . $a['last_name']) ?>" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($a['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="javascript:void(0)" onclick="viewProject(<?= $a['id'] ?>)" class="fw-medium text-decoration-none">
                                <?= htmlspecialchars($a['proposal_title']) ?>
                            </a>
                            <small class="text-muted d-block"><i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($a['grant_title']) ?></small>
                        </td>
                        <td><span class="fw-bold text-success"><?= formatAmount($a['awarded_amount']) ?></span></td>
                        <td style="min-width: 140px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted"><?= $a['completed_milestones'] ?>/<?= $a['milestone_count'] ?> Milestones</small>
                                <small class="fw-bold text-primary"><?= $pct ?>%</small>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                            </div>
                            <?php if ($a['report_count'] > 0): ?>
                                <small class="text-muted d-block mt-1"><i class="bi bi-file-text me-1"></i><?= $a['report_count'] ?> report(s)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= getStatusBadge($a['status']) ?></td>
                        <td>
                            <small class="d-block text-muted">From: <?= formatDate($a['award_date']) ?></small>
                            <small class="d-block text-muted">To: <?= $a['end_date'] ? formatDate($a['end_date']) : '-' ?></small>
                        </td>
                        <td class="no-print text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info" onclick="viewProject(<?= $a['id'] ?>)" title="View Full Project Dossier & Monitoring">
                                    <i class="bi bi-eye me-1"></i>Details
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="editAward(<?= $a['id'] ?>)" title="Manage Status & Dates">
                                    <i class="bi bi-gear"></i>
                                </button>
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

<!-- Manage Award Status Modal -->
<div class="modal fade" id="editAwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="update_award" value="1">
                <input type="hidden" name="award_id" id="edit_award_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Manage Award Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Project Status</label>
                        <select name="status" id="edit_award_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="suspended">Suspended</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Project End Date</label>
                        <input type="date" name="end_date" id="edit_award_end" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Full Project Monitoring Dossier Modal -->
<div class="modal fade" id="viewProjectModal" tabindex="-1" aria-labelledby="viewProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="viewProjectModalLabel">Project Details</h5>
                    <small class="text-white-50" id="viewProjectSubtitle"></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="projectStatusBadge"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="projectDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h6 class="text-muted fw-normal">Loading project details...</h6>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div id="projectModalFooterLinks"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var awardsData = <?= json_encode($awards) ?>;

function editAward(id) {
    var award = awardsData.find(function(a) { return a.id == id; });
    if (award) {
        $('#edit_award_id').val(award.id);
        $('#edit_award_status').val(award.status);
        $('#edit_award_end').val(award.end_date || '');
        $('#editAwardModal').modal('show');
    }
}

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
            '<div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>' +
            '<h6 class="text-muted fw-normal">Loading full project monitoring details...</h6>' +
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

            $footerLinks.html(
                '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">' +
                    '<i class="bi bi-printer me-1"></i>Print Summary' +
                '</button>'
            );

            // KPI stats
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
            html += '<ul class="nav nav-tabs mb-4" id="adminProjectDetailsTabs" role="tablist">' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link active" id="adm-proposal-tab" data-bs-toggle="tab" data-bs-target="#adm-proposal-pane" type="button" role="tab">' +
                        '<i class="bi bi-file-earmark-text me-1"></i>Project Proposal' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="adm-budget-tab" data-bs-toggle="tab" data-bs-target="#adm-budget-pane" type="button" role="tab">' +
                        '<i class="bi bi-cash-coin me-1"></i>Budget & Award' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="adm-researcher-tab" data-bs-toggle="tab" data-bs-target="#adm-researcher-pane" type="button" role="tab">' +
                        '<i class="bi bi-person-badge me-1"></i>Researcher Info' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="adm-milestones-tab" data-bs-toggle="tab" data-bs-target="#adm-milestones-pane" type="button" role="tab">' +
                        '<i class="bi bi-signpost-2 me-1"></i>Milestones <span class="badge bg-secondary ms-1">' + ms.total + '</span>' +
                    '</button>' +
                '</li>' +
                '<li class="nav-item" role="presentation">' +
                    '<button class="nav-link" id="adm-reports-tab" data-bs-toggle="tab" data-bs-target="#adm-reports-pane" type="button" role="tab">' +
                        '<i class="bi bi-clipboard-data me-1"></i>Progress Reports <span class="badge bg-secondary ms-1">' + reps.total + '</span>' +
                    '</button>' +
                '</li>' +
            '</ul>';

            html += '<div class="tab-content" id="adminProjectDetailsTabsContent">';

            // Tab 1: Proposal
            html += '<div class="tab-pane fade show active" id="adm-proposal-pane" role="tabpanel">' +
                '<div class="row g-3">' +
                    '<div class="col-12">' +
                        '<div class="p-3 bg-body-tertiary rounded shadow-sm mb-3">' +
                            '<h6 class="fw-bold mb-2 text-primary"><i class="bi bi-card-text me-2"></i>Executive Abstract</h6>' +
                            '<p class="mb-0 text-body" style="white-space: pre-wrap;">' + (escapeHtml(p.abstract) || '<span class="text-muted">No abstract provided.</span>') + '</p>' +
                        '</div>' +
                    '</div>';

            if (p.problem_statement) {
                html += '<div class="col-md-6">' +
                    '<div class="p-3 bg-body-tertiary rounded shadow-sm h-100">' +
                        '<h6 class="fw-bold mb-2 text-primary"><i class="bi bi-exclamation-diamond me-2"></i>Problem Statement</h6>' +
                        '<p class="mb-0 small text-body" style="white-space: pre-wrap;">' + escapeHtml(p.problem_statement) + '</p>' +
                    '</div>' +
                '</div>';
            }

            if (p.methodology) {
                html += '<div class="col-md-6">' +
                    '<div class="p-3 bg-body-tertiary rounded shadow-sm h-100">' +
                        '<h6 class="fw-bold mb-2 text-primary"><i class="bi bi-gear-wide-connected me-2"></i>Methodology</h6>' +
                        '<p class="mb-0 small text-body" style="white-space: pre-wrap;">' + escapeHtml(p.methodology) + '</p>' +
                    '</div>' +
                '</div>';
            }

            if (p.expected_outcomes) {
                html += '<div class="col-12">' +
                    '<div class="p-3 bg-body-tertiary rounded shadow-sm">' +
                        '<h6 class="fw-bold mb-2 text-primary"><i class="bi bi-bullseye me-2"></i>Expected Outcomes & Impact</h6>' +
                        '<p class="mb-0 small text-body" style="white-space: pre-wrap;">' + escapeHtml(p.expected_outcomes) + '</p>' +
                    '</div>' +
                '</div>';
            }

            // Attached Documents
            html += '<div class="col-12 mt-3">' +
                '<h6 class="fw-bold mb-2"><i class="bi bi-paperclip me-1"></i>Attached Proposal Documents (' + docs.length + ')</h6>';
            if (docs.length === 0) {
                html += '<p class="text-muted small">No documents were uploaded for this proposal.</p>';
            } else {
                html += '<div class="list-group list-group-flush border rounded">';
                docs.forEach(function(d) {
                    html += '<div class="list-group-item d-flex justify-content-between align-items-center py-2">' +
                        '<div class="d-flex align-items-center">' +
                            '<i class="' + d.file_icon + ' fs-4 text-primary me-3"></i>' +
                            '<div>' +
                                '<div class="fw-medium">' + escapeHtml(d.document_type || d.file_name) + '</div>' +
                                '<small class="text-muted">' + escapeHtml(d.file_name) + ' &bull; ' + d.file_size_formatted + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<a href="' + d.download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download>' +
                            '<i class="bi bi-download me-1"></i>Download' +
                        '</a>' +
                    '</div>';
                });
                html += '</div>';
            }
            html += '</div></div></div>'; // end tab 1

            // Tab 2: Budget
            html += '<div class="tab-pane fade" id="adm-budget-pane" role="tabpanel">' +
                '<div class="row g-3">' +
                    '<div class="col-md-6">' +
                        '<div class="card border-0 bg-body-tertiary shadow-sm">' +
                            '<div class="card-header bg-transparent fw-bold py-2">Funding Breakdown</div>' +
                            '<div class="card-body">' +
                                '<table class="table table-sm table-borderless mb-0">' +
                                    '<tr><td class="text-muted">Grant Opportunity:</td><td class="fw-semibold text-end">' + escapeHtml(g.title) + '</td></tr>' +
                                    '<tr><td class="text-muted">Total Grant Budget:</td><td class="fw-semibold text-end">' + g.funding_amount_formatted + '</td></tr>' +
                                    '<tr><td class="text-muted">Researcher Requested:</td><td class="fw-semibold text-end">' + p.budget_amount_formatted + '</td></tr>' +
                                    '<tr class="border-top"><td class="fw-bold text-success">Official Award Amount:</td><td class="fw-bold text-success text-end fs-6">' + a.awarded_amount_formatted + '</td></tr>' +
                                    '<tr><td class="text-muted">Disbursed to Date:</td><td class="fw-semibold text-end">' + a.disbursed_amount_formatted + '</td></tr>' +
                                    '<tr><td class="text-muted">Remaining Balance:</td><td class="fw-semibold text-end text-primary">' + a.remaining_balance_formatted + '</td></tr>' +
                                '</table>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<div class="card border-0 bg-body-tertiary shadow-sm h-100">' +
                            '<div class="card-header bg-transparent fw-bold py-2">Itemized Budget Narrative</div>' +
                            '<div class="card-body">' +
                                '<p class="small text-body mb-0" style="white-space: pre-wrap;">' + (escapeHtml(p.budget_breakdown) || '<span class="text-muted">No budget breakdown detailed.</span>') + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            // Tab 3: Researcher Info
            html += '<div class="tab-pane fade" id="adm-researcher-pane" role="tabpanel">' +
                '<div class="card border-0 bg-body-tertiary shadow-sm">' +
                    '<div class="card-body p-4">' +
                        '<div class="d-flex align-items-center mb-4">' +
                            '<img src="' + r.avatar_url + '" class="rounded-circle me-3 border" width="70" height="70" alt="Avatar">' +
                            '<div>' +
                                '<h5 class="fw-bold mb-0">' + escapeHtml(r.name) + '</h5>' +
                                '<p class="text-muted mb-1">' + escapeHtml(r.department || '') + (r.department && r.institution ? ' &bull; ' : '') + escapeHtml(r.institution || '') + '</p>' +
                                '<span class="badge bg-primary-subtle text-primary">' + escapeHtml(r.qualification || 'Researcher') + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="row g-3 small">' +
                            '<div class="col-sm-6"><strong class="text-muted d-block">Email Address:</strong><a href="mailto:' + escapeHtml(r.email) + '">' + escapeHtml(r.email) + '</a></div>' +
                            '<div class="col-sm-6"><strong class="text-muted d-block">Phone:</strong>' + (escapeHtml(r.phone) || '-') + '</div>' +
                            '<div class="col-sm-6"><strong class="text-muted d-block">Specialization / Expertise:</strong>' + (escapeHtml(r.expertise_area) || '-') + '</div>' +
                            '<div class="col-sm-6"><strong class="text-muted d-block">ORCID ID:</strong>' + (r.orcid_id ? '<a href="https://orcid.org/' + escapeHtml(r.orcid_id) + '" target="_blank">' + escapeHtml(r.orcid_id) + '</a>' : '-') + '</div>' +
                        '</div>';
            if (r.cv_download_url) {
                html += '<div class="mt-4 pt-3 border-top">' +
                    '<a href="' + r.cv_download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download>' +
                        '<i class="bi bi-file-earmark-person me-1"></i>Download Curriculum Vitae (CV)' +
                    '</a>' +
                '</div>';
            }
            html += '</div></div></div>';

            // Tab 4: Milestones
            html += '<div class="tab-pane fade" id="adm-milestones-pane" role="tabpanel">';
            if (ms.items.length === 0) {
                html += '<div class="text-center py-4 text-muted">' +
                    '<i class="bi bi-signpost-2 fs-1 d-block mb-2 text-secondary"></i>' +
                    '<p class="mb-0">No project milestones registered yet.</p>' +
                '</div>';
            } else {
                html += '<div class="table-responsive">' +
                    '<table class="table table-hover align-middle">' +
                        '<thead class="table-light">' +
                            '<tr>' +
                                '<th>Milestone</th>' +
                                '<th>Due Date</th>' +
                                '<th>Completed Date</th>' +
                                '<th>Status</th>' +
                                '<th>Verification Note</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';
                ms.items.forEach(function(item) {
                    html += '<tr>' +
                        '<td>' +
                            '<div class="fw-semibold">' + escapeHtml(item.title) + '</div>' +
                            '<small class="text-muted">' + escapeHtml(item.description || '') + '</small>' +
                        '</td>' +
                        '<td>' + item.due_date_formatted + '</td>' +
                        '<td>' + item.completed_date_formatted + '</td>' +
                        '<td>' + item.status_badge + '</td>' +
                        '<td><small class="text-muted">' + (escapeHtml(item.verification_notes) || '-') + '</small></td>' +
                    '</tr>';
                });
                html += '</tbody></table></div>';
            }
            html += '</div>';

            // Tab 5: Progress Reports & Final Report
            html += '<div class="tab-pane fade" id="adm-reports-pane" role="tabpanel">';
            if (reps.items.length === 0 && !fr) {
                html += '<div class="text-center py-4 text-muted">' +
                    '<i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>' +
                    '<p class="mb-0">No progress reports or final reports submitted yet.</p>' +
                '</div>';
            } else {
                if (fr) {
                    html += '<div class="card border-success mb-3 shadow-sm">' +
                        '<div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">' +
                            '<span><i class="bi bi-award-fill me-2"></i>Final Research Report</span>' +
                            '<span class="badge bg-light text-success">Submitted</span>' +
                        '</div>' +
                        '<div class="card-body">' +
                            '<h6 class="fw-bold mb-1">' + escapeHtml(fr.title || 'Final Project Report') + '</h6>' +
                            '<small class="text-muted d-block mb-2">Submitted: ' + fr.submitted_at_formatted + '</small>' +
                            '<p class="small text-body mb-3">' + escapeHtml(fr.executive_summary || fr.description || '') + '</p>';
                    if (fr.file_download_url) {
                        html += '<a href="' + fr.file_download_url + '" class="btn btn-sm btn-success" target="_blank" download>' +
                            '<i class="bi bi-download me-1"></i>Download Complete Final Report' +
                        '</a>';
                    }
                    html += '</div></div>';
                }

                if (reps.items.length > 0) {
                    html += '<h6 class="fw-bold mb-3"><i class="bi bi-journal-text me-1 text-primary"></i>Submitted Periodic Progress Reports (' + reps.total + ')</h6>' +
                        '<div class="list-group">';
                    reps.items.forEach(function(rep) {
                        html += '<div class="list-group-item list-group-item-action p-3 mb-2 border rounded shadow-sm">' +
                            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                '<h6 class="fw-bold mb-0">' + escapeHtml(rep.title) + '</h6>' +
                                '<span class="badge bg-primary-subtle text-primary">' + rep.report_date_formatted + '</span>' +
                            '</div>' +
                            '<small class="text-muted d-block mb-2">Submitted on ' + rep.submitted_at_formatted + '</small>' +
                            '<p class="small text-body mb-2" style="white-space: pre-wrap;">' + escapeHtml(rep.summary || rep.activities_completed || '') + '</p>';
                        if (rep.file_download_url) {
                            html += '<a href="' + rep.file_download_url + '" class="btn btn-sm btn-outline-primary" target="_blank" download>' +
                                '<i class="bi bi-file-earmark-arrow-down me-1"></i>Download Report Attachment' +
                            '</a>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                }
            }
            html += '</div>'; // end tab 5

            html += '</div>'; // end tab-content

            $content.html(html);
        },
        error: function() {
            $content.html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to communicate with server.</div>');
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return $('<div>').text(text).html();
}

$(document).ready(function() {
    $('.award-filter-btn').on('click', function() {
        $('.award-filter-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        
        let filter = $(this).data('filter');
        if (filter === 'all') {
            $('#awardsTable tbody tr').show();
        } else {
            $('#awardsTable tbody tr').each(function() {
                if ($(this).data('status') === filter) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const awardId = urlParams.get('id');
    if (awardId) {
        viewProject(awardId);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

