<?php
$unreadCount = getUnreadNotificationCount($_SESSION['user_id']);
$notifications = getNotifications($_SESSION['user_id'], 5);
$role = $_SESSION['user_role'];
$baseUrl = SITE_URL;
?>
<nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
        <button type="button" id="sidebarToggle" class="navbar-toggle-btn" title="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="d-flex align-items-center ms-auto">
            <!-- Theme Toggle (Light / Dark) -->
            <?php 
            $navTheme = $_COOKIE['app_theme'] ?? 'dark';
            $isLight = ($navTheme === 'light');
            ?>
            <button type="button" class="btn btn-light me-2 d-flex align-items-center justify-content-center" id="themeToggleBtn" onclick="window.toggleAppTheme(event)" title="<?= $isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode' ?>" aria-label="Toggle theme" style="width: 38px; height: 38px; border-radius: 8px; cursor: pointer;">
                <?php if ($isLight): ?>
                    <i class="bi bi-moon-stars-fill text-primary fs-5" id="themeToggleIcon"></i>
                <?php else: ?>
                    <i class="bi bi-sun-fill text-warning fs-5" id="themeToggleIcon"></i>
                <?php endif; ?>
            </button>

            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $unreadCount ?>
                    </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown p-0" style="width: 320px;">
                    <li class="dropdown-header bg-primary text-white py-2 px-3">
                        <small>Notifications (<?= $unreadCount ?> unread)</small>
                    </li>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <?php if (empty($notifications)): ?>
                        <li><a class="dropdown-item text-center py-3 text-muted small" href="#">No notifications</a></li>
                        <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 <?= !$notif['is_read'] ? 'bg-light' : '' ?>" href="<?= $notif['link'] ?: '#' ?>">
                                <small class="fw-medium d-block"><?= htmlspecialchars($notif['title']) ?></small>
                                <small class="text-muted"><?= truncateText($notif['message'] ?? '', 60) ?></small>
                                <small class="text-muted d-block mt-1"><?= timeAgo($notif['created_at']) ?></small>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <li><a class="dropdown-item text-center small py-2 border-top" href="<?= $baseUrl ?>pages/notifications.php">View All Notifications</a></li>
                </ul>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <img src="<?= getAvatarUrl($_SESSION['user_name']) ?>" alt="" class="rounded-circle me-2" width="28" height="28">
                    <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="<?= $baseUrl ?><?= $role ?>/pages/profile.php">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $baseUrl ?><?= $role ?>/pages/change_password.php">
                            <i class="bi bi-key me-2"></i>Change Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= $baseUrl ?>logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
