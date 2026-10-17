<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
if (!in_array($_SESSION['user_role'], ['admin', 'grant_manager'])) {
    requireRole('admin');
}

$db = getDb();
$pageTitle = 'Milestones';
$awardId = isset($_GET['award_id']) ? (int)$_GET['award_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    if (isset($_POST['add_milestone'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $dueDate = sanitizeInput($_POST['due_date']);
        $deliverables = sanitizeInput($_POST['deliverables']);
        $targetAwardId = (int)$_POST['award_id'];

        $stmt = $db->prepare("INSERT INTO project_milestones (award_id, title, description, due_date, deliverables, status, created_at) VALUES (:award_id, :title, :description, :due_date, :deliverables, 'pending', NOW())");
        $stmt->execute([
            ':award_id' => $targetAwardId,
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $dueDate,
            ':deliverables' => $deliverables,
        ]);
        logAudit($_SESSION['user_id'], 'create', 'project_milestones', $db->lastInsertId(), 'Created milestone: ' . $title);
        $stmtAward = $db->prepare("SELECT researcher_id FROM grant_awards WHERE id = :id");
        $stmtAward->execute([':id' => $targetAwardId]);
        $awardData = $stmtAward->fetch();
        if ($awardData) {
            createNotification($awardData['researcher_id'], 'milestone', 'New Milestone Added', 'Milestone "' . $title . '" has been added to your project.', SITE_URL . 'researcher/pages/my_awards.php');
        }
        $_SESSION['success_message'] = 'Milestone added successfully.';
        header('Location: ' . SITE_URL . 'grant_manager/pages/milestones.php' . ($awardId ? '?award_id=' . $awardId : ''));
        exit();
    }

    if (isset($_POST['update_milestone'])) {
        $milestoneId = (int)$_POST['milestone_id'];
        $status = sanitizeInput($_POST['status']);
        $completionDate = sanitizeInput($_POST['completion_date']) ?: null;

        $stmt = $db->prepare("UPDATE project_milestones SET status = :status, completion_date = :completion_date WHERE id = :id");
        $stmt->execute([':status' => $status, ':completion_date' => $completionDate, ':id' => $milestoneId]);
        logAudit($_SESSION['user_id'], 'update', 'project_milestones', $milestoneId, 'Updated milestone status to ' . $status);
        $_SESSION['success_message'] = 'Milestone updated successfully.';
        header('Location: ' . SITE_URL . 'grant_manager/pages/milestones.php' . ($awardId ? '?award_id=' . $awardId : ''));
        exit();
    }
}

$awardInfo = null;
if ($awardId) {
    $stmt = $db->prepare("SELECT ga.*, u.first_name, u.last_name, g.title as grant_title FROM grant_awards ga JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id WHERE ga.id = :id");
    $stmt->execute([':id' => $awardId]);
    $awardInfo = $stmt->fetch();
}

$milestones = [];
if ($awardId) {
    $stmt = $db->prepare("SELECT pm.* FROM project_milestones pm WHERE pm.award_id = :award_id ORDER BY pm.due_date ASC");
    $stmt->execute([':award_id' => $awardId]);
    $milestones = $stmt->fetchAll();
} else {
    $milestones = $db->query("SELECT pm.*, u.first_name, u.last_name, g.title as grant_title FROM project_milestones pm JOIN grant_awards ga ON pm.award_id = ga.id JOIN users u ON ga.researcher_id = u.id JOIN grants g ON ga.grant_id = g.id ORDER BY pm.due_date ASC")->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h4 class="mb-1 fw-bold"><?= $awardInfo ? 'Milestones: ' . htmlspecialchars($awardInfo['grant_title']) : 'All Milestones' ?></h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/pages/projects.php">Projects</a></li>
                <li class="breadcrumb-item active">Milestones</li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if ($awardInfo): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMilestoneModal"><i class="bi bi-plus-circle me-1"></i>Add Milestone</button>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>grant_manager/pages/milestones.php" class="btn btn-outline-secondary btn-sm">All Milestones</a>
    </div>
</div>

<?= displaySessionMessages() ?>

<?php if ($awardInfo): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <small class="text-muted">Researcher</small>
                <h6 class="mb-0"><?= htmlspecialchars($awardInfo['first_name'] . ' ' . $awardInfo['last_name']) ?></h6>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <small class="text-muted">Amount</small>
                <h6 class="mb-0"><?= formatAmount($awardInfo['awarded_amount']) ?></h6>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <small class="text-muted">Period</small>
                <h6 class="mb-0"><?= $awardInfo['start_date'] ? formatDate($awardInfo['start_date']) : 'N/A' ?> - <?= $awardInfo['end_date'] ? formatDate($awardInfo['end_date']) : 'Ongoing' ?></h6>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <small class="text-muted">Status</small>
                <h6 class="mb-0"><?= getStatusBadge($awardInfo['status']) ?></h6>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><?= $awardId ? 'Milestones for this Project' : 'All Milestones' ?></h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <?php if (!$awardId): ?><th>Project</th><?php endif; ?>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Deliverables</th>
                        <th>Completion Date</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($milestones)): ?>
                    <tr><td colspan="<?= $awardId ? 6 : 7 ?>" class="text-center text-muted py-4">No milestones found</td></tr>
                    <?php else: ?>
                    <?php foreach ($milestones as $m): ?>
                    <tr>
                        <?php if (!$awardId): ?><td><?= htmlspecialchars(truncateText($m['grant_title'], 30)) ?></td><?php endif; ?>
                        <td class="fw-medium"><?= htmlspecialchars($m['title']) ?></td>
                        <td><?= $m['due_date'] ? formatDate($m['due_date']) : '-' ?></td>
                        <td><?= getStatusBadge($m['status']) ?></td>
                        <td><?= htmlspecialchars(truncateText($m['deliverables'] ?? '', 50)) ?></td>
                        <td><?= $m['completion_date'] ? formatDate($m['completion_date']) : '-' ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary btn-icon" onclick="editMilestone(<?= $m['id'] ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($awardId): ?>
<div class="modal fade" id="addMilestoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="add_milestone" value="1">
                <input type="hidden" name="award_id" value="<?= $awardId ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Milestone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Deliverables</label>
                        <textarea name="deliverables" class="form-control" rows="3" placeholder="Expected outputs / deliverables"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="editMilestoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="update_milestone" value="1">
                <input type="hidden" name="milestone_id" id="edit_milestone_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Milestone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Status</label>
                        <select name="status" id="edit_milestone_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Completion Date</label>
                        <input type="date" name="completion_date" id="edit_milestone_completion" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var milestonesData = <?= json_encode($milestones) ?>;

function editMilestone(id) {
    var m = milestonesData.find(function(x) { return x.id == id; });
    if (m) {
        $('#edit_milestone_id').val(m.id);
        $('#edit_milestone_status').val(m.status);
        $('#edit_milestone_completion').val(m.completion_date || '');
        $('#editMilestoneModal').modal('show');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
