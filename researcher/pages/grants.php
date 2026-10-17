<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();
$categoryFilter = sanitizeInput($_GET['category'] ?? '');

$categories = [];
$stmt = $db->query("SELECT * FROM grant_categories ORDER BY name");
$categories = $stmt->fetchAll();

$sql = "SELECT g.*, gc.name as category_name FROM grants g LEFT JOIN grant_categories gc ON g.category_id = gc.id WHERE g.status = 'open' AND g.is_published = 1 AND g.application_start_date <= CURDATE() AND g.application_end_date >= CURDATE()";
$params = [];

if ($categoryFilter) {
    $sql .= " AND g.category_id = :category_id";
    $params[':category_id'] = $categoryFilter;
}

$sql .= " ORDER BY g.application_end_date ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$grants = $stmt->fetchAll();

$appliedIds = [];
if (!empty($grants)) {
    $grantIds = array_column($grants, 'id');
    $placeholders = implode(',', array_fill(0, count($grantIds), '?'));
    $stmt = $db->prepare("SELECT grant_id FROM proposals WHERE researcher_id = ? AND grant_id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $grantIds));
    $appliedIds = array_column($stmt->fetchAll(), 'grant_id');
}

$pageTitle = 'Available Grants';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Available Grants</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Available Grants</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-3 align-items-end flex-wrap flex-lg-nowrap" id="grantFilterForm">
            <div class="flex-fill" style="min-width:200px;max-width:350px">
                <label class="filter-label mb-1">Filter by Category</label>
                <div class="select-wrap">
                    <select name="category" class="modern-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down select-arrow"></i>
                </div>
            </div>
            <a href="<?= SITE_URL ?>researcher/pages/grants.php" class="btn btn-outline-primary filter-clear-btn flex-shrink-0"><i class="bi bi-x-lg me-1"></i>Clear</a>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($grants)): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">No open grants available at the moment.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($grants as $grant): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($grant['title']) ?></h6>
                    <?= getStatusBadge($grant['status']) ?>
                </div>
                <p class="small text-muted mb-2"><?= htmlspecialchars($grant['category_name'] ?? 'Uncategorized') ?></p>
                <p class="small mb-2"><?= htmlspecialchars(truncateText($grant['description'] ?? '', 120)) ?></p>
                <div class="d-flex justify-content-between small mb-1">
                    <span><strong>Amount:</strong> <?= formatAmount($grant['funding_amount']) ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span><strong>Deadline:</strong> <?= formatDate($grant['application_end_date']) ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-<?= strtotime($grant['application_end_date']) < time() ? 'danger' : 'success' ?>">
                        <strong><?= strtotime($grant['application_end_date']) < time() ? 'Expired' : timeAgo($grant['application_end_date']) ?> remaining</strong>
                    </span>
                </div>
                <?php if (in_array($grant['id'], $appliedIds)): ?>
                <button class="btn btn-secondary btn-sm w-100" disabled><i class="bi bi-check-lg me-1"></i>Applied</button>
                <?php else: ?>
                <a href="<?= SITE_URL ?>researcher/pages/apply.php?id=<?= $grant['id'] ?>" class="btn btn-primary btn-sm w-100"><i class="bi bi-send me-1"></i>Apply Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
