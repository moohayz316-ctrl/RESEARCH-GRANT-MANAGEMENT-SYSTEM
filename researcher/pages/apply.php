<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('researcher');

$userId = $_SESSION['user_id'];
$db = getDb();
$grantId = (int)($_GET['id'] ?? 0);
$message = '';
$messageType = '';

$stmt = $db->prepare("SELECT * FROM grants WHERE id = :id AND status = 'open' AND is_published = 1 AND application_start_date <= CURDATE() AND application_end_date >= CURDATE()");
$stmt->execute([':id' => $grantId]);
$grant = $stmt->fetch();

if (!$grant) {
    header('Location: ' . SITE_URL . 'researcher/pages/grants.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM proposals WHERE researcher_id = :researcher_id AND grant_id = :grant_id AND status = 'draft' LIMIT 1");
$stmt->execute([':researcher_id' => $userId, ':grant_id' => $grantId]);
$existingDraft = $stmt->fetch();

$proposal = $existingDraft ?: [
    'id' => 0,
    'title' => '',
    'abstract' => '',
    'problem_statement' => '',
    'methodology' => '',
    'expected_outcomes' => '',
    'budget_breakdown' => '',
    'budget_amount' => '',
    'status' => 'draft'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $title = sanitizeInput($_POST['title'] ?? '');
    $abstract = sanitizeInput($_POST['abstract'] ?? '');
    $problemStatement = sanitizeInput($_POST['problem_statement'] ?? '');
    $methodology = sanitizeInput($_POST['methodology'] ?? '');
    $expectedOutcomes = sanitizeInput($_POST['expected_outcomes'] ?? '');
    $budgetBreakdown = sanitizeInput($_POST['budget_breakdown'] ?? '');
    $budgetAmount = sanitizeInput($_POST['budget_amount'] ?? '');
    $action = $_POST['action'] ?? 'draft';

    if (empty($title) || empty($abstract)) {
        $message = 'Title and abstract are required.';
        $messageType = 'danger';
    } else {
        $status = ($action === 'submit') ? 'submitted' : 'draft';
        $submittedAt = ($status === 'submitted') ? date('Y-m-d H:i:s') : null;

        if ($existingDraft) {
            $sql = "UPDATE proposals SET title = :title, abstract = :abstract, problem_statement = :problem_statement, methodology = :methodology, expected_outcomes = :expected_outcomes, budget_breakdown = :budget_breakdown, budget_amount = :budget_amount, status = :status" . ($submittedAt ? ", submitted_at = :submitted_at" : "") . " WHERE id = :id";
            $params = [
                ':title' => $title,
                ':abstract' => $abstract,
                ':problem_statement' => $problemStatement,
                ':methodology' => $methodology,
                ':expected_outcomes' => $expectedOutcomes,
                ':budget_breakdown' => $budgetBreakdown,
                ':budget_amount' => $budgetAmount,
                ':status' => $status,
                ':id' => $existingDraft['id'],
            ];
            if ($submittedAt) {
                $params[':submitted_at'] = $submittedAt;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $proposalId = $existingDraft['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO proposals (researcher_id, grant_id, title, abstract, problem_statement, methodology, expected_outcomes, budget_breakdown, budget_amount, status, submitted_at, created_at) VALUES (:researcher_id, :grant_id, :title, :abstract, :problem_statement, :methodology, :expected_outcomes, :budget_breakdown, :budget_amount, :status, :submitted_at, NOW())");
            $stmt->execute([
                ':researcher_id' => $userId,
                ':grant_id' => $grantId,
                ':title' => $title,
                ':abstract' => $abstract,
                ':problem_statement' => $problemStatement,
                ':methodology' => $methodology,
                ':expected_outcomes' => $expectedOutcomes,
                ':budget_breakdown' => $budgetBreakdown,
                ':budget_amount' => $budgetAmount,
                ':status' => $status,
                ':submitted_at' => $submittedAt,
            ]);
            $proposalId = $db->lastInsertId();
        }

        if (!empty($_FILES['proposal_document']['name'])) {
            $upload = uploadFile($_FILES['proposal_document'], PROPOSAL_PATH);
            if ($upload['success']) {
                $stmt = $db->prepare("INSERT INTO proposal_documents (proposal_id, document_type, file_name, file_path, file_size, file_type) VALUES (:proposal_id, :document_type, :file_name, :file_path, :file_size, :file_type)");
                $stmt->execute([':proposal_id' => $proposalId, ':document_type' => 'proposal', ':file_name' => $upload['file_name'], ':file_path' => $upload['file_name'], ':file_size' => $_FILES['proposal_document']['size'], ':file_type' => $_FILES['proposal_document']['type']]);
            }
        }

        if (!empty($_FILES['budget_document']['name'])) {
            $upload = uploadFile($_FILES['budget_document'], PROPOSAL_PATH);
            if ($upload['success']) {
                $stmt = $db->prepare("INSERT INTO proposal_documents (proposal_id, document_type, file_name, file_path, file_size, file_type) VALUES (:proposal_id, :document_type, :file_name, :file_path, :file_size, :file_type)");
                $stmt->execute([':proposal_id' => $proposalId, ':document_type' => 'budget', ':file_name' => $upload['file_name'], ':file_path' => $upload['file_name'], ':file_size' => $_FILES['budget_document']['size'], ':file_type' => $_FILES['budget_document']['type']]);
            }
        }

        if (!empty($_FILES['supporting_documents']['name'][0])) {
            $files = $_FILES['supporting_documents'];
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i],
                    ];
                    $upload = uploadFile($file, PROPOSAL_PATH);
                    if ($upload['success']) {
                        $stmt = $db->prepare("INSERT INTO proposal_documents (proposal_id, document_type, file_name, file_path, file_size, file_type) VALUES (:proposal_id, :document_type, :file_name, :file_path, :file_size, :file_type)");
                        $stmt->execute([':proposal_id' => $proposalId, ':document_type' => 'supporting', ':file_name' => $upload['file_name'], ':file_path' => $upload['file_name'], ':file_size' => $file['size'], ':file_type' => $file['type']]);
                    }
                }
            }
        }

        logAudit($userId, $status === 'submitted' ? 'proposal_submitted' : 'proposal_saved', 'proposals', $proposalId, 'Proposal ' . ($status === 'submitted' ? 'submitted' : 'saved as draft') . ' for grant: ' . $grant['title']);

        $message = $status === 'submitted' ? 'Proposal submitted successfully.' : 'Proposal saved as draft.';
        $messageType = 'success';

        if ($status === 'submitted') {
            $admins = $db->query("SELECT id FROM users WHERE role = 'admin' AND is_active = 1")->fetchAll();
            $researcherName = $_SESSION['user_name'] ?? 'A researcher';
            foreach ($admins as $adm) {
                createNotification(
                    $adm['id'],
                    'proposal_submitted',
                    'New Proposal Submitted',
                    'Proposal "' . truncateText($title, 40) . '" was submitted by ' . $researcherName . ' for grant "' . truncateText($grant['title'], 35) . '".',
                    SITE_URL . 'admin/pages/applications.php'
                );
            }
            createNotification(
                $userId,
                'proposal_submitted',
                'Proposal Submitted Successfully',
                'Your proposal "' . truncateText($title, 40) . '" has been submitted for administrative review.',
                SITE_URL . 'researcher/pages/my_proposals.php'
            );
            $_SESSION['success_message'] = 'Proposal submitted successfully.';
            header('Location: ' . SITE_URL . 'researcher/pages/my_proposals.php');
            exit();
        }

        $stmt = $db->prepare("SELECT * FROM proposals WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $proposalId]);
        $proposal = $stmt->fetch();
        $existingDraft = $proposal;
    }
}

$stmt = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :proposal_id");
$stmt->execute([':proposal_id' => $proposal['id']]);
$documents = $stmt->fetchAll();

$pageTitle = 'Apply for Grant';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Apply for Grant</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>researcher/pages/grants.php">Available Grants</a></li>
                <li class="breadcrumb-item active">Apply</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="fw-bold"><?= htmlspecialchars($grant['title']) ?></h5>
                <p class="text-muted mb-2"><?= htmlspecialchars(truncateText($grant['description'] ?? '', 300)) ?></p>
            </div>
            <div class="col-md-4">
                <div class="bg-light rounded p-3">
                    <small class="d-block"><strong>Funding Amount:</strong> <?= formatAmount($grant['funding_amount']) ?></small>
                    <small class="d-block"><strong>Deadline:</strong> <?= formatDate($grant['application_end_date']) ?></small>
                    <small class="d-block"><strong>Status:</strong> <?= getStatusBadge($grant['status']) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">Application Form</h6>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label small fw-medium">Proposal Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($proposal['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Abstract <span class="text-danger">*</span></label>
                <textarea name="abstract" class="form-control" rows="6" required><?= htmlspecialchars($proposal['abstract']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Problem Statement</label>
                <textarea name="problem_statement" class="form-control" rows="5"><?= htmlspecialchars($proposal['problem_statement']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Methodology</label>
                <textarea name="methodology" class="form-control" rows="5"><?= htmlspecialchars($proposal['methodology']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Expected Outcomes</label>
                <textarea name="expected_outcomes" class="form-control" rows="5"><?= htmlspecialchars($proposal['expected_outcomes']) ?></textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-medium">Budget Amount (<?= CURRENCY_SYMBOL ?>)</label>
                    <input type="number" name="budget_amount" class="form-control" step="0.01" value="<?= htmlspecialchars($proposal['budget_amount']) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Budget Breakdown</label>
                <textarea name="budget_breakdown" class="form-control" rows="5"><?= htmlspecialchars($proposal['budget_breakdown']) ?></textarea>
            </div>

            <hr>
            <h6 class="fw-bold mb-3">Supporting Documents</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-medium">Proposal Document</label>
                    <input type="file" name="proposal_document" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-medium">Budget Document</label>
                    <input type="file" name="budget_document" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-medium">Supporting Documents</label>
                    <input type="file" name="supporting_documents[]" class="form-control" multiple>
                </div>
            </div>

            <?php if (!empty($documents)): ?>
            <div class="mb-3">
                <label class="form-label small fw-medium">Uploaded Files</label>
                <ul class="list-group">
                    <?php foreach ($documents as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi <?= getFileIcon($doc['file_name']) ?> me-2"></i><?= htmlspecialchars($doc['file_name']) ?></span>
                        <span class="badge bg-secondary"><?= htmlspecialchars($doc['document_type']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" name="action" value="draft" class="btn btn-secondary"><i class="bi bi-save me-1"></i>Save as Draft</button>
                <button type="submit" name="action" value="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Application</button>
                <a href="<?= SITE_URL ?>researcher/pages/grants.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
