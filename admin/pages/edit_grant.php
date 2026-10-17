<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Edit Grant';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $db->prepare("SELECT * FROM grants WHERE id = :id");
$stmt->execute([':id' => $id]);
$grant = $stmt->fetch();

if (!$grant) {
    $_SESSION['error_message'] = 'Grant not found.';
    header('Location: ' . SITE_URL . 'admin/pages/manage_grants.php');
    exit();
}

$categories = $db->query("SELECT * FROM grant_categories WHERE is_active = 1 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $objectives = sanitizeInput($_POST['objectives']);
    $eligibility = sanitizeInput($_POST['eligibility_criteria']);
    $funding = $_POST['funding_amount'] ?: null;
    $category_id = $_POST['category_id'] ?: null;
    $status = sanitizeInput($_POST['status']);
    $start_date = sanitizeInput($_POST['application_start_date']) ?: null;
    $end_date = sanitizeInput($_POST['application_end_date']) ?: null;
    $review_deadline = sanitizeInput($_POST['review_deadline']) ?: null;
    $max_apps = $_POST['max_applications'] ?: null;

    $guidelines_file = $grant['guidelines_file'];
    if (!empty($_FILES['guidelines_file']['name'])) {
        $upload = uploadFile($_FILES['guidelines_file'], GUIDELINE_PATH);
        if ($upload['success']) {
            if ($grant['guidelines_file'] && file_exists(GUIDELINE_PATH . $grant['guidelines_file'])) {
                unlink(GUIDELINE_PATH . $grant['guidelines_file']);
            }
            $guidelines_file = $upload['file_name'];
        } else {
            $_SESSION['error_message'] = $upload['message'];
        }
    }

    $stmt = $db->prepare("UPDATE grants SET title = :title, description = :description, objectives = :objectives, eligibility_criteria = :eligibility, funding_amount = :funding, category_id = :category_id, status = :status, application_start_date = :start_date, application_end_date = :end_date, review_deadline = :review_deadline, max_applications = :max_apps, guidelines_file = :guidelines, is_published = :is_published WHERE id = :id");
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':objectives' => $objectives,
        ':eligibility' => $eligibility,
        ':funding' => $funding,
        ':category_id' => $category_id,
        ':status' => $status,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':review_deadline' => $review_deadline,
        ':max_apps' => $max_apps,
        ':guidelines' => $guidelines_file,
        ':is_published' => $status === 'open' ? 1 : 0,
        ':id' => $id,
    ]);

    logAudit($_SESSION['user_id'], 'update', 'grants', $id, 'Updated grant: ' . $title);

    if ($status === 'open' && $grant['status'] !== 'open') {
        $researchers = $db->query("SELECT id FROM users WHERE role = 'researcher' AND is_active = 1")->fetchAll();
        foreach ($researchers as $rUser) {
            createNotification(
                $rUser['id'],
                'grant',
                'Grant Opportunity Opened: ' . $title,
                'Grant opportunity "' . $title . '" is now open for proposals.',
                SITE_URL . 'researcher/pages/grants.php'
            );
        }
    }

    $_SESSION['success_message'] = 'Grant updated successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/manage_grants.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Edit Grant</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/pages/manage_grants.php">Grants</a></li>
            <li class="breadcrumb-item active">Edit Grant</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header">Edit Grant: <?= htmlspecialchars($grant['title']) ?></div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($grant['title']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($grant['description']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Objectives</label>
                    <textarea name="objectives" class="form-control" rows="4"><?= htmlspecialchars($grant['objectives'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Eligibility Criteria</label>
                    <textarea name="eligibility_criteria" class="form-control" rows="4"><?= htmlspecialchars($grant['eligibility_criteria'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Funding Amount</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                        <input type="number" step="0.01" name="funding_amount" class="form-control" value="<?= htmlspecialchars($grant['funding_amount'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $grant['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" <?= $grant['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="open" <?= $grant['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= $grant['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="cancelled" <?= $grant['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Application Start Date</label>
                    <input type="date" name="application_start_date" class="form-control" value="<?= htmlspecialchars($grant['application_start_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Application End Date</label>
                    <input type="date" name="application_end_date" class="form-control" value="<?= htmlspecialchars($grant['application_end_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Review Deadline</label>
                    <input type="date" name="review_deadline" class="form-control" value="<?= htmlspecialchars($grant['review_deadline'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Applications</label>
                    <input type="number" name="max_applications" class="form-control" min="0" value="<?= htmlspecialchars($grant['max_applications'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guidelines File</label>
                    <input type="file" name="guidelines_file" class="form-control" accept=".pdf,.doc,.docx">
                    <?php if ($grant['guidelines_file']): ?>
                    <small class="text-muted">Current: <?= htmlspecialchars($grant['guidelines_file']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Grant</button>
                <a href="<?= SITE_URL ?>admin/pages/manage_grants.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
