<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . getDashboardByRole($_SESSION['user_role']));
    exit();
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid reset link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Security token validation failed. Please try again.';
    } else {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        require_once __DIR__ . '/includes/auth.php';
        $result = resetPassword($token, $password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/mortarboard-fill.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--dark:#060a12;--purple:#d4a373;--purple-light:#e8c9a0;--blue:#537497;--text:#f5e6cc;--text-muted:#aabbc8;--text-dim:#69819c;--glass:rgba(245,230,204,.04);--glass-border:rgba(245,230,204,.08);--glow-purple:rgba(212,163,115,.35)}
body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;overflow:hidden}
.auth-bg{position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none}
.auth-bg::before{content:'';position:absolute;top:-30%;left:-10%;width:60%;height:60%;background:radial-gradient(circle,rgba(212,163,115,.12) 0%,transparent 70%);animation:glow-drift 8s ease-in-out infinite}
.auth-bg::after{content:'';position:absolute;bottom:-30%;right:-10%;width:60%;height:60%;background:radial-gradient(circle,rgba(83,116,151,.1) 0%,transparent 70%);animation:glow-drift 8s ease-in-out infinite 4s}
@keyframes glow-drift{0%,100%{transform:translate(0,0)}50%{transform:translate(30px,-30px)}}
.auth-container{position:relative;z-index:1;width:100%}
.auth-card{background:var(--glass);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);border:1px solid var(--glass-border);border-radius:24px;padding:40px 36px;position:relative;overflow:hidden;box-shadow:0 25px 80px rgba(0,0,0,.5)}
.auth-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--purple),var(--blue),transparent)}
.auth-card .card-glow{position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:conic-gradient(from 0deg,transparent,rgba(212,163,115,.03),transparent,rgba(83,116,151,.03),transparent);animation:spin-glow 10s linear infinite;pointer-events:none}
@keyframes spin-glow{to{transform:rotate(360deg)}}
.auth-header{text-align:center;margin-bottom:28px;position:relative;z-index:1}
.auth-logo{width:56px;height:56px;background:linear-gradient(135deg,var(--purple),var(--blue));border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;box-shadow:0 8px 30px var(--glow-purple);text-decoration:none;color:#fff}
.auth-header h4{font-weight:800;font-size:1.4rem;margin-bottom:4px}
.auth-header p{color:var(--text-muted);font-size:.9rem;margin:0}
.form-floating>.form-control{background:rgba(255,255,255,.03)!important;border:1px solid var(--glass-border)!important;color:var(--text)!important;border-radius:12px!important;padding:16px 16px 8px!important;height:60px!important;font-size:.9rem!important;transition:all .3s}
.form-floating>.form-control:focus{background:rgba(255,255,255,.06)!important;border-color:var(--purple)!important;box-shadow:0 0 0 3px var(--glow-purple)!important}
.form-floating>.form-control:focus~label,.form-floating>.form-control:not(:placeholder-shown)~label{color:var(--purple-light)!important;transform:scale(.85) translateY(-.8rem) translateX(.15rem)!important;opacity:.8}
.form-floating>label{color:var(--text-dim)!important;padding:16px 16px!important;font-size:.9rem!important}
.form-floating>.form-control::placeholder{color:transparent}
.btn-auth{background:linear-gradient(135deg,var(--purple),var(--blue));color:#fff;border:none;border-radius:12px;padding:14px;font-weight:600;font-size:.95rem;width:100%;transition:all .3s}
.btn-auth:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 30px var(--glow-purple)}
.auth-link{color:var(--text-muted);font-size:.88rem;transition:all .3s;text-decoration:none}
.auth-link:hover{color:var(--purple-light)}
.alert-custom{background:rgba(239,68,68,.1)!important;border:1px solid rgba(239,68,68,.2)!important;border-radius:12px;color:#FCA5A5!important;font-size:.85rem;padding:12px 16px;position:relative;z-index:1}
.alert-custom .btn-close{filter:invert(1) brightness(2);font-size:.75rem}
.alert-success-custom{background:rgba(16,185,129,.1)!important;border:1px solid rgba(16,185,129,.2)!important;border-radius:12px;color:#6EE7B7!important;font-size:.85rem;padding:12px 16px;position:relative;z-index:1}
    </style>
</head>
<body>
<div class="auth-bg"></div>
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-11">
                <div class="auth-card">
                    <div class="card-glow"></div>
                    <div class="auth-header">
                        <a href="index.php" class="auth-logo"><i class="bi bi-mortarboard-fill"></i></a>
                        <h4>Reset Password</h4>
                        <p>Enter your new password</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert-custom alert alert-dismissible fade show d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i><?= $error ?><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert-success-custom alert alert-dismissible fade show d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill flex-shrink-0"></i><?= $success ?> <a href="login.php" style="color:#8B5CF6;font-weight:600">Login now</a><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if (!$success && !empty($token)): ?>
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="password" placeholder="New Password" required minlength="6">
                            <label for="password">New Password</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" name="confirm_password" class="form-control" id="confirm" placeholder="Confirm Password" required>
                            <label for="confirm">Confirm Password</label>
                        </div>
                        <button type="submit" class="btn-auth">Reset Password</button>
                    </form>
                    <?php endif; ?>
                    <div class="text-center mt-4">
                        <a href="login.php" class="auth-link"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
