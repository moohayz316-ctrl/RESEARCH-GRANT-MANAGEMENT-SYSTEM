<?php
// ============================================================
// Authentication & Authorization Functions
// ============================================================

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'Please login to access this page.';
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        $_SESSION['error_message'] = 'You do not have permission to access this page.';
        $redirect = getDashboardByRole($_SESSION['user_role']);
        header('Location: ' . $redirect);
        exit();
    }
}

function getDashboardByRole($role) {
    $dashboards = [
        'admin' => SITE_URL . 'admin/index.php',
        'researcher' => SITE_URL . 'researcher/index.php',
        'reviewer' => SITE_URL . 'reviewer/index.php',
        'grant_manager' => SITE_URL . 'admin/index.php',
    ];
    return $dashboards[$role] ?? SITE_URL . 'login.php';
}

function loginUser($email, $password) {
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated. Contact administrator.'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_picture'] = $user['profile_picture'];
        $_SESSION['login_time'] = time();

        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);

        logAudit($user['id'], 'login', 'users', $user['id'], 'User logged in');

        return ['success' => true, 'role' => $user['role'], 'message' => 'Login successful.'];
    }
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logAudit($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id'], 'User logged out');
    }
    session_destroy();
    header('Location: ' . SITE_URL . 'login.php');
    exit();
}

function registerUser($data) {
    $db = getDb();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1");
    $stmt->execute([':email' => $data['email'], ':username' => $data['username']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or username already exists.'];
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(32));

    $role = in_array($data['role'] ?? '', ['researcher', 'reviewer']) ? $data['role'] : 'researcher';

    $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, reviewer_status, verification_token, created_at) 
                          VALUES (:username, :email, :password, :first_name, :last_name, :role, :reviewer_status, :token, NOW())");
    $stmt->execute([
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':password' => $hashedPassword,
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':role' => $role,
        ':reviewer_status' => $role === 'reviewer' ? 'active' : 'pending',
        ':token' => $token,
    ]);

    $userId = $db->lastInsertId();

    $profileLink = ($role === 'reviewer') ? (SITE_URL . 'reviewer/pages/profile.php') : (SITE_URL . 'researcher/pages/profile.php');
    createNotification($userId, 'welcome', 'Welcome to ' . SITE_NAME, 'Your account has been created successfully. Please complete your profile.', $profileLink);

    logAudit($userId, 'register', 'users', $userId, 'New user registered as ' . $role);

    return ['success' => true, 'message' => 'Registration successful. You can now login.'];
}

function sendPasswordResetLink($email) {
    $db = getDb();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Email not found in our system.'];
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);

    $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE id = :id");
    $stmt->execute([':token' => $token, ':expires' => $expires, ':id' => $user['id']]);

    $resetLink = SITE_URL . 'reset_password.php?token=' . $token;

    logAudit($user['id'], 'password_reset_request', 'users', $user['id'], 'Password reset requested');

    return ['success' => true, 'message' => 'Password reset link has been sent to your email.', 'reset_link' => $resetLink];
}

function resetPassword($token, $password) {
    $db = getDb();
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid or expired reset token.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
    $stmt->execute([':password' => $hashedPassword, ':id' => $user['id']]);

    logAudit($user['id'], 'password_reset', 'users', $user['id'], 'Password reset completed');

    return ['success' => true, 'message' => 'Password has been reset successfully.'];
}

function changePassword($userId, $currentPassword, $newPassword) {
    $db = getDb();
    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);

    logAudit($userId, 'password_change', 'users', $userId, 'Password changed');

    return ['success' => true, 'message' => 'Password changed successfully.'];
}

function hasPermission($userId, $role) {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId && $_SESSION['user_role'] === $role;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            logoutUser();
        }
        $_SESSION['login_time'] = time();
    }
}
