<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Manage Grants';

$stmt = $db->query("SELECT g.*, gc.name as category_name, gc.icon as category_icon, gc.color as category_color, (SELECT COUNT(*) FROM proposals WHERE grant_id = g.id) as total_applications, (SELECT COUNT(*) FROM proposals WHERE grant_id = g.id AND status = 'approved') as approved_applications FROM grants g LEFT JOIN grant_categories gc ON g.category_id = gc.id ORDER BY g.created_at DESC");
$grants = $stmt->fetchAll();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT title FROM grants WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $grant = $stmt->fetch();
    if ($grant) {
        $stmt = $db->prepare("DELETE FROM grants WHERE id = :id");
        $stmt->execute([':id' => $id]);
        logAudit($_SESSION['user_id'], 'delete', 'grants', $id, 'Deleted grant: ' . $grant['title']);
        $_SESSION['success_message'] = 'Grant deleted successfully.';
    }
    header('Location: ' . SITE_URL . 'admin/pages/manage_grants.php');
    exit();
}

$counts = [
    'all' => count($grants),
    'open' => 0,
    'draft' => 0,
    'closed' => 0,
    'cancelled' => 0,
];
foreach ($grants as $g) {
    if (isset($counts[$g['status']])) {
        $counts[$g['status']]++;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold">Manage Grants</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Manage Grants</li>
            </ol>
        </nav>
    </div>
    <a href="<?= SITE_URL ?>admin/pages/create_grant.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Grant
    </a>
</div>

<?= displaySessionMessages() ?>

<!-- Filter Tabs -->
<div class="mb-3 d-flex flex-wrap gap-2">
    <button class="btn btn-sm btn-primary grant-filter-btn active" data-filter="all">All Grants <span class="badge bg-white text-primary ms-1"><?= $counts['all'] ?></span></button>
    <button class="btn btn-sm btn-outline-success grant-filter-btn" data-filter="open">Open <span class="badge bg-success ms-1"><?= $counts['open'] ?></span></button>
    <button class="btn btn-sm btn-outline-secondary grant-filter-btn" data-filter="draft">Draft <span class="badge bg-secondary ms-1"><?= $counts['draft'] ?></span></button>
    <button class="btn btn-sm btn-outline-warning grant-filter-btn" data-filter="closed">Closed <span class="badge bg-warning text-dark ms-1"><?= $counts['closed'] ?></span></button>
    <button class="btn btn-sm btn-outline-danger grant-filter-btn" data-filter="cancelled">Cancelled <span class="badge bg-danger ms-1"><?= $counts['cancelled'] ?></span></button>
</div>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-folder2-open text-primary me-2"></i>All Grant Opportunities</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover align-middle" id="grantsTable">
                <thead class="table-light">
                    <tr>
                        <th>Grant Opportunity</th>
                        <th>Category</th>
                        <th>Funding Amount</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th>Application Period</th>
                        <th class="no-print text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grants)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No grants found</td></tr>
                    <?php else: ?>
                    <?php foreach ($grants as $g): ?>
                    <tr data-status="<?= htmlspecialchars($g['status']) ?>">
                        <td>
                            <a href="javascript:void(0)" onclick="viewGrant(<?= $g['id'] ?>)" class="fw-bold text-decoration-none">
                                <?= htmlspecialchars($g['title']) ?>
                            </a>
                            <?php if (!empty($g['guidelines_file'])): ?>
                                <small class="text-muted d-block"><i class="bi bi-paperclip me-1 text-primary"></i>Guidelines Attached</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($g['category_name'] ?? 'Uncategorized') ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-success fs-6">
                                <?= $g['funding_amount'] ? formatAmount($g['funding_amount']) : 'N/A' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-file-earmark-text me-1"></i><?= $g['total_applications'] ?> applied
                            </span>
                            <?php if ($g['approved_applications'] > 0): ?>
                                <small class="d-block text-success fw-medium"><?= $g['approved_applications'] ?> approved</small>
                            <?php endif; ?>
                        </td>
                        <td><?= getStatusBadge($g['status']) ?></td>
                        <td>
                            <small class="d-block text-muted">From: <?= $g['application_start_date'] ? formatDate($g['application_start_date']) : 'N/A' ?></small>
                            <small class="d-block text-muted">To: <strong><?= $g['application_end_date'] ? formatDate($g['application_end_date']) : 'No deadline' ?></strong></small>
                        </td>
                        <td class="no-print text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info" onclick="viewGrant(<?= $g['id'] ?>)" title="View Complete Grant Details & Applications">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                                <a href="<?= SITE_URL ?>admin/pages/edit_grant.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Grant">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= SITE_URL ?>admin/pages/manage_grants.php?delete=<?= $g['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-message="Are you sure you want to delete grant '<?= htmlspecialchars(addslashes($g['title'])) ?>'?" title="Delete Grant">
                                    <i class="bi bi-trash"></i>
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

<!-- Detailed Grant Dossier Modal -->
<div class="modal fade" id="viewGrantModal" tabindex="-1" aria-labelledby="grantModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="grantModalTitle">Grant Details</h5>
                    <small class="text-white-50" id="grantModalSubtitle"></small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="grantModalBadge"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" id="grantDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading complete grant specifications...</p>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print Grant Dossier
                    </button>
                </div>
                <div id="grantModalFooterActions" class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewGrant(id) {
    $('#grantModalTitle').text('Loading Grant #' + id + '...');
    $('#grantModalSubtitle').text('');
    $('#grantModalBadge').html('');
    $('#grantModalFooterActions').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');
    
    $('#grantDetailBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading complete grant specifications, guidelines, and applications...</p>
        </div>
    `);

    $('#viewGrantModal').modal('show');

    $.get('<?= SITE_URL ?>admin/ajax/handle_users.php?action=get_grant&id=' + id, function(data) {
        if (typeof data === 'string') {
            try { data = JSON.parse(data); } catch(e){}
        }

        $('#grantModalTitle').text(data.title || 'Grant Details');
        $('#grantModalSubtitle').html('Category: <span class="badge bg-secondary">' + escapeHtml(data.category_name || 'General') + '</span> &bull; Created by: ' + escapeHtml(data.creator_name || 'Admin'));
        $('#grantModalBadge').html(data.status_badge || '');

        let stats = data.stats || {};
        let apps = data.applications || [];

        let html = `
            <!-- Top Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-body-tertiary border-0 h-100 shadow-sm p-3">
                        <small class="text-muted d-block mb-1">Total Available Funding</small>
                        <h5 class="fw-bold text-success mb-0">${data.funding_amount_formatted}</h5>
                        <small class="text-muted">Awarded: ${stats.awarded_total_formatted || '<?= CURRENCY_SYMBOL ?>0.00'}</small>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-body-tertiary border-0 h-100 shadow-sm p-3">
                        <small class="text-muted d-block mb-1">Applications Received</small>
                        <h5 class="fw-bold text-primary mb-0">${stats.total || 0}</h5>
                        <small class="text-muted">Cap: ${data.max_applications ? data.max_applications + ' max' : 'Unlimited'}</small>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-body-tertiary border-0 h-100 shadow-sm p-3">
                        <small class="text-muted d-block mb-1">Application Deadline</small>
                        <h6 class="fw-bold mb-0 text-danger">${data.application_end_date_formatted || 'Open'}</h6>
                        <small class="text-muted">Start: ${data.application_start_date_formatted || 'N/A'}</small>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card bg-body-tertiary border-0 h-100 shadow-sm p-3">
                        <small class="text-muted d-block mb-1">Review Deadline</small>
                        <h6 class="fw-bold mb-0 text-warning">${data.review_deadline_formatted || 'N/A'}</h6>
                        <small class="text-muted">Status: ${data.status ? data.status.toUpperCase() : 'N/A'}</small>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="grantDetailsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">
                        <i class="bi bi-info-circle me-1"></i>Overview & Guidelines
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="apps-tab" data-bs-toggle="tab" data-bs-target="#apps-pane" type="button" role="tab">
                        <i class="bi bi-file-earmark-text me-1"></i>Submitted Applications (${apps.length})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="grantDetailsTabsContent">
                <!-- Tab 1: Overview -->
                <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <!-- Description -->
                            <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
                                <div class="card-header bg-transparent fw-bold py-2 text-primary">
                                    <i class="bi bi-card-text me-2"></i>Detailed Description
                                </div>
                                <div class="card-body">
                                    <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.description || 'No detailed description provided.')}</p>
                                </div>
                            </div>

                            <!-- Objectives -->
                            ${data.objectives ? `
                            <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
                                <div class="card-header bg-transparent fw-bold py-2 text-primary">
                                    <i class="bi bi-bullseye me-2"></i>Key Objectives & Expected Impact
                                </div>
                                <div class="card-body">
                                    <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.objectives)}</p>
                                </div>
                            </div>` : ''}

                            <!-- Eligibility -->
                            ${data.eligibility_criteria ? `
                            <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
                                <div class="card-header bg-transparent fw-bold py-2 text-primary">
                                    <i class="bi bi-check2-square me-2"></i>Eligibility Criteria & Requirements
                                </div>
                                <div class="card-body">
                                    <p class="mb-0 text-secondary" style="white-space: pre-wrap;">${escapeHtml(data.eligibility_criteria)}</p>
                                </div>
                            </div>` : ''}
                        </div>

                        <div class="col-lg-4">
                            <!-- Official Guidelines Document -->
                            <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
                                <div class="card-header bg-transparent fw-bold py-2 text-primary">
                                    <i class="bi bi-file-earmark-arrow-down me-2"></i>Grant Guidelines
                                </div>
                                <div class="card-body">
                                    ${data.guidelines_download_url ? `
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="${data.guidelines_icon} fs-1 text-primary me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-truncate" style="max-width: 200px;">${escapeHtml(data.guidelines_file)}</h6>
                                                <small class="text-muted">Official Document</small>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <a href="${data.guidelines_download_url}" class="btn btn-outline-primary btn-sm" target="_blank" download>
                                                <i class="bi bi-download me-1"></i>Download Guidelines Document
                                            </a>
                                        </div>
                                    ` : `
                                        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>No guideline attachment uploaded for this grant.</p>
                                    `}
                                </div>
                            </div>

                            <!-- Metadata -->
                            <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
                                <div class="card-header bg-transparent fw-bold py-2 text-primary">
                                    <i class="bi bi-gear me-2"></i>Grant Metadata
                                </div>
                                <div class="card-body small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Grant ID:</span>
                                        <strong>#${data.id}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Category:</span>
                                        <span>${escapeHtml(data.category_name || 'N/A')}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Created By:</span>
                                        <span>${escapeHtml(data.creator_name || 'Admin')}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Created Date:</span>
                                        <span>${data.created_at_formatted || 'N/A'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Application Cap:</span>
                                        <span>${data.max_applications ? data.max_applications + ' applications' : 'No limit'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Applications List -->
                <div class="tab-pane fade" id="apps-pane" role="tabpanel">
                    <div class="card border-0 bg-body-tertiary shadow-sm">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2">
                            <span class="fw-bold text-primary"><i class="bi bi-people me-2"></i>Proposals Received (${apps.length})</span>
                            <span class="badge bg-success">Awarded: ${stats.awarded_total_formatted || '<?= CURRENCY_SYMBOL ?>0.00'}</span>
                        </div>
                        <div class="card-body p-0">
                            ${apps.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Applicant</th>
                                                <th>Proposal Title</th>
                                                <th>Budget</th>
                                                <th>Score</th>
                                                <th>Status</th>
                                                <th>Submitted</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${apps.map(a => `
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">${escapeHtml(a.researcher_name)}</div>
                                                        <small class="text-muted">${escapeHtml(a.researcher_email || '')}</small>
                                                    </td>
                                                    <td>
                                                        <a href="<?= SITE_URL ?>admin/pages/applications.php?id=${a.id}" class="fw-medium text-decoration-none" target="_blank">
                                                            ${escapeHtml(a.title)}
                                                        </a>
                                                    </td>
                                                    <td><strong class="text-primary">${a.budget_amount_formatted}</strong></td>
                                                    <td>${a.review_score !== null && a.review_score !== undefined ? `<span class="badge bg-dark">${parseFloat(a.review_score).toFixed(1)} / 100</span>` : '<span class="text-muted small">No Score</span>'}</td>
                                                    <td>${a.status_badge}</td>
                                                    <td><small class="text-muted">${a.submitted_at_formatted}</small></td>
                                                    <td class="text-end">
                                                        <a href="<?= SITE_URL ?>admin/pages/applications.php?id=${a.id}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>View Application
                                                        </a>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            ` : `
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-0">No proposals have been submitted for this grant yet.</p>
                                </div>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#grantDetailBody').html(html);

        $('#grantModalFooterActions').html(`
            <a href="<?= SITE_URL ?>admin/pages/edit_grant.php?id=${data.id}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit Grant
            </a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        `);
    }).fail(function() {
        $('#grantDetailBody').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load grant details.</div>');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return $('<div>').text(text).html();
}

$(document).ready(function() {
    $('.grant-filter-btn').on('click', function() {
        $('.grant-filter-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
        
        let filter = $(this).data('filter');
        if (filter === 'all') {
            $('#grantsTable tbody tr').show();
        } else {
            $('#grantsTable tbody tr').each(function() {
                if ($(this).data('status') === filter) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const grantId = urlParams.get('id');
    if (grantId) {
        viewGrant(grantId);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

