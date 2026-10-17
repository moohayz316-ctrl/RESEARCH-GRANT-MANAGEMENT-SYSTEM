<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $_SESSION['error_message'] = 'New password and confirm password do not match.';
    } elseif (strlen($new) < 6) {
        $_SESSION['error_message'] = 'New password must be at least 6 characters.';
    } else {
        $result = changePassword($_SESSION['user_id'], $current, $new);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: ' . SITE_URL . 'admin/pages/change_password.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Change Password</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Change Password</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="row justify-content-center">
    <div class="col-lg-6" data-aos="fade-up">
        <div class="card">
            <div class="card-header">Update Password</div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#current_password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#new_password"><i class="bi bi-eye"></i></button>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#confirm_password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-key me-1"></i>Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
