<?php $currentTheme = $_COOKIE['app_theme'] ?? 'dark'; ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
    <meta charset="UTF-8">
    <script>
        (function() {
            var saved = 'dark';
            try {
                saved = localStorage.getItem('app_theme');
            } catch(e){}
            if (!saved) {
                var m = document.cookie.match(/(^|;)\s*app_theme=([^;]+)/);
                saved = m ? m[2] : '<?= htmlspecialchars($currentTheme) ?>';
            }
            document.documentElement.setAttribute('data-bs-theme', saved);
        })();

        window.toggleAppTheme = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            var html = document.documentElement;
            var current = html.getAttribute('data-bs-theme') || 'dark';
            var next = (current === 'light') ? 'dark' : 'light';
            
            html.setAttribute('data-bs-theme', next);
            if (document.body) {
                document.body.setAttribute('data-bs-theme', next);
            }
            
            try {
                localStorage.setItem('app_theme', next);
            } catch(err){}
            document.cookie = 'app_theme=' + next + '; path=/; max-age=' + (365 * 24 * 60 * 60) + '; SameSite=Lax';
            
            var btn = document.getElementById('themeToggleBtn');
            var icon = document.getElementById('themeToggleIcon');
            if (icon) {
                if (next === 'light') {
                    icon.className = 'bi bi-moon-stars-fill text-primary fs-5';
                    if (btn) btn.setAttribute('title', 'Switch to Dark Mode');
                } else {
                    icon.className = 'bi bi-sun-fill text-warning fs-5';
                    if (btn) btn.setAttribute('title', 'Switch to Light Mode');
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            var theme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
            var btn = document.getElementById('themeToggleBtn');
            var icon = document.getElementById('themeToggleIcon');
            if (icon) {
                if (theme === 'light') {
                    icon.className = 'bi bi-moon-stars-fill text-primary fs-5';
                    if (btn) btn.setAttribute('title', 'Switch to Dark Mode');
                } else {
                    icon.className = 'bi bi-sun-fill text-warning fs-5';
                    if (btn) btn.setAttribute('title', 'Switch to Light Mode');
                }
            }
        });
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/mortarboard-fill.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time() ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
<div class="wrapper">
    <!-- Mobile Overlay -->
    <div class="overlay"></div>
    <!-- Sidebar -->
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    <!-- Main Content -->
    <div id="content">
        <!-- Top Navbar -->
        <?php include_once __DIR__ . '/navbar.php'; ?>
        <div class="container-fluid p-4">
<?php else: ?>
<?php endif; ?>
