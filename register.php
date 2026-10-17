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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Security token validation failed. Please try again.';
    } else {
    $data = [
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'role' => sanitizeInput($_POST['role'] ?? 'researcher'),
    ];

    if (in_array('', $data) || empty($data['password'])) {
        $error = 'All fields are required.';
    } elseif (!validateEmail($data['email'])) {
        $error = 'Invalid email address.';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        require_once __DIR__ . '/includes/auth.php';
        $result = registerUser($data);
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
    <title>Create Account — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/mortarboard-fill.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
    --dark:#060a12;--dark2:#0a1728;--purple:#d4a373;--purple-light:#e8c9a0;--blue:#537497;--text:#f5e6cc;
    --text-muted:#aabbc8;--text-dim:#69819c;--glass:rgba(245,230,204,.04);--glass-border:rgba(245,230,204,.08);
    --glow-purple:rgba(212,163,115,.35);--glow-secondary:rgba(83,116,151,.35);
}
body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;padding:40px 0}
.auth-bg{position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none}
.auth-bg::before{content:'';position:absolute;top:-20%;left:-5%;width:50%;height:50%;background:radial-gradient(circle,rgba(212,163,115,.1) 0%,transparent 70%);animation:glow-drift 8s ease-in-out infinite}
.auth-bg::after{content:'';position:absolute;bottom:-20%;right:-5%;width:50%;height:50%;background:radial-gradient(circle,rgba(83,116,151,.08) 0%,transparent 70%);animation:glow-drift 8s ease-in-out infinite 4s}
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
.auth-form{position:relative;z-index:1}
.form-floating>.form-control{background:rgba(255,255,255,.03)!important;border:1px solid var(--glass-border)!important;color:var(--text)!important;border-radius:12px!important;padding:16px 16px 8px!important;height:60px!important;font-size:.9rem!important;transition:all .3s}
.form-floating>.form-control:focus{background:rgba(255,255,255,.06)!important;border-color:var(--purple)!important;box-shadow:0 0 0 3px var(--glow-purple)!important}
.form-floating>.form-control:focus~label,.form-floating>.form-control:not(:placeholder-shown)~label{color:var(--purple-light)!important;transform:scale(.85) translateY(-.8rem) translateX(.15rem)!important;opacity:.8}
.form-floating>label{color:var(--text-dim)!important;padding:16px 16px!important;font-size:.9rem!important}
.form-floating>.form-control::placeholder{color:transparent}
.form-control-plaintext{background:rgba(255,255,255,.03)!important;border:1px solid var(--glass-border)!important;color:var(--text)!important;border-radius:12px;padding:14px 16px;font-size:.9rem;width:100%}
.btn-auth{background:linear-gradient(135deg,var(--purple),var(--blue));color:#fff;border:none;border-radius:12px;padding:14px;font-weight:600;font-size:.95rem;width:100%;transition:all .3s;position:relative;overflow:hidden}
.btn-auth:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 30px var(--glow-purple)}
.btn-auth::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:linear-gradient(45deg,transparent,rgba(255,255,255,.1),transparent);transform:rotate(45deg) translateX(-100%);transition:.6s}
.btn-auth:hover::before{transform:rotate(45deg) translateX(100%)}
.auth-link{color:var(--text-muted);font-size:.88rem;transition:all .3s}
.auth-link:hover{color:var(--purple-light)}
.alert-custom{background:rgba(239,68,68,.1)!important;border:1px solid rgba(239,68,68,.2)!important;border-radius:12px;color:#FCA5A5!important;font-size:.85rem;padding:12px 16px;position:relative;z-index:1}
.alert-custom .btn-close{filter:invert(1) brightness(2);font-size:.75rem}
.alert-success-custom{background:rgba(101,163,144,.1)!important;border:1px solid rgba(101,163,144,.2)!important;border-radius:12px;color:#a3d4c2!important;font-size:.85rem;padding:12px 16px;position:relative;z-index:1}
.back-home{display:inline-flex;align-items:center;gap:6px;color:var(--text-dim);font-size:.85rem;text-decoration:none;transition:all .3s;margin-top:16px}
.back-home:hover{color:var(--purple-light)}
.form-hint{color:var(--text-dim);font-size:.75rem;margin-top:4px}
.password-strength{height:4px;background:var(--glass-border);border-radius:4px;margin-top:8px;overflow:hidden;transition:all .3s}
.password-strength .bar{height:100%;border-radius:4px;width:0;transition:all .3s}
.row.gap-fix{margin-left:-8px;margin-right:-8px}
.row.gap-fix>[class*="col-"]{padding-left:8px;padding-right:8px}
    </style>
</head>
<body>
<div class="auth-bg"></div>
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-11">
                <div class="auth-card">
                    <div class="card-glow"></div>
                    <div class="auth-header">
                        <a href="index.php" class="auth-logo"><i class="bi bi-mortarboard-fill"></i></a>
                        <h4>Create Account</h4>
                        <p>Join the research community</p>
                    </div>
                    <div class="auth-form">
                        <?php if ($error): ?>
                            <div class="alert-custom alert alert-dismissible fade show d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i><?= $error ?>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert-success-custom alert alert-dismissible fade show d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill flex-shrink-0"></i><?= $success ?>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label small text-muted d-block mb-2">Account Role</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="role" id="roleResearcher" value="researcher" checked>
                                    <label class="btn btn-outline-secondary" for="roleResearcher"><i class="bi bi-mortarboard me-1"></i>Researcher</label>

                                    <input type="radio" class="btn-check" name="role" id="roleReviewer" value="reviewer">
                                    <label class="btn btn-outline-secondary" for="roleReviewer"><i class="bi bi-clipboard-check me-1"></i>Reviewer</label>
                                </div>
                            </div>
                            <div class="row gap-fix g-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" name="first_name" class="form-control" id="firstName" placeholder="First Name" required>
                                        <label for="firstName">First Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" name="last_name" class="form-control" id="lastName" placeholder="Last Name" required>
                                        <label for="lastName">Last Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="username" class="form-control" id="username" placeholder="username" required>
                                <label for="username">Username</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                                <label for="email">Email Address</label>
                            </div>
                            <div class="row gap-fix g-0">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required minlength="6" oninput="checkStrength(this)">
                                        <label for="password">Password</label>
                                    </div>
                                    <div class="password-strength"><div class="bar" id="strengthBar"></div></div>
                                    <div class="form-hint"><i class="bi bi-info-circle me-1"></i>Min 6 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="password" name="confirm_password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
                                        <label for="confirmPassword">Confirm Password</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn-auth mt-2"><i class="bi bi-person-plus me-2"></i>Create Account</button>
                        </form>
                        <div class="text-center mt-4">
                            <span class="auth-link">Already have an account? <a href="login.php" style="color:var(--purple-light);text-decoration:none;font-weight:600">Sign in</a></span>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="back-home"><i class="bi bi-arrow-left"></i>Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function checkStrength(input){
    const bar=document.getElementById('strengthBar');
    const val=input.value;
    let score=0;
    if(val.length>=6)score+=25;
    if(val.match(/[a-z]/)&&val.match(/[A-Z]/))score+=25;
    if(val.match(/\d/))score+=25;
    if(val.match(/[^a-zA-Z\d]/))score+=25;
    bar.style.width=score+'%';
    if(score<25){bar.style.background='#EF4444'}
    else if(score<50){bar.style.background='#c4936f'}
    else if(score<75){bar.style.background='#537497'}
    else{bar.style.background='#65a390'}
}
</script>
</body>
</html>
