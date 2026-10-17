<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'All Users';

$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">All Users</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">All Users</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>User Management</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-plus-lg me-1"></i>Add User</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No users found</td></tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= getAvatarUrl($user['first_name'] . ' ' . $user['last_name']) ?>" class="rounded-circle me-2" width="32" height="32">
                                <div><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'reviewer' ? 'info' : 'primary') ?>"><?= ucwords($user['role']) ?></span></td>
                        <td><?= getStatusBadge($user['is_active'] ? 'active' : 'inactive') ?></td>
                        <td><?= formatDate($user['created_at']) ?></td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-outline-primary btn-icon edit-user" data-id="<?= $user['id'] ?>" data-first_name="<?= htmlspecialchars($user['first_name']) ?>" data-last_name="<?= htmlspecialchars($user['last_name']) ?>" data-email="<?= htmlspecialchars($user['email']) ?>" data-role="<?= $user['role'] ?>" data-phone="<?= htmlspecialchars($user['phone'] ?? '') ?>" data-department="<?= htmlspecialchars($user['department'] ?? '') ?>" data-institution="<?= htmlspecialchars($user['institution'] ?? '') ?>" data-expertise_area="<?= htmlspecialchars($user['expertise_area'] ?? '') ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="<?= SITE_URL ?>admin/ajax/handle_users.php?action=toggle_status&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?> btn-icon" title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>"><i class="bi bi-<?= $user['is_active'] ? 'pause' : 'play' ?>"></i></a>
                            <a href="<?= SITE_URL ?>admin/ajax/handle_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger btn-icon btn-delete" data-message="Delete user <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>?" title="Delete"><i class="bi bi-trash"></i></a>
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

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= SITE_URL ?>admin/ajax/handle_users.php" class="ajax-form">
                <input type="hidden" name="action" value="add">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
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
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="researcher">Researcher</option>
                                <option value="reviewer">Reviewer</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= SITE_URL ?>admin/ajax/handle_users.php" class="ajax-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_user_id">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="researcher">Researcher</option>
                                <option value="reviewer">Reviewer</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" id="edit_department" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Institution</label>
                            <input type="text" name="institution" id="edit_institution" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Expertise Area</label>
                            <input type="text" name="expertise_area" id="edit_expertise_area" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.edit-user', function() {
        $('#edit_user_id').val($(this).data('id'));
        $('#edit_first_name').val($(this).data('first_name'));
        $('#edit_last_name').val($(this).data('last_name'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_role').val($(this).data('role'));
        $('#edit_phone').val($(this).data('phone'));
        $('#edit_department').val($(this).data('department'));
        $('#edit_institution').val($(this).data('institution'));
        $('#edit_expertise_area').val($(this).data('expertise_area'));
        $('#editUserModal').modal('show');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
