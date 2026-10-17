<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireRole('admin');

$db = getDb();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, ['get_grant', 'get_application', 'get_review'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        jsonResponse(['success' => false, 'message' => 'Security token validation failed.'], 403);
    }
}

header('Content-Type: application/json');

switch ($action) {
    case 'add':
        processAddUser($db);
        break;
    case 'edit':
        processEditUser($db);
        break;
    case 'toggle_status':
        processToggleStatus($db);
        break;
    case 'delete':
        processDeleteUser($db);
        break;
    case 'add_category':
        processAddCategory($db);
        break;
    case 'edit_category':
        processEditCategory($db);
        break;
    case 'get_grant':
        processGetGrant($db);
        break;
    case 'get_application':
        processGetApplication($db);
        break;
    case 'get_review':
        processGetReview($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}

function processAddUser($db) {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $institution = sanitizeInput($_POST['institution'] ?? '');
    $expertise_area = sanitizeInput($_POST['expertise_area'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($role) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Required fields are missing.'], 400);
    }
    if (!in_array($role, ['admin', 'researcher', 'reviewer'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid role selected.'], 400);
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email already exists.'], 400);
    }

    $username = strtolower($first_name . '.' . $last_name) . rand(100, 999);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, phone, department, institution, expertise_area, is_active, email_verified) VALUES (:username, :email, :password, :first_name, :last_name, :role, :phone, :department, :institution, :expertise, 1, 1)");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':role' => $role,
        ':phone' => $phone,
        ':department' => $department,
        ':institution' => $institution,
        ':expertise' => $expertise_area,
    ]);

    $userId = $db->lastInsertId();
    logAudit($_SESSION['user_id'], 'create', 'users', $userId, 'Created user: ' . $first_name . ' ' . $last_name . ' (' . $role . ')');
    createNotification($userId, 'account', 'Account Created', 'Your account has been created by an administrator.', SITE_URL . 'login.php');

    jsonResponse(['success' => true, 'message' => 'User created successfully.', 'reload' => true]);
}

function processEditUser($db) {
    $id = (int)$_POST['id'];
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $institution = sanitizeInput($_POST['institution'] ?? '');
    $expertise_area = sanitizeInput($_POST['expertise_area'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
        jsonResponse(['success' => false, 'message' => 'Required fields are missing.'], 400);
    }
    if (!in_array($role, ['admin', 'researcher', 'reviewer'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid role selected.'], 400);
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
    $stmt->execute([':email' => $email, ':id' => $id]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email already in use by another user.'], 400);
    }

    $stmt = $db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, role = :role, phone = :phone, department = :department, institution = :institution, expertise_area = :expertise_area WHERE id = :id");
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':role' => $role,
        ':phone' => $phone,
        ':department' => $department,
        ':institution' => $institution,
        ':expertise_area' => $expertise_area,
        ':id' => $id,
    ]);

    logAudit($_SESSION['user_id'], 'update', 'users', $id, 'Updated user: ' . $first_name . ' ' . $last_name);
    jsonResponse(['success' => true, 'message' => 'User updated successfully.', 'reload' => true]);
}

function processToggleStatus($db) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT is_active FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
    }

    $newStatus = $user['is_active'] ? 0 : 1;
    $stmt = $db->prepare("UPDATE users SET is_active = :status WHERE id = :id");
    $stmt->execute([':status' => $newStatus, ':id' => $id]);

    $action = $newStatus ? 'activated' : 'deactivated';
    logAudit($_SESSION['user_id'], $action, 'users', $id, 'User ' . $action);

    $_SESSION['success_message'] = 'User ' . $action . ' successfully.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'admin/pages/all_users.php'));
    exit();
}

function processDeleteUser($db) {
    $id = (int)$_GET['id'];
    if ($id == $_SESSION['user_id']) {
        jsonResponse(['success' => false, 'message' => 'You cannot delete your own account.'], 400);
    }

    $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
    }

    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    logAudit($_SESSION['user_id'], 'delete', 'users', $id, 'Deleted user: ' . $user['first_name'] . ' ' . $user['last_name']);

    $_SESSION['success_message'] = 'User deleted successfully.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'admin/pages/all_users.php'));
    exit();
}

function processAddCategory($db) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description'] ?? '');
    $icon = sanitizeInput($_POST['icon'] ?? '');
    $color = sanitizeInput($_POST['color'] ?? '');

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Category name is required.'], 400);
    }

    $stmt = $db->prepare("INSERT INTO grant_categories (name, description, icon, color) VALUES (:name, :description, :icon, :color)");
    $stmt->execute([':name' => $name, ':description' => $description, ':icon' => $icon, ':color' => $color]);

    logAudit($_SESSION['user_id'], 'create', 'grant_categories', $db->lastInsertId(), 'Created category: ' . $name);
    jsonResponse(['success' => true, 'message' => 'Category created successfully.', 'reload' => true]);
}

function processEditCategory($db) {
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description'] ?? '');
    $icon = sanitizeInput($_POST['icon'] ?? '');
    $color = sanitizeInput($_POST['color'] ?? '');

    if (empty($name)) {
        jsonResponse(['success' => false, 'message' => 'Category name is required.'], 400);
    }

    $stmt = $db->prepare("UPDATE grant_categories SET name = :name, description = :description, icon = :icon, color = :color WHERE id = :id");
    $stmt->execute([':name' => $name, ':description' => $description, ':icon' => $icon, ':color' => $color, ':id' => $id]);

    logAudit($_SESSION['user_id'], 'update', 'grant_categories', $id, 'Updated category: ' . $name);
    jsonResponse(['success' => true, 'message' => 'Category updated successfully.', 'reload' => true]);
}

function processGetGrant($db) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT g.*, gc.name as category_name, gc.icon as category_icon, gc.color as category_color, u.first_name as creator_first, u.last_name as creator_last, u.email as creator_email FROM grants g LEFT JOIN grant_categories gc ON g.category_id = gc.id LEFT JOIN users u ON g.created_by = u.id WHERE g.id = :id");
    $stmt->execute([':id' => $id]);
    $grant = $stmt->fetch();

    if (!$grant) {
        jsonResponse(['success' => false, 'message' => 'Grant not found.'], 404);
    }

    $grant['status_badge'] = getStatusBadge($grant['status']);
    $grant['funding_amount_formatted'] = $grant['funding_amount'] ? formatAmount($grant['funding_amount']) : 'N/A';
    $grant['application_start_date_formatted'] = $grant['application_start_date'] ? formatDate($grant['application_start_date']) : 'N/A';
    $grant['application_end_date_formatted'] = $grant['application_end_date'] ? formatDate($grant['application_end_date']) : 'N/A';
    $grant['review_deadline_formatted'] = $grant['review_deadline'] ? formatDate($grant['review_deadline']) : 'N/A';
    $grant['created_at_formatted'] = $grant['created_at'] ? formatDateTime($grant['created_at']) : 'N/A';
    $grant['creator_name'] = !empty($grant['creator_first']) ? trim($grant['creator_first'] . ' ' . $grant['creator_last']) : 'System Administrator';

    // Guidelines file download
    if (!empty($grant['guidelines_file'])) {
        $grant['guidelines_download_url'] = SITE_URL . 'uploads/guidelines/' . rawurlencode($grant['guidelines_file']);
        $grant['guidelines_icon'] = getFileIcon($grant['guidelines_file']);
    } else {
        $grant['guidelines_download_url'] = null;
        $grant['guidelines_icon'] = null;
    }

    // Fetch applications submitted under this grant
    $stmtProps = $db->prepare("SELECT p.id, p.title, p.status, p.budget_amount, p.submitted_at, p.review_score, u.first_name, u.last_name, u.email as researcher_email, u.department as researcher_department, u.institution as researcher_institution FROM proposals p JOIN users u ON p.researcher_id = u.id WHERE p.grant_id = :id ORDER BY p.created_at DESC");
    $stmtProps->execute([':id' => $id]);
    $proposals = $stmtProps->fetchAll();

    $stats = [
        'total' => count($proposals),
        'submitted' => 0,
        'under_review' => 0,
        'approved' => 0,
        'rejected' => 0,
        'awarded_total' => 0
    ];

    foreach ($proposals as &$prop) {
        if (isset($stats[$prop['status']])) {
            $stats[$prop['status']]++;
        }
        if ($prop['status'] === 'approved') {
            $stats['awarded_total'] += (float)$prop['budget_amount'];
        }
        $prop['status_badge'] = getStatusBadge($prop['status']);
        $prop['budget_amount_formatted'] = $prop['budget_amount'] ? formatAmount($prop['budget_amount']) : 'N/A';
        $prop['submitted_at_formatted'] = $prop['submitted_at'] ? formatDate($prop['submitted_at']) : '-';
        $prop['researcher_name'] = $prop['first_name'] . ' ' . $prop['last_name'];
    }
    unset($prop);

    $remaining = max(0, (float)$grant['funding_amount'] - $stats['awarded_total']);
    $stats['awarded_total_formatted'] = formatAmount($stats['awarded_total']);
    $stats['remaining_funding_formatted'] = formatAmount($remaining);

    $grant['stats'] = $stats;
    $grant['applications'] = $proposals;

    jsonResponse($grant);
}

function processGetApplication($db) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT p.*, u.first_name, u.last_name, u.email as researcher_email, u.phone as researcher_phone, u.department as researcher_department, u.institution as researcher_institution, u.qualification as researcher_qualification, u.expertise_area as researcher_expertise, g.title as grant_title, g.funding_amount as grant_funding FROM proposals p JOIN users u ON p.researcher_id = u.id JOIN grants g ON p.grant_id = g.id WHERE p.id = :id");
    $stmt->execute([':id' => $id]);
    $app = $stmt->fetch();

    if (!$app) {
        jsonResponse(['success' => false, 'message' => 'Application not found.'], 404);
    }

    $app['researcher_name'] = $app['first_name'] . ' ' . $app['last_name'];
    $app['status_badge'] = getStatusBadge($app['status']);

    // Fetch award info (approved_amount if exists)
    $stmtAward = $db->prepare("SELECT id, awarded_amount, approved_amount, total_disbursed, status as award_status FROM grant_awards WHERE proposal_id = :id LIMIT 1");
    $stmtAward->execute([':id' => $id]);
    $award = $stmtAward->fetch();
    $app['award'] = $award ?: null;
    $app['award_approved_amount'] = $award ? $award['approved_amount'] : null;

    // Fetch documents
    $stmtDocs = $db->prepare("SELECT * FROM proposal_documents WHERE proposal_id = :id ORDER BY uploaded_at DESC");
    $stmtDocs->execute([':id' => $id]);
    $docs = $stmtDocs->fetchAll();
    foreach ($docs as &$doc) {
        $filePath = !empty($doc['file_path']) ? $doc['file_path'] : $doc['file_name'];
        $doc['download_url'] = SITE_URL . 'uploads/proposals/' . rawurlencode($filePath);
        $doc['file_icon'] = getFileIcon($doc['file_name']);
        $doc['file_size_formatted'] = $doc['file_size'] ? round($doc['file_size'] / 1024, 1) . ' KB' : 'N/A';
    }
    unset($doc);
    $app['documents'] = $docs;

    // Fetch reviewer evaluations (includes recommended_amount)
    $stmtRev = $db->prepare("SELECT pr.*, u.first_name, u.last_name, u.email as reviewer_email FROM proposal_reviews pr JOIN users u ON pr.reviewer_id = u.id WHERE pr.proposal_id = :id ORDER BY pr.created_at DESC");
    $stmtRev->execute([':id' => $id]);
    $reviews = $stmtRev->fetchAll();
    foreach ($reviews as &$r) {
        $r['reviewer_name'] = $r['first_name'] . ' ' . $r['last_name'];
        $r['status_badge'] = getStatusBadge($r['status']);
        $r['recommendation_badge'] = $r['recommendation'] ? getStatusBadge($r['recommendation']) : '-';
        $r['completed_at_formatted'] = $r['completed_at'] ? formatDateTime($r['completed_at']) : 'In Progress';
        $r['recommended_amount_formatted'] = !empty($r['recommended_amount']) ? formatAmount($r['recommended_amount']) : null;
    }
    unset($r);
    $app['reviews'] = $reviews;

    // Compute average recommended_amount from completed approve reviews (PHP 7.3 compatible)
    $avgRec = null;
    $approveReviews = array_filter($reviews, function($r) {
        return $r['recommendation'] === 'approve' && !empty($r['recommended_amount']);
    });
    $recAmounts = array_filter(array_column(array_values($approveReviews), 'recommended_amount'));
    if (count($recAmounts) > 0) {
        $avgRec = array_sum($recAmounts) / count($recAmounts);
    }
    $app['avg_recommended_amount'] = $avgRec;
    $app['avg_recommended_amount_formatted'] = $avgRec ? formatAmount($avgRec) : null;

    jsonResponse($app);
}

function processGetReview($db) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT pr.*, u.first_name as reviewer_first, u.last_name as reviewer_last, p.title as proposal_title FROM proposal_reviews pr JOIN users u ON pr.reviewer_id = u.id JOIN proposals p ON pr.proposal_id = p.id WHERE pr.id = :id");
    $stmt->execute([':id' => $id]);
    $review = $stmt->fetch();

    if (!$review) {
        jsonResponse(['success' => false, 'message' => 'Review not found.'], 404);
    }

    $review['reviewer_name'] = $review['reviewer_first'] . ' ' . $review['reviewer_last'];
    $review['status_badge'] = getStatusBadge($review['status']);
    $review['recommendation_badge'] = $review['recommendation'] ? getStatusBadge($review['recommendation']) : '-';
    jsonResponse($review);
}
