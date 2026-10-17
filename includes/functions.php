<?php
// ============================================================
// Common Functions
// ============================================================

require_once __DIR__ . '/../config/database.php';

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'];
    $lengths = [1, 60, 3600, 86400, 604800, 2630880, 31557600];

    $future = $diff < 0;
    $diff = abs($diff);

    for ($i = count($lengths) - 1; ($i >= 0) && (($diff / $lengths[$i]) <= 1); $i--);
    if ($i < 0) $i = 0;
    $num = $diff / $lengths[$i];
    $num = round($num);
    $period = $periods[$i];
    
    if ($num > 1) $period .= 's';
    
    if ($num == 0) return 'just now';
    return ($future ? 'in ' : '') . $num . ' ' . $period . ($future ? '' : ' ago');
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    return date($format, strtotime($datetime));
}

function formatAmount($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

function getStatusBadge($status) {
    $badges = [
        'active' => 'success',
        'inactive' => 'secondary',
        'pending' => 'warning',
        'draft' => 'secondary',
        'open' => 'success',
        'closed' => 'danger',
        'cancelled' => 'danger',
        'submitted' => 'primary',
        'under_review' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'revision_needed' => 'warning',
        'withdrawn' => 'secondary',
        'assigned' => 'info',
        'in_progress' => 'warning',
        'completed' => 'success',
        'overdue' => 'danger',
        'suspended' => 'warning',
        'terminated' => 'danger',
        'reviewed' => 'info',
        'monthly' => 'primary',
        'quarterly' => 'info',
        'biannual' => 'warning',
        'annual' => 'success',
        'final' => 'danger',
        'approve' => 'success',
        'revision' => 'warning',
        // Fund disbursement statuses
        'paid' => 'success',
        'scheduled' => 'warning',
        'partially_disbursed' => 'info',
        'fully_disbursed' => 'success',
    ];
    $class = $badges[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span class='badge bg-{$class}'>{$label}</span>";
}

function truncateText($text, $limit = 100) {
    if (strlen($text) <= $limit) return $text;
    return substr($text, 0, $limit) . '...';
}

function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

function getProfilePicture($user) {
    if (!empty($user['profile_picture']) && file_exists(PROFILE_PATH . $user['profile_picture'])) {
        return SITE_URL . 'uploads/profiles/' . $user['profile_picture'];
    }
    return "https://ui-avatars.com/api/?name=" . urlencode($user['first_name'] . '+' . $user['last_name']) . "&background=0D6EFD&color=fff&size=150";
}

function getGravatarUrl($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
}

function uploadFile($file, $targetDir, $allowedTypes = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
    }

    $allowedTypes = $allowedTypes ?: explode(',', ALLOWED_FILE_TYPES);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum limit.'];
    }

    $fileName = uniqid() . '_' . time() . '.' . $ext;
    $targetPath = $targetDir . $fileName;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'file_name' => $fileName, 'file_path' => $targetPath, 'file_size' => $file['size']];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file.'];
}

function deleteFile($filePath) {
    if ($filePath && file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

function getFileIcon($fileName) {
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'bi-filetype-pdf text-danger',
        'doc' => 'bi-filetype-doc text-primary',
        'docx' => 'bi-filetype-docx text-primary',
        'xls' => 'bi-filetype-xls text-success',
        'xlsx' => 'bi-filetype-xlsx text-success',
        'ppt' => 'bi-filetype-ppt text-warning',
        'pptx' => 'bi-filetype-pptx text-warning',
        'jpg' => 'bi-filetype-jpg text-info',
        'jpeg' => 'bi-filetype-jpg text-info',
        'png' => 'bi-filetype-png text-info',
        'gif' => 'bi-filetype-gif text-info',
        'txt' => 'bi-filetype-txt text-secondary',
        'zip' => 'bi-filetype-zip text-secondary',
    ];
    return $icons[$ext] ?? 'bi-file-text text-secondary';
}

function getAvatarUrl($name) {
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=0D6EFD&color=fff&size=40";
}

function logAudit($userId, $action, $entityType = null, $entityId = null, $description = null) {
    try {
        $db = getDb();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                              VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip_address, :user_agent)");
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    } catch (Exception $e) {
        // Silently handle audit log errors
    }
}

function createNotification($userId, $type, $title, $message = null, $link = null) {
    try {
        $db = getDb();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                              VALUES (:user_id, :type, :title, :message, :link)");
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':title' => $title,
            ':message' => $message,
            ':link' => $link,
        ]);
    } catch (Exception $e) {
        // Silently handle notification errors
    }
}

function getUnreadNotificationCount($userId) {
    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch()['count'];
    } catch (Exception $e) {
        return 0;
    }
}

function getNotifications($userId, $limit = 10) {
    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getSetting($key, $default = null) {
    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getTableCount($table, $condition = '1=1', $params = []) {
    try {
        $db = getDb();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM {$table} WHERE {$condition}");
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    } catch (Exception $e) {
        return 0;
    }
}

function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function isGrantOpen($grant) {
    $now = date('Y-m-d');
    return $grant['status'] === 'open' && 
           $grant['is_published'] == 1 &&
           $grant['application_start_date'] <= $now && 
           $grant['application_end_date'] >= $now;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION[$type . '_message'] = $message;
    }
    header('Location: ' . $url);
    exit();
}

function getDocumentTypes() {
    return [
        'proposal' => 'Proposal Document',
        'budget' => 'Budget Document',
        'supporting' => 'Supporting Document',
        'ethics' => 'Ethics Approval',
        'other' => 'Other Document',
    ];
}

function getReportTypes() {
    return [
        'monthly' => 'Monthly Report',
        'quarterly' => 'Quarterly Report',
        'biannual' => 'Bi-Annual Report',
        'annual' => 'Annual Report',
        'final' => 'Final Report',
    ];
}

function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function requireCSRF() {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        $_SESSION['error_message'] = 'Security token validation failed. Please try again.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'login.php'));
        exit();
    }
}

function paginate($total, $page, $perPage = 10) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
        'prev_page' => $page - 1,
        'next_page' => $page + 1,
    ];
}

function generatePaginationLinks($baseUrl, $currentPage, $totalPages, $params = []) {
    if ($totalPages <= 1) return '';
    
    $query = http_build_query(array_merge($params, ['page' => '']));
    $baseUrl .= '?' . $query;
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . ($currentPage - 1) . '">Previous</a></li>';
    }
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . $i . '">' . $i . '</a></li>';
    }
    
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . ($currentPage + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

function getMonthsArray() {
    return [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
}

function displayAlert($message, $type = 'success') {
    if (empty($message)) return '';
    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info' => 'bi-info-circle-fill',
    ];
    $icon = $icons[$type] ?? 'bi-info-circle-fill';
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
            <i class='{$icon} me-2'></i>{$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
}

function displaySessionMessages() {
    $html = '';
    foreach (['success', 'danger', 'warning', 'info'] as $type) {
        if (isset($_SESSION[$type . '_message'])) {
            $html .= displayAlert($_SESSION[$type . '_message'], $type);
            unset($_SESSION[$type . '_message']);
        }
    }
    return $html;
}

function getYears() {
    $years = [];
    for ($i = date('Y'); $i >= date('Y') - 10; $i--) {
        $years[] = $i;
    }
    return $years;
}
