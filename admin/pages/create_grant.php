<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Create Grant';

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
    $slug = generateSlug($title);

    $stmt = $db->prepare("SELECT id FROM grants WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = 'A grant with a similar title already exists. Please use a different title.';
        header('Location: ' . SITE_URL . 'admin/pages/create_grant.php');
        exit();
    }

    $guidelines_file = null;
    if (!empty($_FILES['guidelines_file']['name'])) {
        $upload = uploadFile($_FILES['guidelines_file'], GUIDELINE_PATH);
        if ($upload['success']) {
            $guidelines_file = $upload['file_name'];
        } else {
            $_SESSION['error_message'] = $upload['message'];
        }
    }

    $stmt = $db->prepare("INSERT INTO grants (title, slug, description, objectives, eligibility_criteria, funding_amount, category_id, status, application_start_date, application_end_date, review_deadline, max_applications, guidelines_file, is_published, created_by) VALUES (:title, :slug, :description, :objectives, :eligibility, :funding, :category_id, :status, :start_date, :end_date, :review_deadline, :max_apps, :guidelines, :is_published, :created_by)");
    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
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
        ':created_by' => $_SESSION['user_id'],
    ]);

    $grantId = $db->lastInsertId();
    logAudit($_SESSION['user_id'], 'create', 'grants', $grantId, 'Created grant: ' . $title);

    if ($status === 'open') {
        $researchers = $db->query("SELECT id FROM users WHERE role = 'researcher' AND is_active = 1")->fetchAll();
        foreach ($researchers as $rUser) {
            createNotification(
                $rUser['id'],
                'grant',
                'New Grant Opportunity: ' . $title,
                'A new grant opportunity has been published: ' . truncateText($description, 90),
                SITE_URL . 'researcher/pages/grants.php'
            );
        }
    }

    $_SESSION['success_message'] = 'Grant created successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/manage_grants.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">Create Grant Opportunity</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/pages/manage_grants.php">Grants</a></li>
            <li class="breadcrumb-item active">Create Grant</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="card" data-aos="fade-up">
    <div class="card-header">Grant Details</div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Objectives</label>
                    <textarea name="objectives" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Eligibility Criteria</label>
                    <textarea name="eligibility_criteria" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Funding Amount</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                        <input type="number" step="0.01" name="funding_amount" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <div class="select-wrap">
                        <select name="category_id" class="modern-select">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <div class="select-wrap">
                        <select name="status" class="modern-select" required>
                            <option value="draft">Draft</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                        <i class="bi bi-chevron-down select-arrow"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Application Start Date</label>
                    <input type="date" name="application_start_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Application End Date</label>
                    <input type="date" name="application_end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Review Deadline</label>
                    <input type="date" name="review_deadline" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Applications</label>
                    <input type="number" name="max_applications" class="form-control" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guidelines File (PDF)</label>
                    <input type="file" name="guidelines_file" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Create Grant</button>
                <a href="<?= SITE_URL ?>admin/pages/manage_grants.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
