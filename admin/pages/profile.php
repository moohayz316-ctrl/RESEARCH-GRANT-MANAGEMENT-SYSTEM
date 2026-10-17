<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$pageTitle = 'Profile';

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $department = sanitizeInput($_POST['department']);

    $profile_picture = $user['profile_picture'];

    if ($email !== $user['email']) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute([':email' => $email, ':id' => $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Email already in use by another user.';
            header('Location: ' . SITE_URL . 'admin/pages/profile.php');
            exit();
        }
    }

    if (!empty($_FILES['profile_picture']['name'])) {
        $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH, ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload['success']) {
            if ($user['profile_picture'] && file_exists(PROFILE_PATH . $user['profile_picture'])) {
                unlink(PROFILE_PATH . $user['profile_picture']);
            }
            $profile_picture = $upload['file_name'];
        } else {
            $_SESSION['error_message'] = $upload['message'];
        }
    }

    $stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, department = :department, profile_picture = :picture WHERE id = :id");
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':phone' => $phone,
        ':department' => $department,
        ':picture' => $profile_picture,
        ':id' => $_SESSION['user_id'],
    ]);

    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_picture;

    logAudit($_SESSION['user_id'], 'update', 'users', $_SESSION['user_id'], 'Updated profile');
    $_SESSION['success_message'] = 'Profile updated successfully.';
    header('Location: ' . SITE_URL . 'admin/pages/profile.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <h4 class="mb-0 fw-bold">My Profile</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>
</div>

<?= displaySessionMessages() ?>

<div class="row g-4">
    <div class="col-lg-4" data-aos="fade-up">
        <div class="card">
            <div class="card-body text-center">
                <div class="profile-header mb-4">
                    <img src="<?= getProfilePicture($user) ?>" alt="" class="profile-avatar mb-3">
                    <h5 class="text-white mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <p class="text-white-50 mb-0"><?= ucwords(str_replace('_', ' ', $user['role'])) ?></p>
                </div>
                <div class="text-start">
                    <div class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= htmlspecialchars($user['email']) ?></div>
                    <div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></div>
                    <div class="mb-2"><i class="bi bi-building me-2 text-primary"></i><?= htmlspecialchars($user['department'] ?? 'Not set') ?></div>
                    <div><i class="bi bi-calendar me-2 text-primary"></i>Joined <?= formatDate($user['created_at']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
        <div class="card">
            <div class="card-header">Edit Profile</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control file-input-preview" data-preview="#profilePreview" accept="image/*">
                            <div class="mt-2">
                                <img id="profilePreview" src="#" alt="" style="display:none;max-width:100px;border-radius:50%">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
