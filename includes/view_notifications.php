<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Notifications';

$db = getDb();
$userId = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_read'])) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    redirect('view_notifications.php');
}

// Mark single as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $_GET['read'], ':user_id' => $userId]);
    redirect('view_notifications.php');
}

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $userId]);
$notifications = $stmt->fetchAll();

include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Notifications</h5>
    <div>
        <?php if (count($notifications) > 0): ?>
        <a href="?mark_read=1" class="btn btn-outline-primary btn-sm me-2">
            <i class="bi bi-check-all me-1"></i>Mark All Read
        </a>
        <?php endif; ?>
        <a href="<?= SITE_URL . $_SESSION['user_role'] ?>/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-3">No notifications yet.</p>
        </div>
        <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notif): ?>
            <div class="list-group-item list-group-item-action <?= !$notif['is_read'] ? 'bg-light' : '' ?>">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center">
                            <?php if (!$notif['is_read']): ?>
                            <span class="badge bg-primary rounded-circle me-2" style="width: 8px; height: 8px; padding: 0;"></span>
                            <?php endif; ?>
                            <h6 class="mb-1 <?= !$notif['is_read'] ? 'fw-bold' : '' ?>"><?= htmlspecialchars($notif['title']) ?></h6>
                        </div>
                        <?php if ($notif['message']): ?>
                        <p class="mb-1 text-muted small"><?= htmlspecialchars($notif['message']) ?></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i><?= formatDateTime($notif['created_at']) ?>
                            (<?= timeAgo($notif['created_at']) ?>)
                        </small>
                    </div>
                    <div class="d-flex align-items-center">
                        <?php if ($notif['link']): ?>
                        <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!$notif['is_read']): ?>
                        <a href="?read=<?= $notif['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-check"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
