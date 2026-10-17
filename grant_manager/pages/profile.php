<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('grant_manager');

$userId = $_SESSION['user_id'];
$db = getDb();

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

    $profilePicture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH, ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload['success']) {
            if ($profilePicture) {
                deleteFile(PROFILE_PATH . $profilePicture);
            }
            $profilePicture = $upload['file_name'];
        } else {
            $_SESSION['error_message'] = $upload['message'];
            header('Location: ' . SITE_URL . 'grant_manager/pages/profile.php');
            exit();
        }
    }

    $stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, department = :department, institution = :institution, profile_picture = :profile_picture WHERE id = :id");
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':phone' => $phone,
        ':department' => $department,
        ':institution' => $institution,
        ':profile_picture' => $profilePicture,
        ':id' => $userId,
    ]);

    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profilePicture;

    logAudit($userId, 'profile_update', 'users', $userId, 'Profile updated');
    $_SESSION['success_message'] = 'Profile updated successfully.';
    header('Location: ' . SITE_URL . 'grant_manager/pages/profile.php');
    exit();
}

$pageTitle = 'My Profile';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">My Profile</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>grant_manager/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </nav>
    </div>
</div>

<?= displaySessionMessages() ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <img src="<?= getProfilePicture($user) ?>" alt="Profile" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
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
                            <label class="form-label small fw-medium">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
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
