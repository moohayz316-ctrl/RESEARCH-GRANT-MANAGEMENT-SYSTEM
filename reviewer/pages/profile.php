<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('reviewer');

$userId = $_SESSION['user_id'];
$db = getDb();
$message = '';
$messageType = '';

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ' . SITE_URL . 'login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $institution = sanitizeInput($_POST['institution'] ?? '');
    $biography = sanitizeInput($_POST['biography'] ?? '');
    $expertiseArea = sanitizeInput($_POST['expertise_area'] ?? '');
    $orcidId = sanitizeInput($_POST['orcid_id'] ?? '');

    $profilePicture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH, ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload['success']) {
            if ($profilePicture) {
                deleteFile(PROFILE_PATH . $profilePicture);
            }
            $profilePicture = $upload['file_name'];
        } else {
            $message = $upload['message'];
            $messageType = 'danger';
        }
    }

    if (empty($message)) {
        $stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, department = :department, institution = :institution, biography = :biography, expertise_area = :expertise_area, orcid_id = :orcid_id, profile_picture = :profile_picture WHERE id = :id");
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':phone' => $phone,
            ':department' => $department,
            ':institution' => $institution,
            ':biography' => $biography,
            ':expertise_area' => $expertiseArea,
            ':orcid_id' => $orcidId,
            ':profile_picture' => $profilePicture,
            ':id' => $userId,
        ]);

        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;

        logAudit($userId, 'profile_update', 'users', $userId, 'Profile updated');

        $message = 'Profile updated successfully.';
        $messageType = 'success';

        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
    }
}

$pageTitle = 'My Profile';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Profile</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>reviewer/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
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

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="<?= getProfilePicture($user) ?>" alt="Profile" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <?php if ($user['expertise_area']): ?>
                <p class="mb-1"><i class="bi bi-bookmark me-1"></i><?= htmlspecialchars($user['expertise_area']) ?></p>
                <?php endif; ?>
                <?php if ($user['orcid_id']): ?>
                <p class="mb-1"><i class="bi bi-person-badge me-1"></i>ORCID: <?= htmlspecialchars($user['orcid_id']) ?></p>
                <?php endif; ?>
                <hr>
                <div class="text-start small">
                    <p class="mb-1"><strong>Department:</strong> <?= htmlspecialchars($user['department'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Institution:</strong> <?= htmlspecialchars($user['institution'] ?? 'N/A') ?></p>
                    <p class="mb-0"><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                </div>
                <hr>
                <small class="text-muted">Member since <?= formatDate($user['created_at']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Edit Profile</h6>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Institution</label>
                            <input type="text" name="institution" class="form-control" value="<?= htmlspecialchars($user['institution'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Expertise Area</label>
                            <input type="text" name="expertise_area" class="form-control" value="<?= htmlspecialchars($user['expertise_area'] ?? '') ?>" placeholder="e.g. Computer Science, Biology">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">ORCID ID</label>
                            <input type="text" name="orcid_id" class="form-control" value="<?= htmlspecialchars($user['orcid_id'] ?? '') ?>" placeholder="0000-0000-0000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-medium">Biography</label>
                            <textarea name="biography" class="form-control" rows="4"><?= htmlspecialchars($user['biography'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
