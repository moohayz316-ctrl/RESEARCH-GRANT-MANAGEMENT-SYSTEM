<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $settings = [
        'site_name' => sanitizeInput($_POST['site_name']),
        'site_description' => sanitizeInput($_POST['site_description']),
        'site_email' => sanitizeInput($_POST['site_email']),
        'site_phone' => sanitizeInput($_POST['site_phone']),
        'site_address' => sanitizeInput($_POST['site_address']),
        'currency_symbol' => sanitizeInput($_POST['currency_symbol']),
        'max_file_size' => sanitizeInput($_POST['max_file_size']),
        'allowed_file_types' => sanitizeInput($_POST['allowed_file_types']),
        'submission_deadline_reminder_days' => sanitizeInput($_POST['submission_deadline_reminder_days']),
        'auto_approve_reviewers' => sanitizeInput($_POST['auto_approve_reviewers'] ?? '0'),
        'min_reviewers_per_proposal' => sanitizeInput($_POST['min_reviewers_per_proposal']),
        'max_review_score' => sanitizeInput($_POST['max_review_score']),
    ];

    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2");
        $stmt->execute([':key' => $key, ':value' => $value, ':value2' => $value]);
    }

    logAudit($_SESSION['user_id'], 'update', 'system_settings', null, 'Updated system settings');
    $_SESSION['success_message'] = 'Settings updated successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/settings.php');
    exit();
}

$stmt = $db->query("SELECT * FROM system_settings");
$dbSettings = $stmt->fetchAll();
$settings = [];
foreach ($dbSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">System Settings</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header">General Settings</div>
    <div class="card-body">
        <form method="post" class="needs-validation" novalidate>
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? SITE_NAME) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Site Email</label>
                    <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($settings['site_email'] ?? SITE_EMAIL) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Site Phone</label>
                    <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($settings['site_phone'] ?? SITE_PHONE) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($settings['currency_symbol'] ?? CURRENCY_SYMBOL) ?>" maxlength="5">
                </div>
                <div class="col-12">
                    <label class="form-label">Site Description</label>
                    <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Site Address</label>
                    <textarea name="site_address" class="form-control" rows="2"><?= htmlspecialchars($settings['site_address'] ?? SITE_ADDRESS) ?></textarea>
                </div>
            </div>
            <hr class="my-4">
            <h6 class="fw-bold mb-3">Upload Settings</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Max File Size (bytes)</label>
                    <input type="number" name="max_file_size" class="form-control" value="<?= htmlspecialchars($settings['max_file_size'] ?? MAX_FILE_SIZE) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Allowed File Types</label>
                    <input type="text" name="allowed_file_types" class="form-control" value="<?= htmlspecialchars($settings['allowed_file_types'] ?? ALLOWED_FILE_TYPES) ?>">
                </div>
            </div>
            <hr class="my-4">
            <h6 class="fw-bold mb-3">Review Settings</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Submission Deadline Reminder (days)</label>
                    <input type="number" name="submission_deadline_reminder_days" class="form-control" value="<?= htmlspecialchars($settings['submission_deadline_reminder_days'] ?? '7') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Auto Approve Reviewers</label>
                    <select name="auto_approve_reviewers" class="form-select">
                        <option value="1" <?= ($settings['auto_approve_reviewers'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= ($settings['auto_approve_reviewers'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Min Reviewers Per Proposal</label>
                    <input type="number" name="min_reviewers_per_proposal" class="form-control" value="<?= htmlspecialchars($settings['min_reviewers_per_proposal'] ?? '2') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Review Score</label>
                    <input type="number" name="max_review_score" class="form-control" value="<?= htmlspecialchars($settings['max_review_score'] ?? '100') ?>">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
