<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Reviewers';

$stmt = $db->prepare("SELECT * FROM users WHERE role = 'reviewer' ORDER BY created_at DESC");
$stmt->execute();
$reviewers = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Reviewers</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Reviewers</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>All Reviewers</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewerModal"><i class="bi bi-plus-lg me-1"></i>Add Reviewer</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Expertise</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviewers)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No reviewers found</td></tr>
                    <?php else: ?>
                    <?php foreach ($reviewers as $r): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($r['first_name'] . ' ' . $r['last_name']) ?>" class="rounded-circle me-2" width="32" height="32">
                                <div><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= htmlspecialchars($r['expertise_area'] ?? '-') ?></td>
                        <td><?= getStatusBadge($r['reviewer_status'] ?? 'pending') ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary btn-icon" onclick="editReviewer(<?= $r['id'] ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="<?= SITE_URL ?>admin/ajax/handle_users.php?action=toggle_status&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-<?= $r['is_active'] ? 'warning' : 'success' ?> btn-icon" title="<?= $r['is_active'] ? 'Deactivate' : 'Activate' ?>"><i class="bi bi-<?= $r['is_active'] ? 'pause' : 'play' ?>"></i></a>
                            <a href="<?= SITE_URL ?>admin/ajax/handle_users.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger btn-icon btn-delete" data-message="Delete <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>?" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addReviewerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= SITE_URL ?>admin/ajax/handle_users.php" class="ajax-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="role" value="reviewer">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Reviewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Expertise Area</label>
                            <input type="text" name="expertise_area" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Reviewer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editReviewer(id) {
    window.location.href = '<?= SITE_URL ?>admin/pages/all_users.php?edit=' + id;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
