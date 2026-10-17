<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Fund Disbursements';

// KPI totals
$totals = $db->query("
    SELECT
        COALESCE(SUM(CASE WHEN ga.status IN ('active','completed') THEN COALESCE(ga.approved_amount, ga.awarded_amount) ELSE 0 END), 0) as total_approved,
        COALESCE(SUM(ga.total_disbursed), 0) as total_disbursed,
        COUNT(*) as total_awards,
        COUNT(CASE WHEN ga.status = 'active' THEN 1 END) as active_awards
    FROM grant_awards ga
")->fetch();

$remaining = max(0, $totals['total_approved'] - $totals['total_disbursed']);
$disbPct   = $totals['total_approved'] > 0 ? round(($totals['total_disbursed'] / $totals['total_approved']) * 100) : 0;

// All awards with disbursement info
$awards = $db->query("
    SELECT ga.*,
        u.first_name, u.last_name, u.email,
        g.title as grant_title,
        p.title as proposal_title,
        COALESCE(ga.approved_amount, ga.awarded_amount) as effective_approved,
        (SELECT COUNT(*) FROM fund_disbursements fd WHERE fd.award_id = ga.id) as disb_count
    FROM grant_awards ga
    JOIN users u ON ga.researcher_id = u.id
    JOIN grants g ON ga.grant_id = g.id
    JOIN proposals p ON ga.proposal_id = p.id
    ORDER BY ga.created_at DESC
")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Fund Disbursements</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Fund Disbursements</li>
            </ol>
        </nav>
    </div>
    <div class="text-end">
        <small class="text-muted d-block">System records disbursements only.</small>
        <small class="text-muted">Actual transfers are made by the institution.</small>
    </div>
</div>

<?= displaySessionMessages() ?>

<!-- KPI Cards -->
<div class="row g-3 mb-4" data-aos="fade-up">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-award fs-3 text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= $totals['total_awards'] ?></h5>
                        <small class="text-muted">Total Awards</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= formatAmount($totals['total_approved']) ?></h5>
                        <small class="text-muted">Total Approved Funds</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-send fs-3 text-info"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= formatAmount($totals['total_disbursed']) ?></h5>
                        <small class="text-muted">Total Disbursed</small>
                    </div>
                </div>
                <div class="progress mt-2" style="height:4px;">
                    <div class="progress-bar bg-info" style="width:<?= $disbPct ?>%"></div>
                </div>
                <small class="text-muted"><?= $disbPct ?>% disbursed</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-3">
                        <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0 fw-bold"><?= formatAmount($remaining) ?></h5>
                        <small class="text-muted">Remaining to Disburse</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notice Banner -->
<div class="alert alert-info border-0 shadow-sm mb-4" data-aos="fade-up">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Institutional Disbursement Policy:</strong> This system manages and records grant fund allocation and disbursement. Actual financial transfers are carried out by the institution or funding organisation through its authorised financial channels.
</div>

<!-- Awards Table -->
<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-table text-primary me-2"></i>Award Disbursement Tracker</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary disb-filter active" data-filter="all">All</button>
            <button class="btn btn-sm btn-outline-success disb-filter" data-filter="active">Active</button>
            <button class="btn btn-sm btn-outline-primary disb-filter" data-filter="completed">Completed</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover align-middle" id="disbTable">
                <thead class="table-light">
                    <tr>
                        <th>Researcher</th>
                        <th>Project & Grant</th>
                        <th>Approved Fund</th>
                        <th>Disbursed</th>
                        <th>Remaining</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="no-print text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($awards)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No approved awards found</td></tr>
                    <?php else: ?>
                    <?php foreach ($awards as $a):
                        $approved  = (float)$a['effective_approved'];
                        $disbursed = (float)$a['total_disbursed'];
                        $rem       = max(0, $approved - $disbursed);
                        $pct       = $approved > 0 ? round(($disbursed / $approved) * 100) : 0;
                        $barClass  = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-warning');
                    ?>
                    <tr data-status="<?= htmlspecialchars($a['status']) ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($a['first_name'] . ' ' . $a['last_name']) ?>" class="rounded-circle me-2" width="32" height="32" alt="">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($a['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium"><?= htmlspecialchars($a['proposal_title']) ?></div>
                            <small class="text-muted"><i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($a['grant_title']) ?></small>
                        </td>
                        <td class="fw-bold text-success"><?= formatAmount($approved) ?></td>
                        <td class="fw-bold text-info"><?= formatAmount($disbursed) ?></td>
                        <td class="<?= $rem > 0 ? 'text-warning fw-bold' : 'text-muted' ?>"><?= formatAmount($rem) ?></td>
                        <td style="min-width:120px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted"><?= $a['disb_count'] ?> tranche(s)</small>
                                <small class="fw-bold text-<?= $pct >= 100 ? 'success' : 'primary' ?>"><?= $pct ?>%</small>
                            </div>
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                        <td><?= getStatusBadge($a['status']) ?></td>
                        <td class="no-print text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info" onclick="viewDisbHistory(<?= $a['id'] ?>)" title="View Disbursement History">
                                    <i class="bi bi-clock-history me-1"></i>History
                                </button>
                                <?php if ($a['status'] === 'active' && $approved > 0): ?>
                                <button class="btn btn-sm btn-success" onclick="openDisburseModal(<?= $a['id'] ?>)" title="Record Disbursement">
                                    <i class="bi bi-send me-1"></i>Disburse
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

<!-- Disburse Fund Modal -->
<div class="modal fade" id="disburseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-send me-2"></i>Record Fund Disbursement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="disburseForm" onsubmit="event.preventDefault(); submitDisbursementRecord(document.getElementById('submitDisbursement'));">
                <div class="modal-body">
                    <!-- Award Summary -->
                    <div id="awardSummaryCard" class="card bg-light border-0 mb-3" style="display:none;">
                        <div class="card-body py-2">
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Approved</small>
                                    <strong class="text-success" id="sumApproved">—</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Disbursed</small>
                                    <strong class="text-info" id="sumDisbursed">—</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Remaining</small>
                                    <strong class="text-warning" id="sumRemaining">—</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="award_id" id="disburse_award_id">

                    <div class="mb-3">
                        <label class="form-label fw-medium">Disbursement Stage <span class="text-danger">*</span></label>
                        <select name="stage" id="disburse_stage" class="form-select" required>
                            <option value="">Select Stage</option>
                            <option value="First Instalment">First Instalment</option>
                            <option value="Second Instalment">Second Instalment</option>
                            <option value="Third Instalment">Third Instalment</option>
                            <option value="Final Disbursement">Final Disbursement</option>
                            <option value="Fully Disbursed">Fully Disbursed (Full Amount)</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Amount to Disburse (<?= CURRENCY_SYMBOL ?>) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="number" name="disbursed_amount" id="disburse_amount" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                        <small class="text-muted" id="remainingHint"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Reference</label>
                        <input type="text" name="payment_reference" id="disburse_payment_reference" class="form-control" placeholder="e.g. GRANT-MUH-2026-001">
                        <small class="text-muted">Optional. E.g. voucher number, cheque number, or transfer reference.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Disbursement Date <span class="text-danger">*</span></label>
                        <input type="date" name="disbursement_date" id="disburse_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
                        <textarea name="notes" id="disburse_notes" class="form-control" rows="3" placeholder="Any additional notes about this disbursement..."></textarea>
                    </div>

                    <div class="alert alert-warning border-0 py-2 small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        This records the disbursement for tracking purposes only. Actual payment is handled by the institution's financial department.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitDisbursement" onclick="submitDisbursementRecord(this)">
                        <i class="bi bi-send me-1"></i>Record Disbursement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disbursement History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Disbursement History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const AJAX_URL = '<?= SITE_URL ?>admin/ajax/handle_disbursements.php';

// Filter buttons
document.querySelectorAll('.disb-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.disb-filter').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        var filter = this.dataset.filter;
        document.querySelectorAll('#disbTable tbody tr').forEach(function(tr) {
            tr.style.display = (filter === 'all' || tr.dataset.status === filter) ? '' : 'none';
        });
    });
});

// Open Disburse Modal
function openDisburseModal(awardId) {
    var form = document.getElementById('disburseForm');
    if (form) form.reset();

    document.getElementById('disburse_award_id').value = awardId;
    document.getElementById('disburse_amount').value = '';

    var today = new Date().toISOString().split('T')[0];
    var dateInput = document.getElementById('disburse_date');
    if (dateInput) {
        dateInput.value = today;
    }

    $('#awardSummaryCard').hide();
    $('#remainingHint').text('');

    // Load award info for summary card
    $.ajax({
        url: AJAX_URL,
        method: 'GET',
        data: { action: 'get_award_info', award_id: awardId },
        dataType: 'json',
        success: function(res) {
            if (res && res.success) {
                var a = res.award;
                $('#sumApproved').text(a.approved_amount_fmt);
                $('#sumDisbursed').text(a.total_disbursed_fmt);
                $('#sumRemaining').text(a.remaining_fmt);
                $('#remainingHint').text('Remaining to disburse: ' + a.remaining_fmt);
                $('#awardSummaryCard').show();
            }
        },
        error: function(xhr) {
            console.error('Failed to load award info:', xhr.status, xhr.responseText);
        }
    });

    var modalEl = document.getElementById('disburseModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

// Submit Disbursement Function
function submitDisbursementRecord(btnElement) {
    var $btn = $(btnElement || '#submitDisbursement');

    var awardId  = document.getElementById('disburse_award_id').value;
    var stage    = document.getElementById('disburse_stage').value;
    var amountVal = document.getElementById('disburse_amount').value;
    var amount   = parseFloat(amountVal);
    var ref      = document.getElementById('disburse_payment_reference').value;
    var date     = document.getElementById('disburse_date').value;
    var notes    = document.getElementById('disburse_notes').value;

    if (!awardId) {
        Swal.fire({ icon: 'error', title: 'Award Missing', text: 'Award ID could not be found. Please close and re-open the Disburse window.' });
        return;
    }
    if (!stage) {
        Swal.fire({ icon: 'warning', title: 'Stage Required', text: 'Please select a disbursement stage.' });
        document.getElementById('disburse_stage').focus();
        return;
    }
    if (!amountVal || isNaN(amount) || amount <= 0) {
        Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid disbursement amount greater than 0.' });
        document.getElementById('disburse_amount').focus();
        return;
    }
    if (!date) {
        Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please select a disbursement date.' });
        document.getElementById('disburse_date').focus();
        return;
    }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Recording...');

    $.ajax({
        url: AJAX_URL,
        method: 'POST',
        data: {
            action:            'add_disbursement',
            award_id:          awardId,
            stage:             stage,
            disbursed_amount:  amount,
            payment_reference: ref,
            disbursement_date: date,
            notes:             notes
        },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Record Disbursement');
            if (res && res.success) {
                var modalEl = document.getElementById('disburseModal');
                if (modalEl) {
                    var modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Disbursement Recorded!',
                    html: res.message,
                    confirmButtonText: 'OK'
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Disbursement Failed',
                    text: (res && res.message) ? res.message : 'Unknown server response.'
                });
            }
        },
        error: function(xhr, status, error) {
            $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Record Disbursement');
            var errorMsg = 'Failed to connect to server (' + xhr.status + ').';
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp && resp.message) errorMsg = resp.message;
            } catch(e) {
                if (xhr.status === 404) errorMsg = 'Disbursement endpoint not found (404).';
                else if (xhr.status === 403) errorMsg = 'Permission denied (403).';
                else if (xhr.status === 500) errorMsg = 'Database or server error (500).';
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        }
    });
}

// View Disbursement History
function viewDisbHistory(awardId) {
    $('#historyBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    var modalEl = document.getElementById('historyModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    // Load award info + disbursements in parallel
    $.when(
        $.ajax({ url: AJAX_URL, data: { action: 'get_award_info', award_id: awardId }, dataType: 'json' }),
        $.ajax({ url: AJAX_URL, data: { action: 'get_award_disbursements', award_id: awardId }, dataType: 'json' })
    ).done(function(infoRes, disbRes) {
        var award = infoRes[0].award;
        var disbs = disbRes[0].disbursements;

        var html = '<div class="card bg-light border-0 mb-3"><div class="card-body">' +
            '<h6 class="fw-bold mb-1">' + escHtml(award.proposal_title) + '</h6>' +
            '<small class="text-muted"><i class="bi bi-journal-text me-1"></i>' + escHtml(award.grant_title) + '</small>' +
            '<hr class="my-2">' +
            '<div class="row g-2 text-center">' +
            '<div class="col-4"><small class="text-muted d-block">Approved</small><strong class="text-success">' + award.approved_amount_fmt + '</strong></div>' +
            '<div class="col-4"><small class="text-muted d-block">Disbursed</small><strong class="text-info">' + award.total_disbursed_fmt + '</strong></div>' +
            '<div class="col-4"><small class="text-muted d-block">Remaining</small><strong class="text-warning">' + award.remaining_fmt + '</strong></div>' +
            '</div>' +
            '<div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-success" style="width:' + award.disbursed_pct + '%"></div></div>' +
            '<small class="text-muted">' + award.disbursed_pct + '% disbursed</small>' +
            '</div></div>';

        if (!disbs || disbs.length === 0) {
            html += '<p class="text-center text-muted py-4">No disbursements recorded yet.</p>';
        } else {
            html += '<table class="table table-hover align-middle small"><thead class="table-light"><tr>' +
                '<th>#</th><th>Stage</th><th>Amount</th><th>Date</th><th>Reference</th><th>By</th><th>Status</th>' +
                '</tr></thead><tbody>';
            $.each(disbs, function(i, d) {
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td><span class="badge bg-primary">' + escHtml(d.stage) + '</span></td>' +
                    '<td class="fw-bold text-success">' + d.disbursed_amount_formatted + '</td>' +
                    '<td>' + d.disbursement_date_formatted + '</td>' +
                    '<td><code>' + escHtml(d.payment_reference || '—') + '</code></td>' +
                    '<td>' + escHtml(d.disbursed_by_name) + '</td>' +
                    '<td>' + d.status_badge + '</td>' +
                    '</tr>';
                if (d.notes) {
                    html += '<tr><td colspan="7" class="text-muted small ps-4"><i class="bi bi-chat-left-text me-1"></i>' + escHtml(d.notes) + '</td></tr>';
                }
            });
            html += '</tbody></table>';
        }

        $('#historyBody').html(html);
    }).fail(function() {
        $('#historyBody').html('<div class="alert alert-danger">Failed to load disbursement data.</div>');
    });
}

function escHtml(s) {
    if (!s) return '';
    return $('<div>').text(String(s)).html();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
