<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Grant Categories';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM grant_categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    logAudit($_SESSION['user_id'], 'delete', 'grant_categories', $id, 'Deleted grant category');
    $_SESSION['success_message'] = 'Category deleted successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/categories.php');
    exit();
}

$stmt = $db->query("SELECT * FROM grant_categories ORDER BY name");
$categories = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Grant Categories</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Categories</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>All Categories</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg me-1"></i>Add Category</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Icon</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found</td></tr>
                    <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><span class="badge" style="background:<?= htmlspecialchars($cat['color'] ?? '#6c757d') ?>"><?= htmlspecialchars($cat['name']) ?></span></td>
                        <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                        <td><i class="<?= htmlspecialchars($cat['icon'] ?? 'bi bi-tag') ?>"></i></td>
                        <td><span class="badge" style="background:<?= htmlspecialchars($cat['color'] ?? '#6c757d') ?>">&nbsp;</span> <?= htmlspecialchars($cat['color'] ?? '-') ?></td>
                        <td><?= getStatusBadge($cat['is_active'] ? 'active' : 'inactive') ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary btn-icon edit-cat" data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>" data-description="<?= htmlspecialchars($cat['description'] ?? '') ?>" data-icon="<?= htmlspecialchars($cat['icon'] ?? '') ?>" data-color="<?= htmlspecialchars($cat['color'] ?? '') ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="<?= SITE_URL ?>admin/pages/categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger btn-icon btn-delete" data-message="Delete category '<?= htmlspecialchars($cat['name']) ?>'?" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= SITE_URL ?>admin/ajax/handle_users.php" class="ajax-form">
                <input type="hidden" name="action" value="add_category">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon (Bootstrap Icon class)</label>
                            <input type="text" name="icon" class="form-control" placeholder="bi bi-rocket-takeoff">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color (Hex)</label>
                            <input type="color" name="color" class="form-control form-control-color">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= SITE_URL ?>admin/ajax/handle_users.php" class="ajax-form">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="id" id="edit_cat_id">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_cat_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" id="edit_cat_icon" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="edit_cat_color" class="form-control form-control-color">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.edit-cat', function() {
        $('#edit_cat_id').val($(this).data('id'));
        $('#edit_cat_name').val($(this).data('name'));
        $('#edit_cat_description').val($(this).data('description'));
        $('#edit_cat_icon').val($(this).data('icon'));
        $('#edit_cat_color').val($(this).data('color') || '#d4a373');
        $('#editCategoryModal').modal('show');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
